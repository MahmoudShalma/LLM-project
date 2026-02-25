# Mini LMS

A production-quality Learning Management System built with Laravel 12, Livewire v3, Alpine.js, Filament v3, and Docker.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 |
| Frontend | Livewire v3 + Alpine.js |
| Admin | Filament v3 |
| Testing | Pest |
| Database | MySQL 8.0 |
| Video | Plyr.js |
| Infrastructure | Docker + Docker Compose + Redis |

## Quick Start

```bash
# 1. Clone and enter directory
git clone https://github.com/MahmoudShalma/LLM-project && cd LLM-project

# 2. Copy environment file
cp .env.example .env

# 3. Build and start all containers
docker compose up --build -d

# 4. Bootstrap the application (first time only)
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
docker compose exec app npm install && npm run build

# 5. Open in browser
open http://localhost:8080
open http://localhost:8080/admin   # Filament admin panel
```

## Default Credentials

| Role | Email | Password |
|---|---|---|
| Admin | admin@minilms.com | password |
| Student | student@minilms.com | password |

## Running Tests

```bash
# Run all tests
docker compose exec app php artisan test

# Run specific test file
docker compose exec app ./vendor/bin/pest tests/Feature/Enrollment/

# Run with coverage (requires Xdebug)
docker compose exec app ./vendor/bin/pest --coverage
```

## Docker Services

| Service | Description | Port |
|---|---|---|
| `app` | PHP 8.3-FPM (Laravel) | — |
| `web` | Nginx 1.25 | 8080 |
| `db` | MySQL 8.0 | 3307 (host) |
| `queue` | Queue worker (same image) | — |
| `redis` | Redis 7 (queue + cache) | 6380 (host) |

## Project Structure

```
app/
├── Actions/           # Single-responsibility business logic
│   ├── Courses/       # GenerateCourseSlugAction
│   ├── Enrollment/    # EnrollUserAction
│   ├── Lessons/       # MarkLessonStartedAction, MarkLessonCompletedAction
│   ├── Progress/      # CalculateCourseCompletionAction
│   └── Certificates/  # IssueCertificateAction
├── Contracts/
│   ├── Repositories/  # 5 repository interfaces (DIP)
│   └── Strategies/    # CompletionStrategyInterface
├── Events/            # UserEnrolled, LessonCompleted, CourseCompleted
├── Filament/          # Admin resources (Courses, Lessons, Users, Enrollments, Certs)
├── Listeners/         # Queued event handlers with idempotency guards
├── Livewire/          # CourseList, CourseDetail, LessonPlayer
├── Models/            # Eloquent models with relationships and scopes
├── Policies/          # Authorization policies
├── Repositories/      # Concrete repository implementations
└── Strategies/        # AllRequiredLessonsCompletionStrategy
```

## Key Features

- **Public Course Listing** — no N+1 queries, eager-loaded with pagination
- **Guest Free-Preview** — lessons marked `is_free_preview` are accessible without login
- **Enrollment Idempotency** — `UNIQUE(user_id, course_id)` + `firstOrCreate` + race condition handling
- **Lesson Progress** — per-user progress tracking with `UNIQUE(user_id, lesson_id)` guard
- **Completion Detection** — strategy pattern, handles dynamic lesson additions and soft deletes
- **Certificate Generation** — UUID-based, one-per-enrollment guaranteed by DB constraint
- **Email-Once Guard** — `completion_email_sent_at` atomic UPDATE prevents duplicate emails
- **Translated UI** — All strings via `lang/en/lms.php`; `lang/ar/lms.php` prepared but no language switcher yet
- **Dark Mode** — Alpine.js `localStorage` persistence
- **Admin Panel** — Full Filament v3 with drag-to-reorder lessons, slug auto-generation

## Architecture Decisions

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for detailed architectural decisions.

See [docs/PRODUCT_THINKING.md](docs/PRODUCT_THINKING.md) for product reasoning and trade-offs.

See [docs/ERD.md](docs/ERD.md) for the entity-relationship diagram.
