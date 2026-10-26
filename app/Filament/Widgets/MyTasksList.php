<?php

namespace App\Filament\Widgets;

use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class MyTasksList extends TableWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'My Tasks';

    public static function canView(): bool
    {
        $role = auth()->user()?->role;
        return $role && !$role->isOwnerOrManager();
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query(
                Task::query()
                    ->where('assigned_to', $user?->id)
                    ->with(['project', 'channel'])
                    ->orderByRaw("CASE WHEN status = 'done' THEN 1 ELSE 0 END")
                    ->orderBy('deadline')
            )
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->weight('bold')
                    ->limit(35),
                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Channel')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('deadline')
                    ->label('Due')
                    ->dateTime('M j, g:i A')
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : null)
                    ->weight(fn ($record) => $record->isOverdue() ? 'bold' : null),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Done At')
                    ->dateTime('M j, g:i A')
                    ->placeholder('---'),
            ])
            ->actions([
                Tables\Actions\Action::make('start')
                    ->label('Start')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === TaskStatus::Pending)
                    ->action(fn ($record) => $record->markInProgress()),

                Tables\Actions\Action::make('done')
                    ->label('Done')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Complete')
                    ->modalDescription(fn ($record) => match ($record->type) {
                        TaskType::Script => 'Have you pasted the script in the edit page?',
                        TaskType::Video  => 'Have you uploaded the video and pasted the Drive link?',
                        default          => 'Have you posted the video and saved the URL?',
                    })
                    ->visible(fn ($record) => $record->status !== TaskStatus::Done)
                    ->action(fn ($record) => $record->markDone()),

                Tables\Actions\Action::make('edit')
                    ->label('Open')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false);
    }
}