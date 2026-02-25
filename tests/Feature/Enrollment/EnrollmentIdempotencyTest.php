<?php

use App\Actions\Enrollment\EnrollUserAction;
use App\Exceptions\CourseNotPublishedException;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\QueryException;

describe('Enrollment Idempotency', function () {

    it('enrolls a user successfully in a published course', function () {
        $user   = User::factory()->create();
        $course = Course::factory()->published()->create();

        $enrollment = app(EnrollUserAction::class)->handle($user, $course);

        expect($enrollment)->toBeInstanceOf(Enrollment::class)
            ->and($enrollment->user_id)->toBe($user->id)
            ->and($enrollment->course_id)->toBe($course->id)
            ->and(Enrollment::count())->toBe(1);
    });

    it('returns same enrollment on repeated calls (idempotency)', function () {
        $user   = User::factory()->create();
        $course = Course::factory()->published()->create();

        $action = app(EnrollUserAction::class);
        $e1     = $action->handle($user, $course);
        $e2     = $action->handle($user, $course);
        $e3     = $action->handle($user, $course);

        expect($e1->id)->toBe($e2->id)
            ->and($e2->id)->toBe($e3->id)
            ->and(Enrollment::count())->toBe(1);
    });

    it('prevents duplicate enrollments via DB unique constraint', function () {
        $user   = User::factory()->create();
        $course = Course::factory()->published()->create();

        Enrollment::create([
            'user_id'     => $user->id,
            'course_id'   => $course->id,
            'enrolled_at' => now(),
        ]);

        expect(fn () => Enrollment::create([
            'user_id'     => $user->id,
            'course_id'   => $course->id,
            'enrolled_at' => now(),
        ]))->toThrow(QueryException::class);

        expect(Enrollment::count())->toBe(1);
    });

    it('throws CourseNotPublishedException for draft courses', function () {
        $user   = User::factory()->create();
        $course = Course::factory()->draft()->create();

        expect(fn () => app(EnrollUserAction::class)->handle($user, $course))
            ->toThrow(CourseNotPublishedException::class);

        expect(Enrollment::count())->toBe(0);
    });

    it('different users can enroll in the same course independently', function () {
        $user1  = User::factory()->create();
        $user2  = User::factory()->create();
        $course = Course::factory()->published()->create();

        $action = app(EnrollUserAction::class);
        $e1     = $action->handle($user1, $course);
        $e2     = $action->handle($user2, $course);

        expect($e1->id)->not->toBe($e2->id)
            ->and(Enrollment::count())->toBe(2);
    });

    it('fires UserEnrolled event only once per enrollment', function () {
        \Illuminate\Support\Facades\Event::fake([\App\Events\UserEnrolled::class]);

        $user   = User::factory()->create();
        $course = Course::factory()->published()->create();

        $action = app(EnrollUserAction::class);
        $action->handle($user, $course);
        $action->handle($user, $course); // Second call — idempotent

        \Illuminate\Support\Facades\Event::assertDispatchedTimes(\App\Events\UserEnrolled::class, 1);
    });
});
