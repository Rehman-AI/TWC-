<?php

namespace App\Filament\Resources\VideoProjectResource\Pages;

use App\Filament\Resources\VideoProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVideoProjects extends ListRecords
{
    protected static string $resource = VideoProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}