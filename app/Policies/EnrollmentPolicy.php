<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EnrollmentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): bool|null
    {
        if ($user->is_admin) {
            return true;
        }

        return null;
    }

    /**
     * Users can only view their own enrollments (user data isolation).
     */
    public function view(User $user, Enrollment $enrollment): bool
    {
        return $user->id === $enrollment->user_id;
    }
}
