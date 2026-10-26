<?php

namespace App\Filament\Resources\PolicyRuleResource\Pages;

use App\Filament\Resources\PolicyRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPolicyRules extends ListRecords
{
    protected static string $resource = PolicyRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}