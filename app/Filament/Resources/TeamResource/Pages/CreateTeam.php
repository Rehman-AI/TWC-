<?php
namespace App\Filament\Resources\TeamResource\Pages;
use App\Enums\AuditAction;
use App\Enums\TeamRole;
use App\Filament\Resources\TeamResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;
class CreateTeam extends CreateRecord
{
    protected static string $resource = TeamResource::class;
    protected function afterCreate(): void
    {
        $team = $this->record;
        $user = auth()->user();
        $team->update(['owner_id' => $user->id]);
        $team->addMember($user, TeamRole::Owner);
        $user->switchTeam($team);
        app(AuditLogger::class)->logCreated(AuditAction::TeamCreated, $team);
    }
}
