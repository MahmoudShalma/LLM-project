<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3, false);
        $title = rtrim($title, '.');

        return [
            'title'       => $title,
            'slug'        => Str::slug($title),
            'description' => fake()->paragraphs(3, true),
            'image_path'  => null,
            'level'       => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'status'      => 'draft',
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 'beginner',
        ]);
    }
}
