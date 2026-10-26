<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum SceneStatus: string implements HasLabel, HasColor
{
    case Pending        = 'pending';
    case ClipsRequested = 'clips_requested';
    case ClipsUploaded  = 'clips_uploaded';
    case Ready          = 'ready';
    case Failed         = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending        => 'Pending',
            self::ClipsRequested => 'Clips Requested',
            self::ClipsUploaded  => 'Clips Uploaded',
            self::Ready          => 'Ready',
            self::Failed         => 'Failed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending        => 'gray',
            self::ClipsRequested => 'warning',
            self::ClipsUploaded  => 'info',
            self::Ready          => 'success',
            self::Failed         => 'danger',
        };
    }
}