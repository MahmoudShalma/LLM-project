<?php

namespace App\Contracts\Repositories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

interface CourseRepositoryInterface
{
    public function getAllPublished(): Collection;

    public function findBySlug(string $slug): ?Course;

    public function findBySlugWithTrashed(string $slug): ?Course;

    public function slugExists(string $slug, ?int $excludeId = null): bool;
}
