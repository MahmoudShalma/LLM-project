<?php

namespace App\Listeners;

use App\Actions\Certificates\IssueCertificateAction;
use App\Events\CourseCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class IssueCertificateOnCompletion implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';
    public int $tries    = 3;

    public function __construct(
        private IssueCertificateAction $issueCertificate,
    ) {}

    public function handle(CourseCompleted $event): void
    {
        $this->issueCertificate->handle($event->enrollment);
    }
}
