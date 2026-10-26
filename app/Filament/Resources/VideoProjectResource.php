<?php

namespace App\Filament\Resources;

use App\Enums\PipelineStatus;
use App\Enums\VideoPriority;
use App\Filament\Resources\VideoProjectResource\Pages;
use App\Filament\Resources\VideoProjectResource\RelationManagers;
use App\Models\VideoProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VideoProjectResource extends Resource
{
    protected static ?string $model = VideoProject::class;
    protected static ?string $navigationIcon = 'heroicon-o-film';
    protected static ?string $navigationGroup = 'Production';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'title';
    public static function canAccess(): bool
    {
        return auth()->user()?->role === \App\Enums\TeamRole::Owner || auth()->user()?->role === \App\Enums\TeamRole::Manager;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Video Details')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('channel_id')
                            ->relationship('channel', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('slug')->required(),
                                Forms\Components\Hidden::make('team_id')
                                    ->default(fn () => auth()->user()?->team_id),
                            ]),
                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignee', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Assigned To'),
                    ]),
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('language')
                            ->options([
                                'en' => 'English',
                                'ur' => 'Urdu',
                                'ar' => 'Arabic',
                                'es' => 'Spanish',
                                'ja' => 'Japanese',
                            ])
                            ->default('en'),
                        Forms\Components\Select::make('target_duration')
                            ->label('Target Duration')
                            ->options([
                                15 => '15 seconds',
                                30 => '30 seconds',
                                45 => '45 seconds',
                                60 => '60 seconds',
                            ])
                            ->default(60),
                        Forms\Components\Select::make('priority')
                            ->options(VideoPriority::class)
                            ->default('normal'),
                    ]),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date'),
                        Forms\Components\Select::make('status')
                            ->options(PipelineStatus::class)
                            ->default('idea')
                            ->disabled(fn (?VideoProject $record) => $record === null),
                    ]),
                    Forms\Components\Textarea::make('notes')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('Topic idea, reference links, or instructions for the team'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->title),
                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Channel')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Assigned To')
                    ->sortable()
                    ->placeholder('Unassigned'),
                Tables\Columns\TextColumn::make('target_duration')
                    ->label('Duration')
                    ->suffix('s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('language')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => strtoupper($state))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record?->isOverdue() ? 'danger' : null),
                Tables\Columns\TextColumn::make('scenes_count')
                    ->counts('scenes')
                    ->label('Scenes')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(PipelineStatus::class)
                    ->multiple(),
                Tables\Filters\SelectFilter::make('priority')
                    ->options(VideoPriority::class),
                Tables\Filters\SelectFilter::make('channel_id')
                    ->relationship('channel', 'name')
                    ->label('Channel')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->relationship('assignee', 'name')
                    ->label('Assigned To')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Tabs::make('Video Project')
                ->columnSpanFull()
                ->tabs([
                    Infolists\Components\Tabs\Tab::make('Overview')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Infolists\Components\Grid::make(3)->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('priority')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('target_duration')
                                    ->label('Duration')
                                    ->suffix(' seconds'),
                            ]),
                            Infolists\Components\Grid::make(3)->schema([
                                Infolists\Components\TextEntry::make('channel.name')
                                    ->label('Channel')
                                    ->badge()
                                    ->color('gray'),
                                Infolists\Components\TextEntry::make('assignee.name')
                                    ->label('Assigned To')
                                    ->placeholder('Unassigned'),
                                Infolists\Components\TextEntry::make('due_date')
                                    ->date()
                                    ->placeholder('No due date'),
                            ]),
                            Infolists\Components\TextEntry::make('notes')
                                ->columnSpanFull()
                                ->placeholder('No notes'),
                        ]),
                    Infolists\Components\Tabs\Tab::make('Script')
                        ->icon('heroicon-o-pencil-square')
                        ->schema([
                            Infolists\Components\RepeatableEntry::make('scriptVersions')
                                ->schema([
                                    Infolists\Components\Grid::make(4)->schema([
                                        Infolists\Components\TextEntry::make('version')
                                            ->prefix('v'),
                                        Infolists\Components\TextEntry::make('status')
                                            ->badge(),
                                        Infolists\Components\TextEntry::make('word_count')
                                            ->suffix(' words'),
                                        Infolists\Components\TextEntry::make('estimated_duration')
                                            ->suffix(' sec'),
                                    ]),
                                    Infolists\Components\TextEntry::make('plain_text')
                                        ->label('Script Text')
                                        ->columnSpanFull()
                                        ->markdown(),
                                ]),
                        ]),
                    Infolists\Components\Tabs\Tab::make('Timeline')
                        ->icon('heroicon-o-clock')
                        ->schema([
                            Infolists\Components\TextEntry::make('created_at')
                                ->label('Created')
                                ->dateTime(),
                            Infolists\Components\TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->dateTime(),
                        ]),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ScenesRelationManager::class,
            RelationManagers\ScriptVersionsRelationManager::class,
            RelationManagers\AssetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVideoProjects::route('/'),
            'create' => Pages\CreateVideoProject::route('/create'),
            'view'   => Pages\ViewVideoProject::route('/{record}'),
            'edit'   => Pages\EditVideoProject::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::whereNotIn('status', ['published', 'failed', 'blocked'])->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
}