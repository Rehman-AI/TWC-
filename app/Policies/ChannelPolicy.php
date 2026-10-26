<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\TeamRole;
use App\Models\Channel;
use App\Models\User;

class ChannelPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Channel $channel): bool
    {
        return $user->canAccessChannel($channel);
    }

    public function create(User $user): bool
    {
        $team = $user->currentTeam;
        if (!$team) return false;
        $role = $team->memberRole($user);
        return $role && $role->canManageTeam();
    }

    public function update(User $user, Channel $channel): bool
    {
        if ($user->isTeamManager($channel->team)) return true;
        $role = $channel->userRole($user);
        return $role && $role->canManageTeam();
    }

    public function delete(User $user, Channel $channel): bool
    {
        return $user->isTeamOwner($channel->team);
    }

    public function manageAccess(User $user, Channel $channel): bool
    {
        if ($user->isTeamManager($channel->team)) return true;
        $role = $channel->userRole($user);
        return $role && $role->canManageTeam();
    }
}