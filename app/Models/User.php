<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TeamRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'team_id',
        'avatar_url',
        'timezone',
        'role',
        'custom_role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => TeamRole::class,
        ];
    }

    // ── Filament ──────────────────────────────────────

    public function isOwner(): bool
    {
        return $this->role === 'owner' || ($this->role && $this->role->value === 'owner');
    }

    public function isAdmin(): bool
    {
        return $this->isOwner() || ($this->customRole && $this->customRole->isManager());
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // All authenticated users can access the panel
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    // ── Relationships ─────────────────────────────────

    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'channel_user')
            ->withPivot('role');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // ── Team helpers ──────────────────────────────────

    public function switchTeam(Team $team): void
    {
        $this->update(['team_id' => $team->id]);
    }

    public function teamRole(?Team $team = null): ?TeamRole
    {
        $team ??= $this->currentTeam;
        if (! $team) {
            return null;
        }

        $pivot = $this->teams()->where('team_id', $team->id)->first()?->pivot;
        return $pivot ? TeamRole::from($pivot->role) : null;
    }

    public function isTeamOwner(?Team $team = null): bool
    {
        return $this->teamRole($team) === TeamRole::Owner;
    }

    public function customRole(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CustomRole::class, 'custom_role_id');
    }

    public function assignedTasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Task::class, 'assigned_to');
    }

    public function isTeamManager(?Team $team = null): bool
    {
        $role = $this->teamRole($team);
        return $role === TeamRole::Owner || $role === TeamRole::Manager;
    }

    // ── Channel helpers ───────────────────────────────

    public function channelRole(Channel $channel): ?TeamRole
    {
        $pivot = $this->channels()->where('channel_id', $channel->id)->first()?->pivot;
        return $pivot ? TeamRole::from($pivot->role) : null;
    }

    public function canAccessChannel(Channel $channel): bool
    {
        // Team owners/managers always have access
        if ($this->isTeamManager($channel->team)) {
            return true;
        }

        return $this->channels()->where('channel_id', $channel->id)->exists();
    }
}
