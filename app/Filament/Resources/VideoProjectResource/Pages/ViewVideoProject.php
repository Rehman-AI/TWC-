<?php

namespace App\Filament\Resources\VideoProjectResource\Pages;

use App\Enums\PipelineStatus;
use App\Enums\ScriptStatus;
use App\Filament\Resources\VideoProjectResource;
use App\Jobs\BreakdownScenesJob;
use App\Jobs\CreateDriveFoldersJob;
use App\Jobs\GenerateEditPackJob;
use App\Jobs\GenerateScriptJob;
use App\Jobs\LocalizeScriptJob;
use App\Models\Channel;
use App\Services\Policy\PolicyEngine;
use App\Services\QA\QaReviewer;
use App\Services\SEO\SeoGenerator;
use App\Services\Publishing\YouTubePublisher;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewVideoProject extends ViewRecord
{
    protected static string $resource = VideoProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            // ---- SCRIPT PHASE ----

            Actions\Action::make('generateScript')
                ->label('Generate Script')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->visible(fn () => in_array($this->record->status, [PipelineStatus::Idea, PipelineStatus::Scripting]))
                ->form([
                    Forms\Components\TextInput::make('topic')
                        ->default(fn () => $this->record->title)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update(['status' => PipelineStatus::Scripting]);
                    GenerateScriptJob::dispatch($this->record, ['topic' => $data['topic']]);
                    Notification::make()->title('Script generation queued')->success()->send();
                }),

            Actions\Action::make('approveScript')
                ->label('Approve Script')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $this->record->status === PipelineStatus::ScriptReview && auth()->user()?->isTeamManager())
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->currentScript?->approve(auth()->user());
                    $this->record->transitionTo(PipelineStatus::ScriptApproved);
                    Notification::make()->title('Script approved!')->success()->send();
                }),

            // ---- PRODUCTION PHASE ----

            Actions\Action::make('breakdownScenes')
                ->label('Create Scenes')
                ->icon('heroicon-o-film')
                ->color('info')
                ->visible(fn () => $this->record->status === PipelineStatus::ScriptApproved)
                ->requiresConfirmation()
                ->action(function () {
                    BreakdownScenesJob::dispatch($this->record);
                    Notification::make()->title('Scene breakdown queued')->success()->send();
                }),

            Actions\Action::make('generateEditPack')
                ->label('Edit Pack')
                ->icon('heroicon-o-archive-box')
                ->color('primary')
                ->visible(fn () => in_array($this->record->status, [PipelineStatus::ScenesCreated, PipelineStatus::ClipsRequested]))
                ->action(function () {
                    GenerateEditPackJob::dispatch($this->record);
                    $this->record->transitionTo(PipelineStatus::ClipsRequested);
                    Notification::make()->title('Edit Pack generated')->success()->send();
                }),

            // ---- QA PHASE ----

            Actions\Action::make('runQA')
                ->label('Run QA')
                ->icon('heroicon-o-magnifying-glass')
                ->color('warning')
                ->visible(fn () => in_array($this->record->status, [PipelineStatus::FinalUploaded, PipelineStatus::QaFixes]))
                ->action(function () {
                    $qa = app(QaReviewer::class)->review($this->record, auth()->id());
                    $this->record->transitionTo(PipelineStatus::AiQa);

                    $color = $qa->isPassing() ? 'success' : 'warning';
                    Notification::make()
                        ->title("QA Round {$qa->round}: {$qa->overall_result}")
                        ->body("Score: {$qa->score}/100 — {$qa->getOpenIssueCount()} issues found")
                        ->color($color)
                        ->send();
                }),

            Actions\Action::make('returnToEditor')
                ->label('Return for Fixes')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->visible(fn () => $this->record->status === PipelineStatus::AiQa && $this->record->latestQaReview?->getOpenIssueCount() > 0)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->transitionTo(PipelineStatus::QaFixes);
                    Notification::make()->title('Returned to editor for QA fixes')->warning()->send();
                }),

            // ---- SEO ----

            Actions\Action::make('generateSEO')
                ->label('Generate SEO')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->visible(fn () => $this->record->approvedScript() !== null)
                ->action(function () {
                    $seo = app(SeoGenerator::class)->generate($this->record);
                    Notification::make()
                        ->title("SEO Score: {$seo->seo_score}/100 ({$seo->getScoreLabel()})")
                        ->body("Title: {$seo->seo_title}")
                        ->color($seo->getScoreColor())
                        ->send();
                }),

            // ---- POLICY REVIEW ----

            Actions\Action::make('runPolicy')
                ->label('Policy Check')
                ->icon('heroicon-o-shield-check')
                ->color('warning')
                ->visible(fn () => in_array($this->record->status, [PipelineStatus::AiQa, PipelineStatus::PolicyReview, PipelineStatus::SeoReview]))
                ->action(function () {
                    $review = app(PolicyEngine::class)->review($this->record, auth()->id());
                    $this->record->transitionTo(PipelineStatus::PolicyReview);

                    $icon = match ($review->overall_result) {
                        'pass'  => 'success',
                        'warn'  => 'warning',
                        'block' => 'danger',
                    };
                    Notification::make()
                        ->title("Policy: " . strtoupper($review->overall_result))
                        ->body("Pass: {$review->getPassCount()}, Warn: {$review->getWarnCount()}, Block: {$review->getBlockCount()}")
                        ->color($icon)
                        ->send();
                }),

            // ---- APPROVAL & PUBLISH ----

            Actions\Action::make('approve')
                ->label('Approve for Publish')
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->visible(fn () => $this->record->status === PipelineStatus::ReadyForApproval && auth()->user()?->isTeamManager())
                ->requiresConfirmation()
                ->modalHeading('Approve for Publishing')
                ->modalDescription('This video will be queued for publishing to YouTube Shorts. This action cannot be undone.')
                ->action(function () {
                    $this->record->transitionTo(PipelineStatus::Approved);
                    Notification::make()->title('Video approved for publishing!')->success()->send();
                }),

            Actions\Action::make('publish')
                ->label('Publish to YouTube')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('danger')
                ->visible(fn () => $this->record->status === PipelineStatus::Approved && auth()->user()?->isTeamManager())
                ->requiresConfirmation()
                ->form([
                    Forms\Components\DateTimePicker::make('scheduled_at')
                        ->label('Schedule for (leave blank to publish now)')
                        ->helperText('Set a future date/time to schedule the video instead of publishing immediately'),
                ])
                ->action(function (array $data) {
                    $account = $this->record->channel->youtubeAccount();
                    if (! $account) {
                        Notification::make()->title('No YouTube account connected')->danger()->send();
                        return;
                    }

                    $scheduledAt = ! empty($data['scheduled_at']) ? \Carbon\Carbon::parse($data['scheduled_at']) : null;

                    // In a real environment, this would dispatch a job. For now, mark as scheduled/published.
                    $this->record->transitionTo($scheduledAt ? PipelineStatus::Scheduled : PipelineStatus::Publishing);
                    Notification::make()
                        ->title($scheduledAt ? 'Video scheduled' : 'Publishing started')
                        ->success()
                        ->send();
                }),

            // ---- UTILITY ----

            Actions\Action::make('setupDrive')
                ->label('Setup Drive')
                ->icon('heroicon-o-folder-plus')
                ->color('gray')
                ->visible(fn () => empty($this->record->drive_folder_id))
                ->action(function () {
                    CreateDriveFoldersJob::dispatch($this->record);
                    Notification::make()->title('Drive folders being created')->success()->send();
                }),

            Actions\Action::make('localize')
                ->label('Localize')
                ->icon('heroicon-o-language')
                ->color('gray')
                ->visible(fn () => $this->record->approvedScript() !== null)
                ->form([
                    Forms\Components\Select::make('target_channel_id')
                        ->label('Target Channel')
                        ->options(Channel::where('id', '!=', $this->record->channel_id)->pluck('name', 'id'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    $target = Channel::findOrFail($data['target_channel_id']);
                    LocalizeScriptJob::dispatch($this->record, $target);
                    Notification::make()->title("Localization queued for {$target->name}")->success()->send();
                }),
        ];
    }
}