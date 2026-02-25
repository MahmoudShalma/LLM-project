<?php

namespace App\Actions\Enrollment;

use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Events\UserEnrolled;
use App\Exceptions\CourseNotPublishedException;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class EnrollUserAction
{
    public function __construct(
        private EnrollmentRepositoryInterface $enrollments
    ) {}

    public function handle(User $user, Course $course): Enrollment
    {
        if (! $course->isPublished()) {
            throw new CourseNotPublishedException();
        }

        $wasNew = false;

        $enrollment = DB::transaction(function () use ($user, $course, &$wasNew) {
            try {
                $existing = $this->enrollments->findByUserAndCourse($user->id, $course->id);

                if ($existing) {
                    return $existing;
                }

                $wasNew = true;
                return $this->enrollments->firstOrCreate($user->id, $course->id);
            } catch (QueryException $e) {
                if (str_contains($e->getMessage(), 'Duplicate entry') ||
                    str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                    $wasNew = false;
                    return $this->enrollments->findByUserAndCourse($user->id, $course->id);
                }
                throw $e;
            }
        });

        if ($wasNew) {
            event(new UserEnrolled($user, $course, $enrollment));
        }

        return $enrollment;
    }
}
