<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'title'       => 'Laravel for Beginners',
                'slug'        => 'laravel-for-beginners',
                'description' => 'Master the fundamentals of Laravel framework. Learn routing, Eloquent ORM, Blade templates, and more.',
                'level'       => 'beginner',
                'status'      => 'published',
                'lessons'     => [
                    ['title' => 'Introduction to Laravel', 'is_free_preview' => true, 'is_required' => true, 'order_column' => 1],
                    ['title' => 'Routing Basics', 'is_free_preview' => false, 'is_required' => true, 'order_column' => 2],
                    ['title' => 'Blade Templates', 'is_free_preview' => false, 'is_required' => true, 'order_column' => 3],
                    ['title' => 'Eloquent ORM', 'is_free_preview' => false, 'is_required' => true, 'order_column' => 4],
                    ['title' => 'Bonus: Tips & Tricks', 'is_free_preview' => false, 'is_required' => false, 'order_column' => 5],
                ],
            ],
            [
                'title'       => 'Advanced PHP Patterns',
                'slug'        => 'advanced-php-patterns',
                'description' => 'Deep dive into design patterns in PHP. SOLID principles, Repository, Factory, Strategy and more.',
                'level'       => 'advanced',
                'status'      => 'published',
                'lessons'     => [
                    ['title' => 'SOLID Principles Overview', 'is_free_preview' => true, 'is_required' => true, 'order_column' => 1],
                    ['title' => 'Repository Pattern', 'is_free_preview' => false, 'is_required' => true, 'order_column' => 2],
                    ['title' => 'Strategy Pattern', 'is_free_preview' => false, 'is_required' => true, 'order_column' => 3],
                    ['title' => 'Observer Pattern', 'is_free_preview' => false, 'is_required' => true, 'order_column' => 4],
                ],
            ],
            [
                'title'       => 'Docker for Developers',
                'slug'        => 'docker-for-developers',
                'description' => 'Learn Docker from scratch. Containers, images, Docker Compose, and production best practices.',
                'level'       => 'intermediate',
                'status'      => 'published',
                'lessons'     => [
                    ['title' => 'What is Docker?', 'is_free_preview' => true, 'is_required' => true, 'order_column' => 1],
                    ['title' => 'Docker Images & Containers', 'is_free_preview' => false, 'is_required' => true, 'order_column' => 2],
                    ['title' => 'Docker Compose', 'is_free_preview' => false, 'is_required' => true, 'order_column' => 3],
                    ['title' => 'Production Deployment', 'is_free_preview' => false, 'is_required' => true, 'order_column' => 4],
                ],
            ],
            [
                'title'       => 'JavaScript Mastery',
                'slug'        => 'javascript-mastery',
                'description' => 'Coming soon! A comprehensive JavaScript course.',
                'level'       => 'intermediate',
                'status'      => 'draft',
                'lessons'     => [],
            ],
        ];

        foreach ($courses as $courseData) {
            $lessonsData = $courseData['lessons'];
            unset($courseData['lessons']);

            $course = Course::firstOrCreate(
                ['slug' => $courseData['slug']],
                $courseData
            );

            foreach ($lessonsData as $lessonData) {
                $slug = Str::slug($lessonData['title']);
                Lesson::firstOrCreate(
                    ['course_id' => $course->id, 'slug' => $slug],
                    array_merge($lessonData, [
                        'course_id'  => $course->id,
                        'slug'       => $slug,
                        'video_url'  => 'https://www.youtube.com/watch?v=Yf7jG3B3Z1g',
                        'content'    => 'This lesson covers ' . $lessonData['title'] . '. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                    ])
                );
            }
        }
    }
}
