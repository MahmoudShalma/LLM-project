<?php

use App\Actions\Certificates\IssueCertificateAction;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

describe('Certificate Issuance', function () {

    it('issues a certificate for a completed enrollment', function () {
        $enrollment = Enrollment::factory()->completed()->create();

        $cert = app(IssueCertificateAction::class)->handle($enrollment);

        expect($cert)->toBeInstanceOf(Certificate::class)
            ->and($cert->enrollment_id)->toBe($enrollment->id)
            ->and($cert->user_id)->toBe($enrollment->user_id)
            ->and($cert->uuid)->not->toBeEmpty();
    });

    it('issues only one certificate per enrollment (idempotency on queue retry)', function () {
        $enrollment = Enrollment::factory()->completed()->create();

        $action = app(IssueCertificateAction::class);
        $c1     = $action->handle($enrollment);
        $c2     = $action->handle($enrollment); // Simulates queue retry
        $c3     = $action->handle($enrollment); // Another retry

        expect($c1->id)->toBe($c2->id)
            ->and($c2->id)->toBe($c3->id)
            ->and(Certificate::where('enrollment_id', $enrollment->id)->count())->toBe(1);
    });

    it('generates a valid UUID v4 for the certificate', function () {
        $enrollment = Enrollment::factory()->completed()->create();
        $cert       = app(IssueCertificateAction::class)->handle($enrollment);

        $uuidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        expect($cert->uuid)->toMatch($uuidPattern);
    });

    it('each enrollment gets a unique UUID', function () {
        $action = app(IssueCertificateAction::class);

        $e1 = Enrollment::factory()->completed()->create();
        $e2 = Enrollment::factory()->completed()->create();

        $c1 = $action->handle($e1);
        $c2 = $action->handle($e2);

        expect($c1->uuid)->not->toBe($c2->uuid);
    });
});
