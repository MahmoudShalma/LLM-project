<?php

use App\Actions\Courses\GenerateCourseSlugAction;
use App\Models\Course;

describe('Course Slug Uniqueness', function () {

    it('generates a simple slug from title', function () {
        $slug = app(GenerateCourseSlugAction::class)->handle('Introduction to PHP');
        expect($slug)->toBe('introduction-to-php');
    });

    it('appends -2 when slug already exists', function () {
        Course::factory()->create(['slug' => 'intro-to-php']);

        $slug = app(GenerateCourseSlugAction::class)->handle('Intro to PHP');
        expect($slug)->toBe('intro-to-php-2');
    });

    it('increments suffix until unique', function () {
        Course::factory()->create(['slug' => 'docker-basics']);
        Course::factory()->create(['slug' => 'docker-basics-2']);
        Course::factory()->create(['slug' => 'docker-basics-3']);

        $slug = app(GenerateCourseSlugAction::class)->handle('Docker Basics');
        expect($slug)->toBe('docker-basics-4');
    });

    it('avoids soft-deleted course slugs', function () {
        $course = Course::factory()->create(['slug' => 'laravel-advanced']);
        $course->delete(); // Soft delete

        $slug = app(GenerateCourseSlugAction::class)->handle('Laravel Advanced');
        expect($slug)->toBe('laravel-advanced-2');
    });

    it('allows same slug when excluding the current record (edit)', function () {
        $course = Course::factory()->create(['slug' => 'my-course']);

        // Editing same course — should not conflict with its own slug
        $slug = app(GenerateCourseSlugAction::class)->handle('My Course', $course->id);
        expect($slug)->toBe('my-course');
    });

    it('handles multiple soft-deleted slugs correctly', function () {
        $c1 = Course::factory()->create(['slug' => 'php-course']);
        $c2 = Course::factory()->create(['slug' => 'php-course-2']);
        $c1->delete();
        $c2->delete();

        $slug = app(GenerateCourseSlugAction::class)->handle('PHP Course');
        expect($slug)->toBe('php-course-3');
    });
});
