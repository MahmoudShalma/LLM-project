<?php

namespace App\Strategies;

use App\Contracts\Strategies\CompletionStrategyInterface;
use App\Models\Enrollment;
use App\Models\LessonProgress;

class AllRequiredLessonsCompletionStrategy implements CompletionStrategyInterface
{
    public function isComplete(Enrollment $enrollment): bool
    {
        $requiredLessonIds = $enrollment->course
            ->lessons()
            ->required()
            ->whereNull('deleted_at')
            ->pluck('id');

        if ($requiredLessonIds->isEmpty()) {
            return false;
        }

        $completedCount = LessonProgress::where('enrollment_id', $enrollment->id)
            ->whereIn('lesson_id', $requiredLessonIds)
            ->whereNotNull('completed_at')
            ->count();

        return $completedCount >= $requiredLessonIds->count();
    }
}
