<?php

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certificate>
 */
class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    public function definition(): array
    {
        $enrollment = Enrollment::factory()->completed()->create();

        return [
            'uuid'          => (string) Str::uuid(),
            'user_id'       => $enrollment->user_id,
            'course_id'     => $enrollment->course_id,
            'enrollment_id' => $enrollment->id,
            'issued_at'     => now(),
            'completion_email_sent_at' => null,
        ];
    }

    public function withEmailSent(): static
    {
        return $this->state(fn (array $attributes) => [
            'completion_email_sent_at' => now(),
        ]);
    }
}
