<?php

namespace App\Actions\Courses;

use App\Contracts\Repositories\CourseRepositoryInterface;
use Illuminate\Support\Str;

class GenerateCourseSlugAction
{
    public function __construct(
        private CourseRepositoryInterface $courses
    ) {}

    public function handle(string $title, ?int $excludeId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i    = 1;

        while ($this->courses->slugExists($slug, $excludeId)) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
