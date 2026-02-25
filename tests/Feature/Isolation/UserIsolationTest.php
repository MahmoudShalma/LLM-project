<?php

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;

describe('User Data Isolation (Policy Enforcement)', function () {

    it('prevents a user from viewing another users lesson progress page', function () {
        $userA      = User::factory()->create();
        $userB      = User::factory()->create();
        $course     = Course::factory()->published()->create();
        $lesson     = Lesson::factory()->required()->for($course)->create();

        // Only userA is enrolled
        Enrollment::factory()->for($userA)->for($course)->create();

        // userB is NOT enrolled — cannot access non-preview lesson
        $response = $this->actingAs($userB)
            ->get(route('lessons.show', [$course->slug, $lesson->slug]));

        $response->assertForbidden();
    });

    it('prevents a user from viewing another users certificate', function () {
        $userA       = User::factory()->create();
        $userB       = User::factory()->create();
        $course      = Course::factory()->published()->create();
        $enrollment  = Enrollment::factory()->completed()->for($userA)->for($course)->create();

        $certificate = Certificate::factory()->create([
            'uuid'          => \Illuminate\Support\Str::uuid(),
            'user_id'       => $userA->id,
            'course_id'     => $course->id,
            'enrollment_id' => $enrollment->id,
        ]);

        // userB tries to view userA's certificate
        $response = $this->actingAs($userB)
            ->get(route('certificates.show', $certificate->uuid));

        $response->assertForbidden();
    });

    it('allows a user to view their own certificate', function () {
        $userA      = User::factory()->create();
        $course     = Course::factory()->published()->create();
        $enrollment = Enrollment::factory()->completed()->for($userA)->for($course)->create();

        $certificate = Certificate::factory()->create([
            'uuid'          => (string) \Illuminate\Support\Str::uuid(),
            'user_id'       => $userA->id,
            'course_id'     => $course->id,
            'enrollment_id' => $enrollment->id,
        ]);

        $response = $this->actingAs($userA)
            ->get(route('certificates.show', $certificate->uuid));

        $response->assertOk();
    });

    it('guest cannot access non-preview lesson', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->required()->for($course)->create([
            'is_free_preview' => false,
        ]);

        $response = $this->get(route('lessons.show', [$course->slug, $lesson->slug]));

        $response->assertForbidden();
    });

    it('guest can access free preview lessons', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->freePreview()->for($course)->create();

        $response = $this->get(route('lessons.show', [$course->slug, $lesson->slug]));

        $response->assertOk();
    });

    it('cannot enroll unauthenticated user', function () {
        $course = Course::factory()->published()->create();

        $response = $this->post(route('courses.enroll', $course->id));

        $response->assertRedirect(route('login'));
        expect(Enrollment::count())->toBe(0);
    });
});
