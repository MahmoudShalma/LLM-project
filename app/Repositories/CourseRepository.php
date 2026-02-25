<?php

namespace App\Repositories;

use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

class CourseRepository implements CourseRepositoryInterface
{
    public function getAllPublished(): Collection
    {
        return Course::published()
            ->with(['lessons' => fn ($q) => $q->ordered()->select(['id', 'course_id', 'title', 'slug', 'is_free_preview', 'is_required', 'order_column'])])
            ->withCount(['lessons', 'enrollments'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function findBySlug(string $slug): ?Course
    {
        return Course::where('slug', $slug)->first();
    }

    public function findBySlugWithTrashed(string $slug): ?Course
    {
        return Course::withTrashed()->where('slug', $slug)->first();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        return Course::withTrashed()
            ->where('slug', $slug)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}
