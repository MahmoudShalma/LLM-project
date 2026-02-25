<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'course_id',
        'enrollment_id',
        'issued_at',
        'completion_email_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at'                 => 'datetime',
            'completion_email_sent_at'  => 'datetime',
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

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    // Helpers
    public function hasEmailBeenSent(): bool
    {
        return $this->completion_email_sent_at !== null;
    }

    // Route model binding by UUID
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
