<?php

namespace App\Actions\Lessons;

use App\Contracts\Repositories\ProgressRepositoryInterface;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MarkLessonStartedAction
{
    public function __construct(
        private ProgressRepositoryInterface $progress
    ) {}

    public function handle(User $user, Lesson $lesson, Enrollment $enrollment): LessonProgress
    {
        return DB::transaction(function () use ($user, $lesson, $enrollment) {
            return $this->progress->firstOrCreate(
                $user->id,
                $lesson->id,
                $enrollment->id
            );
        });
    }
}
