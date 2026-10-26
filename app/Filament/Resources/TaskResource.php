<?php

namespace App\Filament\Resources;

use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Filament\Resources\TaskResource\Pages;
use App\Models\CustomRole;
use App\Models\Task;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Tasks';
    protected static ?int $navigationSort = 1;

    /** Role-based filtering: each person only sees their tasks */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->role === \App\Enums\TeamRole::Owner || ($user->customRole && $user->customRole->isManager()));

        if ($isAdmin) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->where('assigned_to', $user?->id);
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->role === \App\Enums\TeamRole::Owner || ($user->customRole && $user->customRole->isManager()));

        return $form->schema([
            Forms\Components\Section::make('Task Details')
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->relationship('project', 'title')
                        ->required()->searchable()->preload()
                        ->visible($isAdmin)
                        ->reactive()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state) {
                                $p = \App\Models\VideoProject::find($state);
                                $set('channel_id', $p?->channel_id);
                            }
                        }),
                    Forms\Components\Select::make('channel_id')
                        ->relationship('channel', 'name')
                        ->required()->visible($isAdmin),
                    Forms\Components\Select::make('type')
                        ->options(TaskType::class)
                        ->required()->visible($isAdmin),
                    Forms\Components\Select::make('assigned_to')
                        ->label('Assign To')
                        ->options(fn () => User::whereNotNull('custom_role_id')
                            ->with('customRole')
                            ->get()
                            ->mapWithKeys(fn ($u) => [$u->id => $u->name . ' (' . ($u->customRole?->name ?? '?') . ')'])
                        )
                        ->searchable()->visible($isAdmin),
                    Forms\Components\DateTimePicker::make('deadline')
                        ->default(today()->endOfDay()),
                    Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
                ]),

            // SCRIPT: scriptwriter pastes script here
            Forms\Components\Section::make('Script')
                ->schema([
                    Forms\Components\RichEditor::make('script_content')
                        ->label('Write or paste your script here')
                        ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'h2', 'h3'])
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('extra_notes')
                        ->label('Extra Notes / Directions for Video Maker')
                        ->rows(3)
                        ->placeholder('Any additional instructions, references, or ideas...')
                        ->columnSpanFull(),
                ])
                ->visible(fn (?Task $record) => $record?->type === TaskType::Script),

            // VIDEO: video maker pastes CapCut link
            Forms\Components\Section::make('Video')
                ->schema([
                    Forms\Components\TextInput::make('video_drive_url')
                        ->label('CapCut / Google Drive Video Link')
                        ->url()
                        ->placeholder('Paste the CapCut or Google Drive link here')
                        ->helperText('The video link will automatically be sent to all platform heads when you mark this task as done'),
                ])
                ->visible(fn (?Task $record) => $record?->type === TaskType::Video),

            // PLATFORM: platform head sees the link and marks posted
            Forms\Components\Section::make('Platform Posting')
                ->schema([
                    Forms\Components\Placeholder::make('video_link')
                        ->label('Video to Post')
                        ->content(fn (?Task $record) => $record?->video_drive_url
                            ? new \Illuminate\Support\HtmlString('<a href="' . e($record->video_drive_url) . '" target="_blank" class="text-primary-500 underline font-bold text-lg">' . e($record->video_drive_url) . '</a>')
                            : 'No video link yet'
                        ),
                    Forms\Components\TextInput::make('post_url')
                        ->label('Posted URL (paste after you post it)')
                        ->url()
                        ->placeholder('Paste the live post URL after publishing'),
                ])
                ->visible(fn (?Task $record) => $record?->type?->isPlatformPosting()),
        ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->role === \App\Enums\TeamRole::Owner || ($user->customRole && $user->customRole->isManager()));

        return $table
            ->defaultSort('deadline', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')->searchable()->weight('bold')->limit(30),
                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Channel'),
                Tables\Columns\TextColumn::make('type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Assigned To')->visible($isAdmin),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('deadline')
                    ->dateTime('M j, g:i A')->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : null)
                    ->weight(fn ($record) => $record->isOverdue() ? 'bold' : null)
                    ->description(fn ($record) => $record->isOverdue() ? 'OVERDUE' : null),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Done At')->dateTime('M j')->placeholder('---')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(TaskStatus::class),
                Tables\Filters\SelectFilter::make('type')->options(TaskType::class),
                Tables\Filters\SelectFilter::make('channel_id')
                    ->relationship('channel', 'name')->label('Channel')->visible($isAdmin),
                Tables\Filters\Filter::make('overdue')
                    ->query(fn ($q) => $q->overdue())->label('Overdue Only'),
            ])
            ->actions([
                // Start working
                Tables\Actions\Action::make('start')
                    ->label('Start')->icon('heroicon-o-play')->color('warning')
                    ->visible(fn ($record) => $record->status === TaskStatus::Pending)
                    ->action(fn ($record) => $record->markInProgress()),

                // SCRIPTWRITER: Assign to Video Maker
                Tables\Actions\Action::make('assignToVideoMaker')
                    ->label('Send to Video Maker')
                    ->icon('heroicon-o-video-camera')
                    ->color('primary')
                    ->visible(fn ($record) =>
                        $record->type === TaskType::Script
                        && $record->status === TaskStatus::Done
                        && ! Task::where('project_id', $record->project_id)->where('type', 'video')->exists()
                    )
                    ->form([
                        Forms\Components\Select::make('video_maker_id')
                            ->label('Choose Video Maker')
                            ->options(fn () => User::whereHas('customRole', fn ($q) =>
                                $q->where('is_active', true)->whereJsonContains('permissions', 'make_video')
                            )->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        Task::create([
                            'project_id'     => $record->project_id,
                            'channel_id'     => $record->channel_id,
                            'type'           => TaskType::Video->value,
                            'status'         => TaskStatus::Pending->value,
                            'assigned_to'    => $data['video_maker_id'],
                            'assigned_by'    => auth()->id(),
                            'deadline'       => today()->endOfDay(),
                            'script_content' => $record->script_content,
                            'notes'          => $record->notes,
                        ]);
                        Notification::make()->title('Script sent to Video Maker!')->success()->send();
                    }),

                // Mark Done
                Tables\Actions\Action::make('markDone')
                    ->label('Mark Done')->icon('heroicon-o-check-circle')->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Complete This Task')
                    ->modalDescription(fn ($record) => match ($record->type) {
                        TaskType::Script => 'Script is ready? This will let you assign it to a Video Maker.',
                        TaskType::Video  => 'Video link pasted? This will auto-send to all platform heads.',
                        default          => 'Have you posted the video on the platform?',
                    })
                    ->visible(fn ($record) => $record->status !== TaskStatus::Done)
                    ->action(fn ($record) => $record->markDone()),

                Tables\Actions\EditAction::make()->iconButton(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit'   => Pages\EditTask::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) return null;
        $isAdmin = $user->role === 'owner' || ($user->role && $user->role->value === 'owner');
        $count = $isAdmin
            ? Task::overdue()->count()
            : Task::forUser($user)->where('status', '!=', 'done')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $user = auth()->user();
        $isAdmin = $user && $user->role === \App\Enums\TeamRole::Owner;
        return $isAdmin ? (Task::overdue()->count() > 0 ? 'danger' : 'success') : 'primary';
    }
}