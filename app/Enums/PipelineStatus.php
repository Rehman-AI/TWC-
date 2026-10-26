<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PipelineStatus: string implements HasLabel, HasColor, HasIcon
{
    case Idea              = 'idea';
    case Scripting         = 'scripting';
    case ScriptReview      = 'script_review';
    case ScriptApproved    = 'script_approved';
    case ScenesCreated     = 'scenes_created';
    case ClipsRequested    = 'clips_requested';
    case ClipsUploaded     = 'clips_uploaded';
    case InEditing         = 'in_editing';
    case FinalUploaded     = 'final_uploaded';
    case AiQa              = 'ai_qa';
    case QaFixes           = 'qa_fixes';
    case SeoReview         = 'seo_review';
    case PolicyReview      = 'policy_review';
    case ReadyForApproval  = 'ready_for_approval';
    case Approved          = 'approved';
    case Scheduled         = 'scheduled';
    case Publishing        = 'publishing';
    case Published         = 'published';
    case Failed            = 'failed';
    case Blocked           = 'blocked';

    public function getLabel(): string
    {
        return match ($this) {
            self::Idea             => 'Idea',
            self::Scripting        => 'Scripting',
            self::ScriptReview     => 'Script Review',
            self::ScriptApproved   => 'Script Approved',
            self::ScenesCreated    => 'Scenes Created',
            self::ClipsRequested   => 'Clips Requested',
            self::ClipsUploaded    => 'Clips Uploaded',
            self::InEditing        => 'In Editing',
            self::FinalUploaded    => 'Final Uploaded',
            self::AiQa             => 'AI QA',
            self::QaFixes          => 'QA Fixes',
            self::SeoReview        => 'SEO Review',
            self::PolicyReview     => 'Policy Review',
            self::ReadyForApproval => 'Ready for Approval',
            self::Approved         => 'Approved',
            self::Scheduled        => 'Scheduled',
            self::Publishing       => 'Publishing',
            self::Published        => 'Published',
            self::Failed           => 'Failed',
            self::Blocked          => 'Blocked',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Idea             => 'gray',
            self::Scripting        => 'info',
            self::ScriptReview     => 'warning',
            self::ScriptApproved   => 'success',
            self::ScenesCreated    => 'info',
            self::ClipsRequested   => 'warning',
            self::ClipsUploaded    => 'info',
            self::InEditing        => 'warning',
            self::FinalUploaded    => 'info',
            self::AiQa             => 'warning',
            self::QaFixes          => 'danger',
            self::SeoReview        => 'warning',
            self::PolicyReview     => 'warning',
            self::ReadyForApproval => 'info',
            self::Approved         => 'success',
            self::Scheduled        => 'success',
            self::Publishing       => 'warning',
            self::Published        => 'success',
            self::Failed           => 'danger',
            self::Blocked          => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Idea             => 'heroicon-o-light-bulb',
            self::Scripting        => 'heroicon-o-pencil-square',
            self::ScriptReview     => 'heroicon-o-eye',
            self::ScriptApproved   => 'heroicon-o-check-circle',
            self::ScenesCreated    => 'heroicon-o-film',
            self::ClipsRequested   => 'heroicon-o-arrow-up-tray',
            self::ClipsUploaded    => 'heroicon-o-arrow-down-tray',
            self::InEditing        => 'heroicon-o-scissors',
            self::FinalUploaded    => 'heroicon-o-document-check',
            self::AiQa             => 'heroicon-o-cpu-chip',
            self::QaFixes          => 'heroicon-o-wrench-screwdriver',
            self::SeoReview        => 'heroicon-o-magnifying-glass',
            self::PolicyReview     => 'heroicon-o-shield-check',
            self::ReadyForApproval => 'heroicon-o-hand-thumb-up',
            self::Approved         => 'heroicon-o-check-badge',
            self::Scheduled        => 'heroicon-o-clock',
            self::Publishing       => 'heroicon-o-arrow-up-circle',
            self::Published        => 'heroicon-o-rocket-launch',
            self::Failed           => 'heroicon-o-x-circle',
            self::Blocked          => 'heroicon-o-no-symbol',
        };
    }

    /** Statuses that count as "in progress" for quota tracking */
    public function isActive(): bool
    {
        return ! in_array($this, [
            self::Published,
            self::Failed,
            self::Blocked,
        ]);
    }

    /** Statuses in the scripting phase (for Script Board) */
    public function isScriptPhase(): bool
    {
        return in_array($this, [
            self::Idea,
            self::Scripting,
            self::ScriptReview,
            self::ScriptApproved,
        ]);
    }

    /** Allowed next statuses for pipeline transitions */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Idea             => [self::Scripting],
            self::Scripting        => [self::ScriptReview],
            self::ScriptReview     => [self::ScriptApproved, self::Scripting],
            self::ScriptApproved   => [self::ScenesCreated],
            self::ScenesCreated    => [self::ClipsRequested],
            self::ClipsRequested   => [self::ClipsUploaded],
            self::ClipsUploaded    => [self::InEditing],
            self::InEditing        => [self::FinalUploaded],
            self::FinalUploaded    => [self::AiQa],
            self::AiQa             => [self::QaFixes, self::SeoReview],
            self::QaFixes          => [self::InEditing],
            self::SeoReview        => [self::PolicyReview],
            self::PolicyReview     => [self::ReadyForApproval, self::Blocked],
            self::ReadyForApproval => [self::Approved],
            self::Approved         => [self::Scheduled],
            self::Scheduled        => [self::Publishing],
            self::Publishing       => [self::Published, self::Failed],
            self::Published        => [],
            self::Failed           => [self::Scripting, self::ClipsRequested, self::InEditing],
            self::Blocked          => [self::Scripting],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions());
    }
}