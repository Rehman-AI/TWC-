<?php
namespace App\Filament\Resources\ChannelResource\Pages;
use App\Enums\AuditAction;
use App\Filament\Resources\ChannelResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;
class CreateChannel extends CreateRecord
{
    protected static string $resource = ChannelResource::class;
    protected function afterCreate(): void
    {
        app(AuditLogger::class)->logCreated(AuditAction::ChannelCreated, $this->record);
    }
}
