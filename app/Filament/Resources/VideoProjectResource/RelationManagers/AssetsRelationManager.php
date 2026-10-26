<?php

namespace App\Filament\Resources\VideoProjectResource\RelationManagers;

use App\Enums\AssetType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'assets';
    protected static ?string $title = 'Assets';
    protected static ?string $icon = 'heroicon-o-document';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->options(AssetType::class)
                ->required(),
            Forms\Components\TextInput::make('filename')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('scene_id')
                ->relationship('scene', 'order')
                ->label('Scene')
                ->placeholder('Project-level asset'),
            Forms\Components\TextInput::make('drive_url')
                ->label('Drive URL')
                ->url(),
            Forms\Components\TextInput::make('drive_file_id')
                ->label('Drive File ID'),
            Forms\Components\TextInput::make('mime_type')
                ->maxLength(100),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('filename')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('scene.order')
                    ->label('Scene')
                    ->prefix('#')
                    ->placeholder('Project'),
                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Type')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('size_bytes')
                    ->label('Size')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1048576, 1) . ' MB' : '—')
                    ->sortable(),
                Tables\Columns\IconColumn::make('drive_file_id')
                    ->label('On Drive')
                    ->boolean()
                    ->trueIcon('heroicon-o-cloud')
                    ->falseIcon('heroicon-o-cloud-arrow-up'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(AssetType::class),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Upload Asset'),
                Tables\Actions\Action::make('detectUploads')
                    ->label('Detect Drive Uploads')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalDescription('Scan the project Drive folder for new uploads (Flow clips, CapCut exports, etc.)')
                    ->action(function () {
                        // This would scan Google Drive for new files in the project folder
                        // For now, just show a notification
                        \Filament\Notifications\Notification::make()
                            ->title('Drive scan queued')
                            ->body('New uploads will be detected and catalogued.')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('openDrive')
                    ->label('Open in Drive')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn ($record) => $record->drive_url)
                    ->visible(fn ($record) => ! empty($record->drive_url))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }
}