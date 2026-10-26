<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TeamRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'slug',
        'niche',
        'language',
        'description',
        'tts_provider',
        'stock_provider',
        'approval_required',
        'posting_schedule',
        'style_guide',
        'brand_kit',
        'banned_words',
        'is_active',
        'target_platforms',
        'video_min_duration',
        'video_max_duration',
    ];

    protected function casts(): array
    {
        return [
            'approval_required' => 'boolean',
            'posting_schedule'  => 'array',
            'style_guide'       => 'array',
            'brand_kit'         => 'array',
            'banned_words'      => 'array',
            'is_active'         => 'boolean',
            'target_platforms'  => 'array',
        ];
    }

    // -- Relationships -----------------------------------------------

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'channel_user')
            ->withPivot('role');
    }

    public function platformAccounts(): HasMany
    {
        return $this->hasMany(PlatformAccount::class);
    }

    public function videoProjects(): HasMany
    {
        return $this->hasMany(VideoProject::class);
    }

    public function musicPlaylist(): BelongsToMany
    {
        return $this->belongsToMany(Music::class, 'channel_music');
    }

    // -- Helpers -------------------------------------------------------

    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function userRole(User $user): ?TeamRole
    {
        $pivot = $this->users()->where('user_id', $user->id)->first()?->pivot;
        return $pivot ? TeamRole::from($pivot->role) : null;
    }

    public function grantAccess(User $user, TeamRole $role = TeamRole::Member): void
    {
        $this->users()->syncWithoutDetaching([
            $user->id => ['role' => $role->value],
        ]);
    }

    public function revokeAccess(User $user): void
    {
        $this->users()->detach($user->id);
    }

    public function activeProjectCount(): int
    {
        return $this->videoProjects()
            ->whereNotIn('status', ['published', 'failed', 'blocked'])
            ->count();
    }

    public function publishedProjectCount(): int
    {
        return $this->videoProjects()
            ->where('status', 'published')
            ->count();
    }

    /** Get the YouTube platform account, if connected */
    public function youtubeAccount(): ?PlatformAccount
    {
        return $this->platformAccounts()
            ->where('platform', 'youtube')
            ->first();
    }

    /** Get brand kit color or default */
    public function getBrandColor(string $key, string $default = '#e8227a'): string
    {
        return $this->brand_kit['colors'][$key] ?? $default;
    }
}