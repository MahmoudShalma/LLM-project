<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    /**
     * Admins can do everything.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->is_admin) {
            return true;
        }

        return null;
    }

    /**
     * Guests and authenticated users can view published courses.
     */
    public function view(?User $user, Course $course): bool
    {
        return $course->isPublished();
    }

    /**
     * Only authenticated users can enroll, and only in published courses.
     */
    public function enroll(User $user, Course $course): bool
    {
        return $course->isPublished();
    }
}
