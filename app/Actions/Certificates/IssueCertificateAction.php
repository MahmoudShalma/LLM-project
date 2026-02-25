<?php

namespace App\Actions\Certificates;

use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IssueCertificateAction
{
    public function __construct(
        private CertificateRepositoryInterface $certificates
    ) {}

    public function handle(Enrollment $enrollment): Certificate
    {
        return DB::transaction(function () use ($enrollment) {
            $existing = $this->certificates->findByEnrollmentId($enrollment->id);

            if ($existing) {
                return $existing;
            }

            return $this->certificates->create([
                'uuid'          => (string) Str::uuid(),
                'user_id'       => $enrollment->user_id,
                'course_id'     => $enrollment->course_id,
                'enrollment_id' => $enrollment->id,
                'issued_at'     => now(),
            ]);
        });
    }
}
