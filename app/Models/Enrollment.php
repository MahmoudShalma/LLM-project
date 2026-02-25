<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    /** @use HasFactory<\Database\Factories\EnrollmentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'enrolled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at'  => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    // Scopes
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    // Helpers
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function getProgressPercentage(): int
    {
        $totalRequired = $this->course
            ->lessons()
            ->required()
            ->count();

        if ($totalRequired === 0) {
            return 0;
        }

        $completed = $this->lessonProgress()
            ->whereNotNull('completed_at')
            ->whereHas('lesson', fn ($q) => $q->required())
            ->count();

        return (int) round(($completed / $totalRequired) * 100);
    }
}
