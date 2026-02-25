<?php

namespace App\Contracts\Repositories;

use App\Models\Certificate;

interface CertificateRepositoryInterface
{
    public function findByEnrollmentId(int $enrollmentId): ?Certificate;

    public function findByUuid(string $uuid): ?Certificate;

    public function create(array $data): Certificate;

    public function markEmailSent(Certificate $certificate): Certificate;
}
