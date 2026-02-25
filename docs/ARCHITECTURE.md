# Architecture

## Overview

Mini-LMS is a Laravel 12 application with a layered architecture. The frontend is driven by **Livewire v3 + Alpine.js** for interactive components. The admin panel is **Filament v3**. All side effects (emails, certificate issuance) are handled by **queued listeners** so HTTP responses are never blocked.

```
Browser
  │
  ├── Livewire Components  (CourseList, CourseDetail, LessonPlayer, StudentDashboard)
  │     └── Actions        (EnrollUserAction, MarkLessonCompletedAction, ...)
  │           └── Repositories  (via interfaces → concrete implementations)
  │                 └── Eloquent Models
  │
  ├── HTTP Controllers     (EnrollmentController, CertificateController)
  │
  └── Filament Admin Panel (/admin)
        └── Resources      (CourseResource, LessonResource, UserResource, ...)
```

## Directory Structure

```
app/
├── Actions/               One class, one operation (handle() method)
│   ├── Certificates/
│   ├── Courses/
│   ├── Enrollment/
│   ├── Lessons/
│   └── Progress/
│
├── Contracts/
│   ├── Repositories/      Five repository interfaces
│   └── Strategies/        CompletionStrategyInterface
│
├── Events/                UserEnrolled, LessonCompleted, CourseCompleted
├── Listeners/             Queued — side effects only (email, certificate)
├── Observers/             LessonObserver — re-checks completion on lesson edits
│
├── Filament/Resources/    Admin CRUD: Course, Lesson, Enrollment, Certificate, User
├── Http/
│   ├── Controllers/       EnrollmentController, CertificateController
│   └── Middleware/        EnsureEnrolled
│
├── Livewire/              Volt + class-based components
│   ├── Courses/           CourseList, CourseDetail
│   └── Lessons/           LessonPlayer
│
├── Models/                Course, Lesson, Enrollment, LessonProgress, Certificate, User
├── Observers/             LessonObserver
├── Policies/              Course, Lesson, Enrollment, Certificate
├── Providers/             AppServiceProvider, RepositoryServiceProvider, VoltServiceProvider
├── Repositories/          Concrete implementations of repository interfaces
└── Strategies/            AllRequiredLessonsCompletionStrategy
```

## Routes

| Method | URL | Handler | Auth |
|---|---|---|---|
| GET | `/` | `CourseList` (Livewire) | public |
| GET | `/courses/{course:slug}` | `CourseDetail` (Livewire) | public |
| GET | `/courses/{course:slug}/lessons/{lesson:slug}` | `LessonPlayer` (Livewire) | public (free preview) or enrolled |
| POST | `/courses/{course}/enroll` | `EnrollmentController@store` | auth + verified |
| GET | `/certificates/{uuid}` | `CertificateController@show` | auth |
| GET | `/dashboard` | `StudentDashboard` (Livewire) | auth + verified |
| GET | `/admin/*` | Filament panel | auth + `is_admin` |
| GET/POST | `/login`, `/register`, `/forgot-password`, etc. | Livewire Volt | guest |

## Request Lifecycle — Enrolling in a Course

```
POST /courses/{course}/enroll
  │
  ├─ EnsureAuthenticated + EnsureEmailVerified middleware
  │
  └─ EnrollmentController::store()
        └─ authorize('enroll', $course)       ← EnrollmentPolicy (not already enrolled)
              └─ EnrollUserAction::handle()
                    ├─ Enrollment::firstOrCreate()  ← idempotent, safe on double-submit
                    ├─ DB UNIQUE(user_id, course_id) ← race-condition safety net
                    └─ event(new UserEnrolled(...)) ← AFTER the DB write
```

## Request Lifecycle — Completing a Lesson

```
Livewire: LessonPlayer::markComplete()
  │
  └─ MarkLessonCompletedAction::handle()
        ├─ LessonProgress::firstOrCreate()    ← idempotent
        ├─ Update completed_at
        └─ event(new LessonCompleted(...))    ← dispatched AFTER DB write
              │
              └─ CheckCourseCompletion (queued listener)
                    └─ AllRequiredLessonsCompletionStrategy::isComplete()
                          │  (queries current non-deleted required lessons)
                          │
                          ├─ false → stop
                          └─ true  → atomic UPDATE enrollments SET completed_at = NOW()
                                         WHERE completed_at IS NULL
                                           │
                                     affected > 0?
                                           │
                                           └─ event(new CourseCompleted(...))
                                                 ├─ IssueCertificateOnCompletion (queued)
                                                 └─ SendCompletionEmail (queued)
```

## Idempotency — Defense in Depth

Every write operation is safe to retry without producing duplicates:

| Operation | Guard |
|---|---|
| Enroll user | `Enrollment::firstOrCreate()` + DB UNIQUE(`user_id`, `course_id`) |
| Start lesson | `LessonProgress::firstOrCreate()` |
| Complete lesson | `whereNull('completed_at')->update(...)` — no-op on re-run |
| Mark course complete | Atomic `UPDATE WHERE completed_at IS NULL`, check `affected > 0` |
| Issue certificate | `Certificate::firstOrCreate()` + DB UNIQUE(`enrollment_id`) |
| Send completion email | Atomic `UPDATE WHERE completion_email_sent_at IS NULL`, check `affected > 0` |

**Critical rule:** All `event()` calls happen **outside** database transactions. Events dispatched inside a transaction can fire before the commit, causing listeners to see stale data or fire on rows that are later rolled back.

## Event / Listener Map

| Event | Listener | Queue | Notes |
|---|---|---|---|
| `Registered` (Laravel) | `SendWelcomeEmail` | `default` | Sends welcome email on signup |
| `UserEnrolled` | — | — | Available for future notifications |
| `LessonCompleted` | `CheckCourseCompletion` | `default` | Checks if all required lessons are done |
| `CourseCompleted` | `IssueCertificateOnCompletion` | `default` | Creates certificate row |
| `CourseCompleted` | `SendCompletionEmail` | `default` | Emails certificate, once-only |

## Completion Strategy

The completion check is behind an interface so the algorithm can be swapped without touching any listener or action:

```php
interface CompletionStrategyInterface {
    public function isComplete(Enrollment $enrollment): bool;
}
```

`AllRequiredLessonsCompletionStrategy` queries **only non-deleted, required** lessons at check time:

```php
$requiredIds = $enrollment->course->lessons()
    ->where('is_required', true)
    ->whereNull('deleted_at')   // respects lessons removed after enrollment
    ->pluck('id');

if ($requiredIds->isEmpty()) return false;

$completed = LessonProgress::where('enrollment_id', $enrollment->id)
    ->whereIn('lesson_id', $requiredIds)
    ->whereNotNull('completed_at')
    ->count();

return $completed >= $requiredIds->count();
```

Because the query is dynamic, adding or removing a required lesson after enrollment automatically changes the completion threshold — no backfill or migration needed.

## LessonObserver

When an admin edits lessons, active enrollments are automatically re-evaluated:

| Trigger | Action |
|---|---|
| Required lesson soft-deleted | Re-check all active enrollments for that course |
| `is_required` toggled | Re-check all active enrollments for that course |

The observer uses the same atomic `UPDATE WHERE completed_at IS NULL` pattern to guarantee `CourseCompleted` fires at most once per enrollment.

## Authorization

All access control goes through Laravel Policies. Admins bypass all checks via `Policy::before()`.

| Policy | Key Rules |
|---|---|
| `CoursePolicy` | `view`: published or admin; `update`/`delete`: admin only |
| `LessonPolicy` | `view`: enrolled OR free preview (nullable `$user`); admin bypass |
| `EnrollmentPolicy` | `enroll`: authenticated, not already enrolled, course published |
| `CertificatePolicy` | `view`: owns the certificate or admin |

## Middleware

| Middleware | Applied To | Purpose |
|---|---|---|
| `auth` | Dashboard, enroll, certificate | Require login |
| `verified` | Dashboard, enroll | Require email verification |
| `EnsureEnrolled` | LessonPlayer (via policy) | Checked in `LessonPolicy::view()` |

## Livewire Route Model Binding

Livewire v3 resolves route parameters into typed public properties via `ImplicitRouteBinding`. The `mount()` signature must use model type hints:

```php
// Correct — Livewire resolves the slug and passes a bound model
public function mount(Course $course, Lesson $lesson): void { ... }

// Wrong — Livewire passes the raw slug string; model lookup fails
public function mount(string $course, string $lesson): void { ... }
```

Models expose `getRouteKeyName(): string { return 'slug'; }` so all URLs use human-readable slugs instead of integer IDs.

## Slug Generation

`GenerateCourseSlugAction` checks `withTrashed()` to avoid reusing slugs from soft-deleted courses:

```php
while (Course::withTrashed()->where('slug', $candidate)->exists()) {
    $candidate = $base . '-' . $suffix++;
}
```

## Repository Pattern

All data access goes through interfaces, bound in `RepositoryServiceProvider`. Actions depend on interfaces, not concrete classes.

```
RepositoryServiceProvider binds:
  CourseRepositoryInterface     → CourseRepository
  LessonRepositoryInterface     → LessonRepository
  EnrollmentRepositoryInterface → EnrollmentRepository
  ProgressRepositoryInterface   → ProgressRepository
  CertificateRepositoryInterface→ CertificateRepository
```

This makes swapping the data layer (e.g., adding a cache layer) possible without touching any Action class.

## Filament Admin

| Resource | Notes |
|---|---|
| `CourseResource` | Slug auto-generated on title change via `afterStateUpdated` |
| `LessonResource` | Drag-to-reorder `order_column` with SortableList; scoped to parent course |
| `EnrollmentResource` | Read-only list; shows enrollment and completion dates |
| `CertificateResource` | Read-only; displays UUID, user, course, issued date |
| `UserResource` | Create/edit users; toggle `is_admin` |
| `StatsOverviewWidget` | Dashboard widgets: total users, courses, enrollments, certificates |
| `LatestEnrollmentsWidget` | Recent enrollments table on admin dashboard |
