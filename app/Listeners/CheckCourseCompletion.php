<?php

namespace App\Listeners;

use App\Actions\Progress\CalculateCourseCompletionAction;
use App\Events\CourseCompleted;
use App\Events\LessonCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class CheckCourseCompletion implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';
    public int $tries    = 3;

    public function __construct(
        private CalculateCourseCompletionAction $completionAction,
    ) {}

    public function handle(LessonCompleted $event): void
    {
        $enrollment = $event->enrollment->fresh();

        if ($enrollment->isCompleted()) {
            return;
        }

        if (! $this->completionAction->handle($enrollment)) {
            return;
        }

        $affected = DB::table('enrollments')
            ->where('id', $enrollment->id)
            ->whereNull('completed_at')
            ->update(['completed_at' => now(), 'updated_at' => now()]);

        if ($affected > 0) {
            $enrollment = $enrollment->fresh();
            event(new CourseCompleted(
                $event->user,
                $event->lesson->course,
                $enrollment
            ));
        }
    }
}
