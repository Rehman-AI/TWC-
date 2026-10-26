<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum VideoPriority: string implements HasLabel, HasColor
{
    case Low    = 'low';
    case Normal = 'normal';
    case High   = 'high';
    case Urgent = 'urgent';

    public function getLabel(): string
    {
        return match ($this) {
            self::Low    => 'Low',
            self::Normal => 'Normal',
            self::High   => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Low    => 'gray',
            self::Normal => 'info',
            self::High   => 'warning',
            self::Urgent => 'danger',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::Urgent => 0,
            self::High   => 1,
            self::Normal => 2,
            self::Low    => 3,
        };
    }
}