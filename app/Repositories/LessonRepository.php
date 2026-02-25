<?php

namespace App\Repositories;

use App\Contracts\Repositories\LessonRepositoryInterface;
use App\Models\Lesson;

class LessonRepository implements LessonRepositoryInterface
{
    public function findById(int $id): ?Lesson
    {
        return Lesson::find($id);
    }
}
