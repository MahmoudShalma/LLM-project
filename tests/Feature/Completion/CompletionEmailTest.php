<?php

use App\Events\CourseCompleted;
use App\Listeners\SendCompletionEmail;
use App\Mail\CourseCompletionMail;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

describe('Completion Email Idempotency', function () {

    it('sends completion email exactly once on first call', function () {
        Mail::fake();

        $enrollment  = Enrollment::factory()->completed()->create();
        $certificate = Certificate::factory()->create([
            'user_id'       => $enrollment->user_id,
            'course_id'     => $enrollment->course_id,
            'enrollment_id' => $enrollment->id,
            'completion_email_sent_at' => null,
        ]);

        $event    = new CourseCompleted($enrollment->user, $enrollment->course, $enrollment);
        $listener = app(SendCompletionEmail::class);
        $listener->handle($event);

        Mail::assertSent(CourseCompletionMail::class, 1);
        expect(Certificate::find($certificate->id)->completion_email_sent_at)->not->toBeNull();
    });

    it('does NOT send email again on queue retry (idempotency)', function () {
        Mail::fake();

        $enrollment  = Enrollment::factory()->completed()->create();
        $certificate = Certificate::factory()->create([
            'user_id'       => $enrollment->user_id,
            'course_id'     => $enrollment->course_id,
            'enrollment_id' => $enrollment->id,
            'completion_email_sent_at' => now(), // Already sent
        ]);

        $event    = new CourseCompleted($enrollment->user, $enrollment->course, $enrollment);
        $listener = app(SendCompletionEmail::class);
        $listener->handle($event);
        $listener->handle($event); // Simulate retry

        Mail::assertNothingSent();
    });

    it('sends email exactly once even when listener called multiple times', function () {
        Mail::fake();

        $enrollment  = Enrollment::factory()->completed()->create();
        $certificate = Certificate::factory()->create([
            'user_id'       => $enrollment->user_id,
            'course_id'     => $enrollment->course_id,
            'enrollment_id' => $enrollment->id,
            'completion_email_sent_at' => null,
        ]);

        $event    = new CourseCompleted($enrollment->user, $enrollment->course, $enrollment);
        $listener = app(SendCompletionEmail::class);

        // Call three times
        $listener->handle($event);
        $listener->handle($event);
        $listener->handle($event);

        // Only sent once
        Mail::assertSent(CourseCompletionMail::class, 1);
    });
});
