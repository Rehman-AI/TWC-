<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum PlatformEnum: string implements HasLabel, HasIcon, HasColor
{
    case YouTube   = 'youtube';
    case TikTok    = 'tiktok';
    case Instagram = 'instagram';
    case Facebook  = 'facebook';
    case Snapchat  = 'snapchat';

    public function getLabel(): string
    {
        return match ($this) {
            self::YouTube   => 'YouTube',
            self::TikTok    => 'TikTok',
            self::Instagram => 'Instagram',
            self::Facebook  => 'Facebook',
            self::Snapchat  => 'Snapchat',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::YouTube   => 'heroicon-o-play',
            self::TikTok    => 'heroicon-o-musical-note',
            self::Instagram => 'heroicon-o-camera',
            self::Facebook  => 'heroicon-o-globe-alt',
            self::Snapchat  => 'heroicon-o-chat-bubble-left',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::YouTube   => 'danger',
            self::TikTok    => 'info',
            self::Instagram => 'warning',
            self::Facebook  => 'info',
            self::Snapchat  => 'warning',
        };
    }

    public function supportsApi(): bool
    {
        return match ($this) {
            self::YouTube   => true,
            self::TikTok    => true,
            self::Instagram => true,
            self::Facebook  => true,
            self::Snapchat  => false,
        };
    }

    public function maxDurationSeconds(): int
    {
        return match ($this) {
            self::YouTube   => 60,
            self::TikTok    => 60,
            self::Instagram => 90,
            self::Facebook  => 60,
            self::Snapchat  => 60,
        };
    }
}