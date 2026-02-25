<?php

use App\Actions\Enrollment\EnrollUserAction;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

describe('EnrollUserAction Edge Cases', function () {

    it('handles race condition gracefully: returns existing enrollment when DB constraint fires', function () {
        $user   = User::factory()->create();
        $course = Course::factory()->published()->create();

        // Pre-insert enrollment to simulate "winner" of the race
        $existing = Enrollment::create([
            'user_id'     => $user->id,
            'course_id'   => $course->id,
            'enrolled_at' => now(),
        ]);

        // Action should catch duplicate and return existing, not throw
        $result = app(EnrollUserAction::class)->handle($user, $course);

        expect($result->id)->toBe($existing->id)
            ->and(Enrollment::count())->toBe(1);
    });

    it('enrollment enrolled_at is set on creation', function () {
        $user   = User::factory()->create();
        $course = Course::factory()->published()->create();

        $enrollment = app(EnrollUserAction::class)->handle($user, $course);

        expect($enrollment->enrolled_at)->not->toBeNull();
    });

    it('completed_at is null on new enrollment', function () {
        $user   = User::factory()->create();
        $course = Course::factory()->published()->create();

        $enrollment = app(EnrollUserAction::class)->handle($user, $course);

        expect($enrollment->completed_at)->toBeNull();
    });

    it('same user can enroll in multiple different courses', function () {
        $user    = User::factory()->create();
        $course1 = Course::factory()->published()->create();
        $course2 = Course::factory()->published()->create();
        $course3 = Course::factory()->published()->create();

        $action = app(EnrollUserAction::class);
        $action->handle($user, $course1);
        $action->handle($user, $course2);
        $action->handle($user, $course3);

        expect(Enrollment::where('user_id', $user->id)->count())->toBe(3);
    });
});
