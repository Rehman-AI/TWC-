<?php

namespace App\Filament\Resources\ChannelResource\RelationManagers;

use App\Enums\AccountStatus;
use App\Enums\PlatformEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PlatformAccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'platformAccounts';
    protected static ?string $title = 'Platform Accounts';
    protected static ?string $icon = 'heroicon-o-link';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('platform')
                ->options(PlatformEnum::class)
                ->required(),
            Forms\Components\TextInput::make('account_name')
                ->label('Account Name')
                ->maxLength(255),
            Forms\Components\TextInput::make('platform_account_id')
                ->label('Platform Account ID')
                ->maxLength(255),
            Forms\Components\Select::make('status')
                ->options(AccountStatus::class)
                ->default('active'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('platform')
                    ->badge(),
                Tables\Columns\TextColumn::make('account_name')
                    ->label('Account')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('token_expires_at')
                    ->label('Token Expires')
                    ->dateTime()
                    ->placeholder('Not connected'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Account'),
                Tables\Actions\Action::make('connectYouTube')
                    ->label('Connect YouTube')
                    ->icon('heroicon-o-play')
                    ->color('danger')
                    ->url(fn () => route('oauth.youtube.redirect', ['channel' => $this->getOwnerRecord()->id]))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('disconnect')
                    ->label('Disconnect')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->markRevoked();
                    }),
            ])
            ->bulkActions([]);
    }
}