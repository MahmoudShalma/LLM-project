<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonResource\Pages;
use App\Models\Course;
use App\Models\Lesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;
    protected static ?string $navigationIcon = 'heroicon-o-play-circle';
    protected static ?string $navigationGroup = 'Learning';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Lesson Details')
                ->schema([
                    Forms\Components\Select::make('course_id')
                        ->label('Course')
                        ->relationship('course', 'title')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                            $operation === 'create' ? $set('slug', Str::slug($state)) : null
                        ),

                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('video_url')
                        ->url()
                        ->label('Video URL')
                        ->maxLength(500)
                        ->placeholder('https://youtube.com/watch?v=...')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('content')
                        ->rows(5)
                        ->label('Lesson Content')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('order_column')
                        ->numeric()
                        ->label('Order')
                        ->default(1)
                        ->required(),

                    Forms\Components\Toggle::make('is_free_preview')
                        ->label('Free Preview')
                        ->helperText('Allow guests to access this lesson.'),

                    Forms\Components\Toggle::make('is_required')
                        ->label('Required for Completion')
                        ->default(true)
                        ->helperText('Required lessons must be completed for course certificate.'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_column')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\IconColumn::make('is_free_preview')
                    ->label('Free Preview')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course_id')
                    ->label('Course')
                    ->options(Course::pluck('title', 'id')),
                Tables\Filters\TernaryFilter::make('is_required'),
                Tables\Filters\TernaryFilter::make('is_free_preview'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->defaultSort('course_id')
            ->defaultSort('order_column');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit'   => Pages\EditLesson::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }
}
