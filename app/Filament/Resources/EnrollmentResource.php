<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Users & Progress';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Enrollment Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Student'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('course.title')
                            ->label('Course'),
                        Infolists\Components\TextEntry::make('enrolled_at')
                            ->label('Enrolled At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Completed At')
                            ->dateTime()
                            ->placeholder('Not completed yet'),
                        Infolists\Components\TextEntry::make('certificate.uuid')
                            ->label('Certificate')
                            ->placeholder('No certificate issued')
                            ->copyable(),
                    ])->columns(2),

                Infolists\Components\Section::make('Progress')
                    ->schema([
                        Infolists\Components\TextEntry::make('progress')
                            ->label('Completion')
                            ->getStateUsing(fn (Enrollment $record) => $record->getProgressPercentage() . '%'),
                        Infolists\Components\TextEntry::make('lessons_completed')
                            ->label('Lessons Completed')
                            ->getStateUsing(fn (Enrollment $record) =>
                                $record->lessonProgress()->completed()->count()
                                . ' / '
                                . $record->course->lessons()->required()->count()
                            ),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('enrolled_at')
                    ->label('Enrolled')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('completed_at')
                    ->label('Completed')
                    ->boolean()
                    ->getStateUsing(fn (Enrollment $record) => ! is_null($record->completed_at)),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('completed')
                    ->label('Completed')
                    ->nullable()
                    ->attribute('completed_at'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('enrolled_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnrollments::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
