<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProgressRepositoryInterface;
use App\Models\LessonProgress;

class ProgressRepository implements ProgressRepositoryInterface
{
    public function findByUserAndLesson(int $userId, int $lessonId): ?LessonProgress
    {
        return LessonProgress::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();
    }

    public function firstOrCreate(int $userId, int $lessonId, int $enrollmentId): LessonProgress
    {
        return LessonProgress::firstOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            [
                'enrollment_id' => $enrollmentId,
                'started_at'    => now(),
            ]
        );
    }

    public function markCompleted(LessonProgress $progress): LessonProgress
    {
        $progress->update(['completed_at' => now()]);
        return $progress->fresh();
    }
}
