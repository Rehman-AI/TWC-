<?php

namespace App\Filament\Resources\VideoProjectResource\RelationManagers;

use App\Enums\ScriptStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ScriptVersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'scriptVersions';
    protected static ?string $title = 'Script Versions';
    protected static ?string $icon = 'heroicon-o-pencil-square';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Script Structure')
                ->schema([
                    Forms\Components\Textarea::make('content.hook')
                        ->label('Hook (First 2 seconds)')
                        ->rows(2)
                        ->required()
                        ->helperText('The attention-grabbing opening'),
                    Forms\Components\Textarea::make('content.body')
                        ->label('Body')
                        ->rows(5)
                        ->required(),
                    Forms\Components\Textarea::make('content.cta')
                        ->label('Call to Action')
                        ->rows(2),
                    Forms\Components\Textarea::make('content.loop_ending')
                        ->label('Loop Ending')
                        ->rows(2)
                        ->helperText('Creates a seamless loop back to the hook'),
                ]),
            Forms\Components\Textarea::make('plain_text')
                ->label('Full Narration Text')
                ->rows(6)
                ->columnSpanFull()
                ->helperText('Complete narration text for TTS and word count'),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('word_count')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\TextInput::make('estimated_duration')
                    ->label('Est. Duration (sec)')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),
            ]),
            Forms\Components\Textarea::make('reviewer_notes')
                ->label('Review Notes')
                ->rows(2)
                ->columnSpanFull()
                ->visible(fn ($record) => $record?->reviewed_at !== null),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('version', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('version')
                    ->prefix('v')
                    ->sortable()
                    ->width('80px'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('word_count')
                    ->label('Words')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estimated_duration')
                    ->label('Est. Duration')
                    ->suffix('s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Reviewed By')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ScriptStatus::class),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New Version'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === ScriptStatus::Review
                        && auth()->user()?->isTeamManager())
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Approval Notes')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $record->approve(auth()->user(), $data['notes'] ?? null);
                        \Filament\Notifications\Notification::make()
                            ->title('Script v' . $record->version . ' approved')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === ScriptStatus::Review
                        && auth()->user()?->isTeamManager())
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Rejection Reason')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->reject(auth()->user(), $data['notes']);
                        \Filament\Notifications\Notification::make()
                            ->title('Script v' . $record->version . ' rejected')
                            ->danger()
                            ->send();
                    }),
                Tables\Actions\Action::make('submitForReview')
                    ->label('Submit for Review')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === ScriptStatus::Draft)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => ScriptStatus::Review]);
                        $record->videoProject->update(['status' => 'script_review']);
                        \Filament\Notifications\Notification::make()
                            ->title('Script submitted for review')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }
}