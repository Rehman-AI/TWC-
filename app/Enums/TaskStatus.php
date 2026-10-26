<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TaskStatus: string implements HasLabel, HasColor, HasIcon
{
    case Pending    = 'pending';
    case InProgress = 'in_progress';
    case Done       = 'done';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending    => 'Pending',
            self::InProgress => 'In Progress',
            self::Done       => 'Done',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending    => 'gray',
            self::InProgress => 'warning',
            self::Done       => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending    => 'heroicon-o-clock',
            self::InProgress => 'heroicon-o-arrow-path',
            self::Done       => 'heroicon-o-check-circle',
        };
    }
}