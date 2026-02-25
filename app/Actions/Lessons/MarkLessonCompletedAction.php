<?php

namespace App\Actions\Lessons;

use App\Contracts\Repositories\ProgressRepositoryInterface;
use App\Events\LessonCompleted;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MarkLessonCompletedAction
{
    public function __construct(
        private ProgressRepositoryInterface $progress
    ) {}

    public function handle(User $user, Lesson $lesson, Enrollment $enrollment): LessonProgress
    {
        $wasNewlyCompleted = false;

        $progress = DB::transaction(function () use ($user, $lesson, $enrollment, &$wasNewlyCompleted) {
            $progress = $this->progress->firstOrCreate(
                $user->id,
                $lesson->id,
                $enrollment->id
            );

            if ($progress->completed_at === null) {
                $wasNewlyCompleted = true;
                $progress = $this->progress->markCompleted($progress);
            }

            return $progress;
        });

        if ($wasNewlyCompleted) {
            event(new LessonCompleted($user, $lesson, $enrollment));
        }

        return $progress;
    }
}
