<?php

namespace App\Filament\Widgets;

use App\Models\Enrollment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestEnrollmentsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Latest Enrollments';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Enrollment::query()
                    ->with(['user', 'course'])
                    ->latest('enrolled_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable(),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->limit(30),

                Tables\Columns\TextColumn::make('enrolled_at')
                    ->label('Enrolled')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('completed_at')
                    ->label('Completed')
                    ->boolean()
                    ->getStateUsing(fn (Enrollment $record) => ! is_null($record->completed_at)),
            ])
            ->paginated(false);
    }
}
