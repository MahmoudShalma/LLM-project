<?php

namespace App\Contracts\Repositories;

use App\Models\Lesson;

interface LessonRepositoryInterface
{
    public function findById(int $id): ?Lesson;
}
