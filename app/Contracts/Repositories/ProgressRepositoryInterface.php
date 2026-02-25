<?php

namespace App\Contracts\Repositories;

use App\Models\LessonProgress;

interface ProgressRepositoryInterface
{
    public function findByUserAndLesson(int $userId, int $lessonId): ?LessonProgress;

    public function firstOrCreate(int $userId, int $lessonId, int $enrollmentId): LessonProgress;

    public function markCompleted(LessonProgress $progress): LessonProgress;
}
