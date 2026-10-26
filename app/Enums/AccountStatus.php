<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum AccountStatus: string implements HasLabel, HasColor
{
    case Active  = 'active';
    case Revoked = 'revoked';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active  => 'Active',
            self::Revoked => 'Revoked',
            self::Expired => 'Expired',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active  => 'success',
            self::Revoked => 'danger',
            self::Expired => 'warning',
        };
    }
}