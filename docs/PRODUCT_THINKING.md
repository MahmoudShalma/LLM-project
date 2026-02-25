# Product Thinking

## Problem Statement

Online learning platforms face a deceptively hard problem: tracking student progress reliably at scale. The naive approach (increment a counter, check a flag) fails under concurrent requests, network retries, and admin course edits. This project demonstrates how to build a learning system that is correct under real-world conditions.

## Core User Journeys

### 1. Student Discovery
```
Guest visits site → sees published courses → clicks free-preview lesson → watches video
                 → registers/logs in → enrolls → resumes from where they left off
```

### 2. Course Completion
```
Student completes required lessons → system detects completion → certificate issued
                                  → completion email sent (exactly once)
                                  → certificate accessible via unique URL
```

### 3. Admin Course Management
```
Admin creates course (draft) → adds lessons → reorders via drag → publishes
                             → students can now enroll
                             → admin can add new required lessons mid-enrollment
                             → existing students will need to complete new lessons
```

---

## 1. Business Risks

### Risk 1 — Low course completion rate
**The risk**: Students enroll out of curiosity but never finish. High enrollment counts look good but if nobody completes, the platform produces no certificates and delivers no value to learners or to Career 180 as a business.

**How the architecture mitigates it**:
- `lesson_progress.started_at` captures when each student first plays a lesson — this separates "enrolled and never started" from "started but dropped off at lesson 3," giving instructors actionable data on exactly where students quit
- The `LessonObserver` ensures completion re-evaluates automatically when a required lesson is deleted — so a student who was stuck can suddenly complete without needing to do anything
- The certificate + completion email is a real incentive loop: students who are close to done have a concrete reward to push through

**Residual risk**: Without a notification system, a student who is 90% complete and a new required lesson is added has no idea their goal just moved. This is acknowledged and documented.

---

### Risk 2 — Certificate credibility
**The risk**: If certificates can be obtained fraudulently (marking all lessons complete without watching them, or guessing another student's certificate URL), the certificate loses value for employers and students stop caring about earning one.

**How the architecture mitigates it**:
- There is no "bulk complete" API — each lesson must be individually marked complete via `MarkLessonCompletedAction`, which validates enrollment and checks the lesson actually belongs to the enrolled course
- Certificate URLs use UUID v4 (`/certificates/{uuid}`) — non-sequential, non-guessable, collision probability is astronomically low
- `UNIQUE(enrollment_id)` on certificates means one enrollment can never produce more than one certificate, even under queue retries
- Soft-delete on lessons preserves the full `lesson_progress` audit trail — you can always verify which lessons a student completed and when

**Residual risk**: The system trusts that clicking "Mark Complete" means the student watched the video. There is no video-watch-time validation (e.g., "must watch at least 80% of the video before marking complete"). This is a known gap for the MVP.

---

### Risk 3 — Admin actions silently breaking enrolled students
**The risk**: An admin publishes a course, students enroll and start learning. The admin then removes a free-preview lesson that many students were using to evaluate the course, or adds a new required lesson the day before students expected to finish. Students lose trust.

**How the architecture mitigates it**:
- The `LessonObserver` handles the positive case: if the admin deletes the last remaining required lesson, all active enrollments are automatically re-evaluated and completed — students don't get stuck
- `is_required = false` lessons don't affect completion at all — admins can freely add enrichment content without blocking anyone
- `courses.status = draft` allows admins to pull a course offline for major revisions without affecting enrolled students' ability to continue

**Residual risk**: There is no notification to enrolled students when new required lessons are added. This is the highest-likelihood risk in the table — mitigating it requires a notification system that is out of scope for this MVP.

---

## 2. Metrics That Matter

### Metric 1 — Course completion rate
**Definition**: `completed enrollments / total enrollments` per course.

**Where to capture it**: `enrollments` table — `completed_at IS NOT NULL` vs `COUNT(*)` grouped by `course_id`.

**Compute or store?** Compute on-demand for the admin dashboard. At scale (>10k enrollments per course), store a denormalized `completion_count` and `enrollment_count` on the `courses` table, updated by the `CourseCompleted` and `UserEnrolled` listeners. This turns a `GROUP BY` aggregation into a single-row lookup.

**Performance consideration**: With a compound index on `(course_id, completed_at)`, the query stays fast up to ~100k rows. Beyond that, pre-aggregate nightly via a scheduled Artisan command and cache in Redis with a 1-hour TTL so the dashboard never blocks on a heavy query.

---

### Metric 2 — Drop-off lesson (bottleneck detection)
**Definition**: The lesson with the highest ratio of `started_at IS NOT NULL AND completed_at IS NULL` — students who started it but never finished.

**Where to capture it**: `lesson_progress` table — already has both timestamps.

**Compute or store?** Compute on-demand in the admin panel. Query:
```sql
SELECT lesson_id, COUNT(*) as stuck_count
FROM lesson_progress
WHERE started_at IS NOT NULL AND completed_at IS NULL
GROUP BY lesson_id
ORDER BY stuck_count DESC
```

**Performance consideration**: The existing index on `(user_id, completed_at)` helps partially. For large datasets, a dedicated `lesson_stats` table updated by `LessonCompleted` listener would be more efficient. For MVP, the on-demand query with proper indexes is sufficient.

---

### Metric 3 — Time-to-first-lesson after enrollment
**Definition**: Median time between `enrollments.enrolled_at` and the first `lesson_progress.started_at` for that enrollment.

**Where to capture it**: JOIN between `enrollments` and `lesson_progress`, taking `MIN(started_at)` per `enrollment_id`.

**Compute or store?** Store. Add a `first_lesson_started_at` column to `enrollments` and set it inside `MarkLessonStartedAction` only when no prior `lesson_progress` rows exist for that enrollment. This turns a multi-table aggregation into a single-column read.

**Performance consideration**: Storing it means no JOIN needed. The stored value is immutable once written (first lesson start never changes), so there is no consistency risk.

---

### Metric 4 — Daily enrollment velocity
**Definition**: Number of new enrollments per course per day/week — used to measure marketing impact and course popularity trends.

**Where to capture it**: `enrollments.enrolled_at` — time-series column already present.

**Compute or store?** Store aggregated daily snapshots. A nightly scheduled command computes `SELECT DATE(enrolled_at), course_id, COUNT(*) FROM enrollments GROUP BY 1,2` and writes results to a `daily_enrollment_stats` table. The dashboard reads from this pre-aggregated table, not the raw `enrollments` table.

**Performance consideration**: The raw `enrollments` table will grow unboundedly. Running `GROUP BY DATE(enrolled_at)` on millions of rows every page load is a full table scan. Pre-aggregation means the dashboard query is always `O(days × courses)` — manageable.

---

### Metric 5 — Certificate issuance health (system correctness metric)
**Definition**: `certificates.count` should always equal `enrollments WHERE completed_at IS NOT NULL AND deleted_at IS NULL`. Any divergence means a queue failure caused a completed enrollment to never receive a certificate.

**Where to capture it**: Compare counts in both tables — no schema change needed.

**Compute or store?** Compute on a schedule — this is a monitoring/alerting metric, not a display metric. Run every 5 minutes via a scheduled job; alert if the gap exceeds 0 for more than 15 minutes.

**Performance consideration**: Both counts are simple `COUNT(*)` queries on indexed columns — extremely cheap. No caching needed.

---

## 3. Future Evolution

### Paid Courses

**What the current architecture already supports**:
- `EnrollUserAction` is the single entry point for enrollment creation. Adding a payment gate means adding one check before `firstOrCreate()` — the rest of the enrollment flow (events, listeners, certificates) is unchanged
- `CoursePolicy::enroll()` already validates course eligibility — a `paid` check fits naturally here
- The `courses` table already has a `status` enum — extending it or adding a `price` decimal column is a simple migration

**What would need refactoring**:
- `EnrollmentController::store()` currently creates the enrollment immediately. For paid courses it needs to initiate a Stripe PaymentIntent, redirect the user, and only create the enrollment on webhook confirmation — this is a significant flow change
- Need a new `Payment` model and a `StripeWebhookController` to handle `payment_intent.succeeded` events
- The enrollment idempotency guard (`firstOrCreate`) would need to be extended to also verify payment status before allowing access to course content

---

### Mobile App API

**What the current architecture already supports**:
- The Actions layer (`EnrollUserAction`, `MarkLessonCompletedAction`, `IssueCertificateAction`) is already fully decoupled from HTTP. A mobile API controller calls the same Actions that Livewire components currently call — zero duplication of business logic
- Authorization Policies work identically for API requests
- The Event/Listener pipeline is transport-agnostic — `CourseCompleted` fires the same way whether triggered from Livewire or an API controller

**What would need refactoring**:
- All current routes return Livewire-rendered HTML. Need a parallel `/api/v1/` route group returning JSON
- Authentication is currently session-based (Breeze). Mobile apps need token-based auth — add Laravel Sanctum and issue personal access tokens
- No API Resource transformers exist yet — need `CourseResource`, `LessonResource`, `EnrollmentResource` classes to control the JSON shape and avoid over-exposing model attributes
- Livewire components mix display logic with data fetching — fine for web, but the API controllers will need to replicate the query logic from the Livewire components

---

### Corporate Multi-Tenant Accounts

**What the current architecture already supports**:
- The `is_admin` flag demonstrates the concept of role-based access already — extending this to a full `Role` model is a natural evolution
- Policies are centralized and already use `before()` hooks — adding a tenant membership check to `before()` is straightforward

**What would need refactoring**:
- This is the largest structural change of the four. Every model (`User`, `Course`, `Enrollment`, `Certificate`, `Lesson`) needs a `tenant_id` FK and a global scope to automatically filter by the current tenant
- Filament admin currently has no tenant concept — the panel would need to be split into a super-admin panel (cross-tenant) and a company-admin panel (single-tenant scoped)
- Course visibility rules change significantly: a corporate account may want private courses visible only to their employees, not the public catalog
- Database strategy decision: shared schema with `tenant_id` (simpler to build, harder to isolate) vs separate DB per tenant (better isolation, harder to operate)

---

### Gamification Badges

**What the current architecture already supports**:
- The event system is already firing the right signals: `UserEnrolled`, `LessonCompleted`, `CourseCompleted`. Badge rules are just listeners on these events — `OnFirstEnrollmentBadge`, `OnCourseCompletedBadge`, `OnStreakBadge`
- `IssueCertificateOnCompletion` is already the pattern for "award something when a condition is met" — badges follow the exact same structure
- The queued listener architecture means badge checks don't block the main request

**What would need refactoring**:
- Need `Badge` and `UserBadge` models (minimal schema: `badge_id`, `user_id`, `earned_at`)
- Streak badges require tracking consecutive daily activity — need a `last_activity_at` column on `users` or a dedicated `activity_log` table, since the current schema has no concept of daily activity
- The student dashboard (`StudentDashboard` Livewire component) would need a badges section — a display change, not an architectural one
- Otherwise this fits cleanly into the current event-driven architecture with minimal refactoring

---

## 4. Trade-offs

### Trade-off 1 — Dynamic completion criteria vs enrollment snapshot

**What I chose**: Evaluate completion against the current set of non-deleted required lessons at check time, not against a snapshot captured at enrollment.

**The cost**: A student who is 9 out of 10 lessons complete can have a new required lesson added by an admin and suddenly needs to complete one more. This feels unfair from the student's perspective.

**Why I chose it anyway**: The snapshot approach means a student enrolled in January and a student enrolled in March could have different completion requirements for the same course — one passes, one doesn't, even though they did the same work. This is worse for course integrity. Instructors fix errors and add critical content; the snapshot would leave early enrollees with an incomplete education. The mitigating case (admin deletes a lesson) is handled by the `LessonObserver` auto-completing enrollments, so the only painful direction is "requirements increased," which can be softened with a notification system later.

---

### Trade-off 2 — Asynchronous side effects vs synchronous simplicity

**What I chose**: Certificate issuance and completion emails are dispatched to a Redis queue and handled by a separate worker process, not inline in the HTTP request.

**The cost**: The idempotency problem becomes significantly harder. A synchronous flow has no retries to worry about. The async approach requires atomic writes, `affected > 0` guards, and careful event placement — all of which added meaningful complexity to the codebase.

**Why I chose it anyway**: If certificate issuance or email sending fails (mail server is down, queue worker crashes), the student's "Mark Complete" action should still succeed immediately. A synchronous approach means a failing mail server returns a 500 to a student who just finished a course. Async isolates failures: the student sees success, and the background job retries until the email goes through. The idempotency complexity is a one-time engineering cost; the UX benefit is permanent.

---

### Trade-off 3 — Soft deletes on lessons, no soft deletes on enrollments

**What I chose**: `lessons` use `SoftDeletes`; `enrollments` do not.

**The cost**: Every query against lessons must remember to filter `WHERE deleted_at IS NULL`. Forgetting this scope returns incorrect completion counts. The `AllRequiredLessonsCompletionStrategy` and all Filament resource queries carry this cognitive burden. Soft-deleted lesson rows also accumulate indefinitely in the table.

**Why I chose it this way**: Hard-deleting a lesson would cascade-delete all `lesson_progress` rows referencing it (via the FK constraint). A student who completed lesson X would lose that progress record, and their completion percentage would drop. Worse, if they had already completed the course, the certificate becomes based on a now-inconsistent progress history. Soft deletes preserve the audit trail. Enrollments, on the other hand, have no child records that need protecting in the same way — and allowing clean re-enrollment (fresh start) is the right UX for the case where a student drops and re-joins a course.

---

## Key Product Decisions

### Why `is_required` on lessons?

**Alternative considered**: Track lesson completion based on enrollment snapshot (lessons at enrollment time).

**Decision**: Always evaluate against the current set of non-deleted required lessons.

**Reasoning**: Instructors regularly update courses — fixing errors, adding important content. The "snapshot at enrollment" approach means enrolled students miss critical updates. Current approach ensures all students complete the same curriculum.

### Why UUID for certificates?

UUIDs provide:
1. **Shareability**: Students can share certificate URLs without exposing sequential IDs
2. **Verification**: Employers can verify authenticity via the public URL
3. **Non-guessability**: Prevents enumeration attacks (can't guess other students' certificates)

### Why `completion_email_sent_at` column?

The naive approach fires the completion email from a queue listener. If the queue retries (network error, process crash), the email fires again. `completion_email_sent_at` uses an atomic UPDATE to claim the "email slot":

```sql
UPDATE certificates
SET completion_email_sent_at = NOW()
WHERE id = ? AND completion_email_sent_at IS NULL
```

Only the first UPDATE succeeds (returns 1 affected row). The email is sent only if `affected > 0`.

---

## Technical Debt Acknowledgments

1. **No pagination on lesson list** — acceptable for MVP; add Livewire infinite scroll at scale
2. **No CDN for video** — YouTube embeds work but limit control; consider Cloudflare Stream
3. **SQLite in tests, MySQL in prod** — potential date/string function differences; use same DB engine to be safe
4. **No rate limiting on enrollment** — add throttle middleware in production
5. **No isolated mail environment** — configure Mailtrap or Mailpit in dev to catch outgoing emails without hitting real inboxes
