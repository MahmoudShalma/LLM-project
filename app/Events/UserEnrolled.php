<?php

namespace App\Events;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEnrolled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Course $course,
        public readonly Enrollment $enrollment,
    ) {}
}
