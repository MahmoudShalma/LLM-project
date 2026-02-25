<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        $title = fake()->sentence(4, false);
        $title = rtrim($title, '.');

        return [
            'course_id'      => Course::factory(),
            'title'          => $title,
            'slug'           => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 99999),
            'video_url'      => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'content'        => fake()->paragraphs(2, true),
            'order_column'   => fake()->numberBetween(1, 100),
            'is_free_preview' => false,
            'is_required'    => true,
        ];
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => false,
        ]);
    }

    public function freePreview(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_free_preview' => true,
            'is_required'     => false,
        ]);
    }

    public function forCourse(Course $course): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => $course->id,
        ]);
    }
}
