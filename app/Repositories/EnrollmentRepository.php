<?php

namespace App\Repositories;

use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Models\Enrollment;

class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    public function findByUserAndCourse(int $userId, int $courseId): ?Enrollment
    {
        return Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
    }

    public function firstOrCreate(int $userId, int $courseId): Enrollment
    {
        return Enrollment::firstOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            ['enrolled_at' => now()]
        );
    }
}
