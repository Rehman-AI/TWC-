<?php

namespace App\Filament\Pages;

use App\Enums\PipelineStatus;
use App\Models\VideoProject;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class PipelineBoard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Production';
    protected static ?int $navigationSort = 4;
    protected static ?string $title = 'Pipeline Board';
    public static function canAccess(): bool
    {
        return auth()->user()?->role === \App\Enums\TeamRole::Owner || auth()->user()?->role === \App\Enums\TeamRole::Manager;
    }

    protected static string $view = 'filament.pages.pipeline-board';

    public ?string $channelFilter = null;

    public function getColumns(): array
    {
        $statuses = PipelineStatus::cases();
        $columns = [];

        foreach ($statuses as $status) {
            $columns[] = [
                'status' => $status,
                'label'  => $status->getLabel(),
                'color'  => $status->getColor(),
                'icon'   => $status->getIcon(),
            ];
        }

        return $columns;
    }

    public function getProjectsForStatus(PipelineStatus $status): Collection
    {
        $query = VideoProject::with(['channel', 'assignee'])
            ->where('status', $status);

        if ($this->channelFilter) {
            $query->where('channel_id', $this->channelFilter);
        }

        return $query->orderBy('priority')->orderByDesc('created_at')->get();
    }

    public function moveProject(int $projectId, string $newStatus): void
    {
        $project = VideoProject::findOrFail($projectId);
        $targetStatus = PipelineStatus::from($newStatus);

        if ($project->status->canTransitionTo($targetStatus)) {
            $project->update(['status' => $targetStatus]);
            Notification::make()
                ->title("Moved to {$targetStatus->getLabel()}")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Invalid transition')
                ->body("Cannot move from {$project->status->getLabel()} to {$targetStatus->getLabel()}")
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newProject')
                ->label('New Video')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn () => route('filament.admin.resources.video-projects.create')),
        ];
    }
}