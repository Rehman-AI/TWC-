<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TaskType: string implements HasLabel, HasColor, HasIcon
{
    case Script    = 'script';
    case Video     = 'video';
    case YouTube   = 'youtube';
    case Facebook  = 'facebook';
    case Instagram = 'instagram';
    case TikTok    = 'tiktok';

    public function getLabel(): string
    {
        return match ($this) {
            self::Script    => 'Script Writing',
            self::Video     => 'Video Making',
            self::YouTube   => 'YouTube Posting',
            self::Facebook  => 'Facebook Posting',
            self::Instagram => 'Instagram Posting',
            self::TikTok    => 'TikTok Posting',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Script    => 'info',
            self::Video     => 'warning',
            self::YouTube   => 'danger',
            self::Facebook  => 'primary',
            self::Instagram => 'success',
            self::TikTok    => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Script    => 'heroicon-o-pencil-square',
            self::Video     => 'heroicon-o-video-camera',
            self::YouTube   => 'heroicon-o-play',
            self::Facebook  => 'heroicon-o-share',
            self::Instagram => 'heroicon-o-camera',
            self::TikTok    => 'heroicon-o-musical-note',
        };
    }

    public function requiredRole(): string
    {
        return match ($this) {
            self::Script    => 'scriptwriter',
            self::Video     => 'video_maker',
            self::YouTube   => 'youtube_head',
            self::Facebook  => 'facebook_head',
            self::Instagram => 'instagram_head',
            self::TikTok    => 'tiktok_head',
        };
    }

    public function isPlatformPosting(): bool
    {
        return in_array($this, [self::YouTube, self::Facebook, self::Instagram, self::TikTok]);
    }
}