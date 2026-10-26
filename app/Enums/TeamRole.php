<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TeamRole: string implements HasLabel, HasColor, HasIcon
{
    case Owner         = 'owner';
    case Manager       = 'manager';
    case Member        = 'member';
    case Scriptwriter  = 'scriptwriter';
    case VideoMaker    = 'video_maker';
    case YouTubeHead   = 'youtube_head';
    case FacebookHead  = 'facebook_head';
    case InstagramHead = 'instagram_head';
    case TikTokHead    = 'tiktok_head';

    public function getLabel(): string
    {
        return match ($this) {
            self::Owner         => 'Owner',
            self::Manager       => 'Manager',
            self::Member        => 'Team Member',
            self::Scriptwriter  => 'Scriptwriter',
            self::VideoMaker    => 'Video Maker',
            self::YouTubeHead   => 'YouTube Head',
            self::FacebookHead  => 'Facebook Head',
            self::InstagramHead => 'Instagram Head',
            self::TikTokHead    => 'TikTok Head',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Owner         => 'danger',
            self::Manager       => 'warning',
            self::Member        => 'gray',
            self::Scriptwriter  => 'info',
            self::VideoMaker    => 'success',
            self::YouTubeHead   => 'danger',
            self::FacebookHead  => 'primary',
            self::InstagramHead => 'success',
            self::TikTokHead    => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Owner         => 'heroicon-o-shield-check',
            self::Manager       => 'heroicon-o-briefcase',
            self::Member        => 'heroicon-o-user',
            self::Scriptwriter  => 'heroicon-o-pencil-square',
            self::VideoMaker    => 'heroicon-o-video-camera',
            self::YouTubeHead   => 'heroicon-o-play',
            self::FacebookHead  => 'heroicon-o-share',
            self::InstagramHead => 'heroicon-o-camera',
            self::TikTokHead    => 'heroicon-o-musical-note',
        };
    }

    public function isOwnerOrManager(): bool
    {
        return in_array($this, [self::Owner, self::Manager]);
    }

    public function canApprove(): bool
    {
        return $this->isOwnerOrManager();
    }

    public function canManageTeam(): bool
    {
        return $this->isOwnerOrManager();
    }

    public function canAssignWork(): bool
    {
        return $this->isOwnerOrManager();
    }

    public function taskTypes(): array
    {
        return match ($this) {
            self::Owner, self::Manager => array_column(\App\Enums\TaskType::cases(), 'value'),
            self::Scriptwriter  => ['script'],
            self::VideoMaker    => ['video'],
            self::YouTubeHead   => ['youtube'],
            self::FacebookHead  => ['facebook'],
            self::InstagramHead => ['instagram'],
            self::TikTokHead    => ['tiktok'],
            self::Member        => [],
        };
    }

    public function rank(): int
    {
        return match ($this) {
            self::Owner   => 0,
            self::Manager => 1,
            default       => 2,
        };
    }

    public function outranks(self $other): bool
    {
        return $this->rank() < $other->rank();
    }
}