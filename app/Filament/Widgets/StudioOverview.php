<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use App\Models\Channel;
use App\Models\Team;
use App\Models\VideoProject;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudioOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -2;
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $user = auth()->user();
        $teamId = $user?->team_id;

        $channelIds = Channel::where('team_id', $teamId)->pluck('id');

        $totalProjects = VideoProject::whereIn('channel_id', $channelIds)->count();
        $activeProjects = VideoProject::whereIn('channel_id', $channelIds)
            ->whereNotIn('status', ['published', 'failed', 'blocked'])
            ->count();
        $publishedProjects = VideoProject::whereIn('channel_id', $channelIds)
            ->where('status', 'published')
            ->count();
        $inReview = VideoProject::whereIn('channel_id', $channelIds)
            ->where('status', 'script_review')
            ->count();
        $overdueCount = VideoProject::whereIn('channel_id', $channelIds)
            ->whereNotIn('status', ['published', 'failed', 'blocked'])
            ->where('due_date', '<', now())
            ->count();

        return [
            Stat::make('Active Videos', $activeProjects)
                ->description("{$totalProjects} total across all channels")
                ->descriptionIcon('heroicon-m-film')
                ->chart([2, 4, 3, 5, 4, $activeProjects])
                ->color('primary'),

            Stat::make('Channels', Channel::where('team_id', $teamId)->where('is_active', true)->count())
                ->description(Channel::where('team_id', $teamId)->count() . ' total')
                ->descriptionIcon('heroicon-m-tv')
                ->chart([1, 2, 2])
                ->color('info'),

            Stat::make('Awaiting Review', $inReview)
                ->description($overdueCount > 0 ? "{$overdueCount} overdue" : 'All on track')
                ->descriptionIcon($overdueCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($overdueCount > 0 ? 'danger' : 'success'),

            Stat::make('Published', $publishedProjects)
                ->description('Videos live on YouTube')
                ->descriptionIcon('heroicon-m-rocket-launch')
                ->chart([0, 0, 1, 0, $publishedProjects])
                ->color('success'),

            Stat::make('Team Members', $teamId
                ? Team::find($teamId)?->members()->count() ?? 0
                : 0
            )
                ->description('Active members')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('gray'),

            Stat::make('Audit Events', AuditLog::where('team_id', $teamId)->count())
                ->description('Total logged actions')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->chart([0, 0, 1])
                ->color('warning'),
        ];
    }
}