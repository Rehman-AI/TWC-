<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Team $team): bool
    {
        return $team->hasMember($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Team $team): bool
    {
        $role = $team->memberRole($user);
        return $role && $role->canManageTeam();
    }

    public function delete(User $user, Team $team): bool
    {
        return $team->memberRole($user) === TeamRole::Owner;
    }

    public function manageMembers(User $user, Team $team): bool
    {
        $role = $team->memberRole($user);
        return $role && $role->canManageTeam();
    }

    public function changeRole(User $user, Team $team, User $target): bool
    {
        $userRole = $team->memberRole($user);
        $targetRole = $team->memberRole($target);

        if (!$userRole || !$targetRole) return false;
        if ($user->id === $target->id) return false;

        return $userRole->outranks($targetRole);
    }
}