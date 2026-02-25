<?php

namespace App\Observers;

use App\Actions\Progress\CalculateCourseCompletionAction;
use App\Events\CourseCompleted;
use App\Models\Enrollment;
use App\Models\Lesson;
use Illuminate\Support\Facades\DB;

class LessonObserver
{
    public function __construct(
        private CalculateCourseCompletionAction $completionAction,
    ) {}

    public function deleted(Lesson $lesson): void
    {
        if (! $lesson->is_required) {
            return;
        }

        $this->recheckActiveEnrollments($lesson->course_id);
    }

    public function updated(Lesson $lesson): void
    {
        if (! $lesson->wasChanged('is_required')) {
            return;
        }

        $this->recheckActiveEnrollments($lesson->course_id);
    }

    private function recheckActiveEnrollments(int $courseId): void
    {
        Enrollment::where('course_id', $courseId)
            ->active()
            ->each(function (Enrollment $enrollment) {
                if (! $this->completionAction->handle($enrollment)) {
                    return;
                }

                $affected = DB::table('enrollments')
                    ->where('id', $enrollment->id)
                    ->whereNull('completed_at')
                    ->update(['completed_at' => now(), 'updated_at' => now()]);

                if ($affected > 0) {
                    event(new CourseCompleted(
                        $enrollment->user,
                        $enrollment->course,
                        $enrollment->fresh()
                    ));
                }
            });
    }
}
