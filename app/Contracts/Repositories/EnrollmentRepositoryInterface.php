<?php

namespace App\Contracts\Repositories;

use App\Models\Enrollment;

interface EnrollmentRepositoryInterface
{
    public function findByUserAndCourse(int $userId, int $courseId): ?Enrollment;

    public function firstOrCreate(int $userId, int $courseId): Enrollment;
}
