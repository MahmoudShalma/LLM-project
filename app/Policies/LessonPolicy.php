<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LessonPolicy
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
     * Users can view a lesson if:
     * - It's a free preview (accessible to all, including guests)
     * - OR they are enrolled in the course
     */
    public function view(?User $user, Lesson $lesson): bool
    {
        // Free preview is accessible to everyone including guests
        if ($lesson->is_free_preview) {
            return true;
        }

        // Non-preview lessons require authentication and enrollment
        if (! $user) {
            return false;
        }

        return $user->isEnrolledIn($lesson->course);
    }

    /**
     * Only enrolled users can mark lessons as completed.
     */
    public function complete(User $user, Lesson $lesson): bool
    {
        return $user->isEnrolledIn($lesson->course);
    }
}
