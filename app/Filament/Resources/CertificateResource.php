<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateResource\Pages;
use App\Models\Certificate;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Users & Progress';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Certificate Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('uuid')
                            ->label('UUID')
                            ->fontFamily('mono')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Student'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('course.title')
                            ->label('Course'),
                        Infolists\Components\TextEntry::make('issued_at')
                            ->label('Issued At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('completion_email_sent_at')
                            ->label('Email Sent At')
                            ->dateTime()
                            ->placeholder('Not sent yet'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->fontFamily('mono')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Issued')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('completion_email_sent_at')
                    ->label('Email Sent')
                    ->boolean()
                    ->getStateUsing(fn (Certificate $record) => ! is_null($record->completion_email_sent_at)),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('issued_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertificates::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
