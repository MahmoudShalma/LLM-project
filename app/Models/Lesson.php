<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    /** @use HasFactory<\Database\Factories\LessonFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'video_url',
        'content',
        'order_column',
        'is_free_preview',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'is_free_preview' => 'boolean',
            'is_required'     => 'boolean',
            'order_column'    => 'integer',
        ];
    }

    // Relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    // Scopes
    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    public function scopeFreePreview(Builder $query): Builder
    {
        return $query->where('is_free_preview', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_column');
    }

    // Route model binding by slug (Livewire uses typed public properties for binding)
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Helpers
    public function isAccessibleByGuest(): bool
    {
        return $this->is_free_preview;
    }
}
