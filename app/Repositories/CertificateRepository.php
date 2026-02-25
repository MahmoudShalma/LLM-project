<?php

namespace App\Repositories;

use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Models\Certificate;

class CertificateRepository implements CertificateRepositoryInterface
{
    public function findByEnrollmentId(int $enrollmentId): ?Certificate
    {
        return Certificate::where('enrollment_id', $enrollmentId)->first();
    }

    public function findByUuid(string $uuid): ?Certificate
    {
        return Certificate::where('uuid', $uuid)->first();
    }

    public function create(array $data): Certificate
    {
        return Certificate::create($data);
    }

    public function markEmailSent(Certificate $certificate): Certificate
    {
        Certificate::where('id', $certificate->id)
            ->whereNull('completion_email_sent_at')
            ->update(['completion_email_sent_at' => now()]);

        return $certificate->fresh();
    }
}
