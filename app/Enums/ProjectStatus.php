<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProjectStatus: string implements HasLabel, HasColor, HasIcon
{
    case Idea        = 'idea';
    case Scripting   = 'scripting';
    case VideoMaking = 'video_making';
    case Posting     = 'posting';
    case Complete    = 'complete';

    public function getLabel(): string
    {
        return match ($this) {
            self::Idea        => 'Idea',
            self::Scripting   => 'Scripting',
            self::VideoMaking => 'Video Making',
            self::Posting     => 'Posting',
            self::Complete    => 'Complete',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Idea        => 'gray',
            self::Scripting   => 'info',
            self::VideoMaking => 'warning',
            self::Posting     => 'primary',
            self::Complete    => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Idea        => 'heroicon-o-light-bulb',
            self::Scripting   => 'heroicon-o-pencil-square',
            self::VideoMaking => 'heroicon-o-video-camera',
            self::Posting     => 'heroicon-o-arrow-up-tray',
            self::Complete    => 'heroicon-o-check-badge',
        };
    }
}