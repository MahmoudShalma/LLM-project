<?php

namespace App\Providers;

use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Contracts\Repositories\LessonRepositoryInterface;
use App\Contracts\Repositories\ProgressRepositoryInterface;
use App\Contracts\Strategies\CompletionStrategyInterface;
use App\Repositories\CertificateRepository;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\LessonRepository;
use App\Repositories\ProgressRepository;
use App\Strategies\AllRequiredLessonsCompletionStrategy;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CourseRepositoryInterface::class, CourseRepository::class);
        $this->app->bind(EnrollmentRepositoryInterface::class, EnrollmentRepository::class);
        $this->app->bind(LessonRepositoryInterface::class, LessonRepository::class);
        $this->app->bind(ProgressRepositoryInterface::class, ProgressRepository::class);
        $this->app->bind(CertificateRepositoryInterface::class, CertificateRepository::class);
        $this->app->bind(CompletionStrategyInterface::class, AllRequiredLessonsCompletionStrategy::class);
    }
}
