<?php

namespace App\Contracts\Strategies;

use App\Models\Enrollment;

interface CompletionStrategyInterface
{
    public function isComplete(Enrollment $enrollment): bool;
}
