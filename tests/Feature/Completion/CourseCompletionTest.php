<?php

use App\Actions\Lessons\MarkLessonCompletedAction;
use App\Events\CourseCompleted;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\Event;

describe('Course Completion Flow', function () {

    it('does not mark course complete when only some required lessons done', function () {
        $user       = User::factory()->create();
        $course     = Course::factory()->published()->create();
        $lessons    = Lesson::factory()->required()->count(3)->for($course)->create();
        $enrollment = Enrollment::factory()->for($user)->for($course)->create();

        $action = app(MarkLessonCompletedAction::class);
        $action->handle($user, $lessons[0], $enrollment);
        $action->handle($user, $lessons[1], $enrollment);

        expect($enrollment->fresh()->completed_at)->toBeNull();
    });

    it('marks course complete when all required lessons are done', function () {
        $user       = User::factory()->create();
        $course     = Course::factory()->published()->create();
        $lessons    = Lesson::factory()->required()->count(3)->for($course)->create();
        Lesson::factory()->optional()->for($course)->create();
        $enrollment = Enrollment::factory()->for($user)->for($course)->create();

        $action = app(MarkLessonCompletedAction::class);
        $action->handle($user, $lessons[0], $enrollment);
        $action->handle($user, $lessons[1], $enrollment);
        $action->handle($user, $lessons[2], $enrollment);

        expect($enrollment->fresh()->completed_at)->not->toBeNull();
    });

    it('does not count optional lessons toward completion', function () {
        $user       = User::factory()->create();
        $course     = Course::factory()->published()->create();
        Lesson::factory()->required()->for($course)->create();
        $optional   = Lesson::factory()->optional()->for($course)->create();
        $enrollment = Enrollment::factory()->for($user)->for($course)->create();

        $action = app(MarkLessonCompletedAction::class);
        // Only complete optional lesson — course should NOT be complete
        $action->handle($user, $optional, $enrollment);

        expect($enrollment->fresh()->completed_at)->toBeNull();
    });

    it('handles courses with lessons added after enrollment', function () {
        $user     = User::factory()->create();
        $course   = Course::factory()->published()->create();
        // Two required lessons so completing only one does NOT trigger completion
        $lesson1  = Lesson::factory()->required()->for($course)->create(['order_column' => 1]);
        $lesson2  = Lesson::factory()->required()->for($course)->create(['order_column' => 2]);
        $enrollment = Enrollment::factory()->for($user)->for($course)->create();

        $action = app(MarkLessonCompletedAction::class);
        $action->handle($user, $lesson1, $enrollment);

        // lesson2 not yet done — course should still be incomplete
        expect($enrollment->fresh()->completed_at)->toBeNull();

        // Admin adds a 3rd required lesson mid-enrollment
        $newLesson = Lesson::factory()->required()->for($course)->create(['order_column' => 3]);

        // Complete lesson2 — lessons 1 & 2 done, but newLesson still pending
        $action->handle($user, $lesson2, $enrollment->fresh());
        expect($enrollment->fresh()->completed_at)->toBeNull();

        // Complete the newly added lesson — all 3 required done → course completes
        $action->handle($user, $newLesson, $enrollment->fresh());
        expect($enrollment->fresh()->completed_at)->not->toBeNull();
    });

    it('handles courses with required lessons soft-deleted after enrollment', function () {
        $user       = User::factory()->create();
        $course     = Course::factory()->published()->create();
        $lesson1    = Lesson::factory()->required()->for($course)->create(['order_column' => 1]);
        $lesson2    = Lesson::factory()->required()->for($course)->create(['order_column' => 2]);
        $enrollment = Enrollment::factory()->for($user)->for($course)->create();

        $action = app(MarkLessonCompletedAction::class);
        $action->handle($user, $lesson1, $enrollment);

        // Admin soft-deletes lesson2
        $lesson2->delete();

        // Run the listener again — only lesson1 is now required and completed
        $listener = app(\App\Listeners\CheckCourseCompletion::class);
        $listener->handle(new \App\Events\LessonCompleted($user, $lesson1, $enrollment->fresh()));

        expect($enrollment->fresh()->completed_at)->not->toBeNull();
    });

    it('auto-completes enrollment when admin deletes the last remaining required lesson', function () {
        Event::fake([CourseCompleted::class]);

        $user       = User::factory()->create();
        $course     = Course::factory()->published()->create();
        $lesson1    = Lesson::factory()->required()->for($course)->create(['order_column' => 1]);
        $lesson2    = Lesson::factory()->required()->for($course)->create(['order_column' => 2]);
        $enrollment = Enrollment::factory()->for($user)->for($course)->create();

        $action = app(MarkLessonCompletedAction::class);
        $action->handle($user, $lesson1, $enrollment);

        expect($enrollment->fresh()->completed_at)->toBeNull();

        // Admin soft-deletes lesson2 — observer should auto-complete enrollment
        $lesson2->delete();

        expect($enrollment->fresh()->completed_at)->not->toBeNull();
        Event::assertDispatched(CourseCompleted::class);
    });

    it('auto-completes enrollment when admin changes last remaining lesson from required to optional', function () {
        Event::fake([CourseCompleted::class]);

        $user       = User::factory()->create();
        $course     = Course::factory()->published()->create();
        $lesson1    = Lesson::factory()->required()->for($course)->create(['order_column' => 1]);
        $lesson2    = Lesson::factory()->required()->for($course)->create(['order_column' => 2]);
        $enrollment = Enrollment::factory()->for($user)->for($course)->create();

        $action = app(MarkLessonCompletedAction::class);
        $action->handle($user, $lesson1, $enrollment);

        expect($enrollment->fresh()->completed_at)->toBeNull();

        // Admin marks lesson2 as optional — observer should auto-complete
        $lesson2->update(['is_required' => false]);

        expect($enrollment->fresh()->completed_at)->not->toBeNull();
        Event::assertDispatched(CourseCompleted::class);
    });

    it('does not auto-complete when deleting an optional lesson', function () {
        $user       = User::factory()->create();
        $course     = Course::factory()->published()->create();
        $lesson1    = Lesson::factory()->required()->for($course)->create(['order_column' => 1]);
        $lesson2    = Lesson::factory()->required()->for($course)->create(['order_column' => 2]);
        $optional   = Lesson::factory()->optional()->for($course)->create(['order_column' => 3]);
        $enrollment = Enrollment::factory()->for($user)->for($course)->create();

        $action = app(MarkLessonCompletedAction::class);
        $action->handle($user, $lesson1, $enrollment);

        // Delete optional lesson — should NOT trigger completion (lesson2 still required)
        $optional->delete();

        expect($enrollment->fresh()->completed_at)->toBeNull();
    });

    it('does not auto-complete already-completed enrollments on lesson delete', function () {
        $user       = User::factory()->create();
        $course     = Course::factory()->published()->create();
        $lesson1    = Lesson::factory()->required()->for($course)->create(['order_column' => 1]);
        $lesson2    = Lesson::factory()->required()->for($course)->create(['order_column' => 2]);
        $enrollment = Enrollment::factory()->for($user)->for($course)->create();

        $action = app(MarkLessonCompletedAction::class);
        $action->handle($user, $lesson1, $enrollment);
        $action->handle($user, $lesson2, $enrollment->fresh());

        $completedAt = $enrollment->fresh()->completed_at;
        expect($completedAt)->not->toBeNull();

        // Delete lesson — should not change the already-completed enrollment
        $lesson2->delete();

        expect($enrollment->fresh()->completed_at->toDateTimeString())->toBe($completedAt->toDateTimeString());
    });

    it('does not re-fire LessonCompleted or CourseCompleted when lesson already marked done', function () {
        Event::fake([CourseCompleted::class]);

        $user       = User::factory()->create();
        $course     = Course::factory()->published()->create();
        $lesson     = Lesson::factory()->required()->for($course)->create();
        $enrollment = Enrollment::factory()->for($user)->for($course)->create();

        $action = app(MarkLessonCompletedAction::class);
        $action->handle($user, $lesson, $enrollment);          // First time — fires event
        $action->handle($user, $lesson, $enrollment->fresh()); // Duplicate — should NOT re-fire

        Event::assertDispatchedTimes(CourseCompleted::class, 1);
    });
});
