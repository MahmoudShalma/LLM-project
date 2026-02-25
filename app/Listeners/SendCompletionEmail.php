<?php

namespace App\Listeners;

use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Events\CourseCompleted;
use App\Mail\CourseCompletionMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendCompletionEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'emails';
    public int $tries    = 3;

    public function __construct(
        private CertificateRepositoryInterface $certificates,
    ) {}

    public function handle(CourseCompleted $event): void
    {
        $certificate = $this->certificates->findByEnrollmentId($event->enrollment->id);

        if (! $certificate) {
            $this->release(30);
            return;
        }

        if ($certificate->hasEmailBeenSent()) {
            return;
        }

        $updated = $this->certificates->markEmailSent($certificate);

        if (! $updated->hasEmailBeenSent()) {
            return;
        }

        Mail::to($event->user->email)->send(
            new CourseCompletionMail($event->enrollment, $updated)
        );
    }
}
