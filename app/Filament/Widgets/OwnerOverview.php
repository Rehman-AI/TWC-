<?php

namespace App\Filament\Widgets;

use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OwnerOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, [\App\Enums\TeamRole::Owner, \App\Enums\TeamRole::Manager]);
    }

    protected function getStats(): array
    {
        $totalTasks     = Task::count();
        $pendingTasks   = Task::pending()->count();
        $inProgressTasks = Task::inProgress()->count();
        $doneTasks      = Task::done()->count();
        $overdueTasks   = Task::overdue()->count();
        $todayDone      = Task::done()->whereDate('completed_at', today())->count();

        return [
            Stat::make('Tasks Done', $doneTasks . '/' . $totalTasks)
                ->description($totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) . '% complete' : 'No tasks yet')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->url(TaskResource::getUrl('index', ['tableFilters[status][value]' => 'done']))
                ->extraAttributes(['class' => 'cursor-pointer']),

            Stat::make('Pending', $pendingTasks)
                ->description('Click to see who')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(TaskResource::getUrl('index', ['tableFilters[status][value]' => 'pending']))
                ->extraAttributes(['class' => 'cursor-pointer']),

            Stat::make('In Progress', $inProgressTasks)
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->url(TaskResource::getUrl('index', ['tableFilters[status][value]' => 'in_progress']))
                ->extraAttributes(['class' => 'cursor-pointer']),

            Stat::make('Overdue', $overdueTasks)
                ->description($overdueTasks > 0 ? 'Needs attention!' : 'All on track')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($overdueTasks > 0 ? 'danger' : 'success')
                ->url(TaskResource::getUrl('index', ['tableFilters[overdue][isActive]' => 'true']))
                ->extraAttributes(['class' => 'cursor-pointer']),

            Stat::make('Done Today', $todayDone)
                ->description('Tasks completed today')
                ->icon('heroicon-o-star')
                ->color('info'),

            Stat::make('Team', User::where('role', '!=', 'owner')->count())
                ->icon('heroicon-o-user-group')
                ->color('primary'),
        ];
    }
}