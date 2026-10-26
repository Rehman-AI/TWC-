<?php

namespace App\Filament\Resources\VideoProjectResource\Pages;

use App\Filament\Resources\VideoProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVideoProject extends CreateRecord
{
    protected static string $resource = VideoProjectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}