<?php

namespace App\Filament\Pages;

use App\Enums\PipelineStatus;
use App\Models\VideoProject;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class ScriptBoard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationGroup = 'Production';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Script Board';
    public static function canAccess(): bool
    {
        return auth()->user()?->role === \App\Enums\TeamRole::Owner || auth()->user()?->role === \App\Enums\TeamRole::Manager;
    }

    protected static string $view = 'filament.pages.script-board';

    public ?string $channelFilter = null;

    public function mount(): void
    {
        $this->channelFilter = null;
    }

    public function getColumns(): array
    {
        return [
            ['status' => PipelineStatus::Idea, 'label' => 'Ideas', 'color' => '#71717a'],
            ['status' => PipelineStatus::Scripting, 'label' => 'Scripting', 'color' => '#0ea5e9'],
            ['status' => PipelineStatus::ScriptReview, 'label' => 'In Review', 'color' => '#f59e0b'],
            ['status' => PipelineStatus::ScriptApproved, 'label' => 'Approved', 'color' => '#10b981'],
        ];
    }

    public function getProjectsForStatus(PipelineStatus $status): Collection
    {
        $query = VideoProject::with(['channel', 'assignee', 'currentScript'])
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
                ->title("Moved \"{$project->title}\" to {$targetStatus->getLabel()}")
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
            Action::make('newIdea')
                ->label('New Idea')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn () => route('filament.admin.resources.video-projects.create')),
        ];
    }
}