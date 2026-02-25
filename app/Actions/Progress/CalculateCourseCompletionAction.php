<?php

namespace App\Actions\Progress;

use App\Contracts\Strategies\CompletionStrategyInterface;
use App\Models\Enrollment;

class CalculateCourseCompletionAction
{
    public function __construct(
        private CompletionStrategyInterface $strategy
    ) {}

    public function handle(Enrollment $enrollment): bool
    {
        return $this->strategy->isComplete($enrollment);
    }
}
