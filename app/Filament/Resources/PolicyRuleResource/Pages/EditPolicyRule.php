<?php

namespace App\Filament\Resources\PolicyRuleResource\Pages;

use App\Filament\Resources\PolicyRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPolicyRule extends EditRecord
{
    protected static string $resource = PolicyRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}