<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DailyReport extends TableWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Today\'s Performance Report';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, [\App\Enums\TeamRole::Owner, \App\Enums\TeamRole::Manager]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('role', '!=', 'owner')
                    ->withCount([
                        'assignedTasks as total_tasks_count',
                        'assignedTasks as today_done_count' => fn ($q) => $q->where('status', 'done')->whereDate('completed_at', today()),
                        'assignedTasks as pending_count' => fn ($q) => $q->whereIn('status', ['pending', 'in_progress']),
                        'assignedTasks as overdue_count' => fn ($q) => $q->where('status', '!=', 'done')->where('deadline', '<', now()),
                        'assignedTasks as done_count' => fn ($q) => $q->where('status', 'done'),
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? $state),
                Tables\Columns\TextColumn::make('today_done_count')
                    ->label('Done Today')
                    ->alignCenter()
                    ->color('success')
                    ->weight('bold')
                    ->size('lg'),
                Tables\Columns\TextColumn::make('pending_count')
                    ->label('Pending')
                    ->alignCenter()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),
                Tables\Columns\TextColumn::make('overdue_count')
                    ->label('Overdue')
                    ->alignCenter()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->weight(fn ($state) => $state > 0 ? 'bold' : 'normal'),
                Tables\Columns\TextColumn::make('done_count')
                    ->label('Total Done')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_tasks_count')
                    ->label('Total Assigned')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        if ($record->total_tasks_count === 0) return '---';
                        $pct = round(($record->done_count / $record->total_tasks_count) * 100);
                        return $pct . '%';
                    })
                    ->alignCenter()
                    ->color(function ($record) {
                        if ($record->total_tasks_count === 0) return 'gray';
                        $pct = ($record->done_count / $record->total_tasks_count) * 100;
                        if ($pct >= 80) return 'success';
                        if ($pct >= 50) return 'warning';
                        return 'danger';
                    })
                    ->weight('bold'),
            ])
            ->paginated(false);
    }
}