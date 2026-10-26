<?php

namespace App\Filament\Resources\VideoProjectResource\RelationManagers;

use App\Enums\SceneStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ScenesRelationManager extends RelationManager
{
    protected static string $relationship = 'scenes';
    protected static ?string $title = 'Scenes';
    protected static ?string $icon = 'heroicon-o-film';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('order')
                    ->label('Scene #')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                Forms\Components\TextInput::make('duration_seconds')
                    ->label('Duration (seconds)')
                    ->numeric()
                    ->required()
                    ->step(0.5)
                    ->minValue(0.5)
                    ->maxValue(60),
            ]),
            Forms\Components\Textarea::make('narration_text')
                ->label('Narration')
                ->rows(3)
                ->required()
                ->columnSpanFull(),
            Forms\Components\Textarea::make('visual_prompt')
                ->label('Visual Prompt')
                ->rows(3)
                ->required()
                ->columnSpanFull()
                ->helperText('Describe the visual for stock footage search or Google Flow prompt'),
            Forms\Components\Textarea::make('on_screen_text')
                ->label('On-Screen Text')
                ->rows(2)
                ->columnSpanFull(),
            Forms\Components\Textarea::make('flow_prompt')
                ->label('Google Flow Prompt')
                ->rows(3)
                ->columnSpanFull()
                ->helperText('Generated prompt for Google Flow clip generation'),
            Forms\Components\Select::make('status')
                ->options(SceneStatus::class)
                ->default('pending'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->reorderable('order')
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->width('60px'),
                Tables\Columns\TextColumn::make('narration_text')
                    ->label('Narration')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->narration_text)
                    ->searchable(),
                Tables\Columns\TextColumn::make('visual_prompt')
                    ->label('Visual')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('on_screen_text')
                    ->label('On-Screen')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->suffix('s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(SceneStatus::class),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('autoBreakdown')
                    ->label('Auto-Breakdown from Script')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalDescription('Break the approved script into individual scenes using AI.')
                    ->action(function () {
                        \Filament\Notifications\Notification::make()
                            ->title('Scene breakdown queued')
                            ->body('Scenes will be generated from the approved script.')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}