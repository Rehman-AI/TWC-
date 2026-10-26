<?php

namespace App\Filament\Resources;

use App\Enums\AuditAction;
use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->role === \App\Enums\TeamRole::Owner || auth()->user()?->role === \App\Enums\TeamRole::Manager;
    }

    protected static ?string $model = AuditLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Audit Log';

    public static function canCreate(): bool
    {
        return false; // Audit logs are system-generated only
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('M j, Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->formatStateUsing(fn (AuditAction $state) => $state->label())
                    ->color(fn (AuditAction $state) => $state->isSensitive() ? 'danger' : 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options(
                        collect(AuditAction::cases())
                            ->mapWithKeys(fn ($a) => [$a->value => $a->label()])
                            ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
