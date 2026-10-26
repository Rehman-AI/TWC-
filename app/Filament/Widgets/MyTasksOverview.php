<?php

namespace App\Filament\Widgets;

use App\Enums\TaskStatus;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyTasksOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        $role = auth()->user()?->role;
        return $role && !$role->isOwnerOrManager();
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $myTasks   = Task::forUser($user)->count();
        $pending   = Task::forUser($user)->pending()->count();
        $inProg    = Task::forUser($user)->inProgress()->count();
        $done      = Task::forUser($user)->done()->count();
        $overdue   = Task::forUser($user)->overdue()->count();

        return [
            Stat::make('My Tasks', $myTasks)
                ->icon('heroicon-o-clipboard-document-list')
                ->color('primary'),
            Stat::make('Pending', $pending)
                ->icon('heroicon-o-clock')
                ->color('gray'),
            Stat::make('In Progress', $inProg)
                ->icon('heroicon-o-arrow-path')
                ->color('warning'),
            Stat::make('Completed', $done)
                ->description($myTasks > 0 ? round(($done / $myTasks) * 100) . '% done' : '')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Overdue', $overdue)
                ->description($overdue > 0 ? 'Complete ASAP!' : 'All on track')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($overdue > 0 ? 'danger' : 'success'),
        ];
    }
}