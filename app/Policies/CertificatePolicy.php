<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CertificatePolicy
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
     * Users can only view their own certificates (cross-user prevention).
     */
    public function view(User $user, Certificate $certificate): bool
    {
        return $user->id === $certificate->user_id;
    }
}
