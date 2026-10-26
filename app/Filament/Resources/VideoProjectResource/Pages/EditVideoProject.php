<?php

namespace App\Filament\Resources\VideoProjectResource\Pages;

use App\Filament\Resources\VideoProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVideoProject extends EditRecord
{
    protected static string $resource = VideoProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}