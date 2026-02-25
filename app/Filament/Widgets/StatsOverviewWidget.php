<?php

namespace App\Filament\Widgets;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Students', User::where('is_admin', false)->count())
                ->description('Registered students')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Published Courses', Course::published()->count())
                ->description(Course::draft()->count() . ' drafts')
                ->icon('heroicon-o-book-open')
                ->color('success'),

            Stat::make('Enrollments', Enrollment::count())
                ->description(Enrollment::completed()->count() . ' completed')
                ->icon('heroicon-o-academic-cap')
                ->color('warning'),

            Stat::make('Certificates', Certificate::count())
                ->description('Issued to students')
                ->icon('heroicon-o-document-check')
                ->color('info'),
        ];
    }
}
