<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\PlatformEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'platform',
        'platform_account_id',
        'account_name',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'platform'         => PlatformEnum::class,
            'status'           => AccountStatus::class,
            'access_token'     => 'encrypted',
            'refresh_token'    => 'encrypted',
            'token_expires_at' => 'datetime',
            'scopes'           => 'array',
            'metadata'         => 'array',
        ];
    }

    // -- Relationships -----------------------------------------------

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    // -- Helpers ------------------------------------------------------

    public function isTokenExpired(): bool
    {
        if (! $this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    public function isTokenExpiringSoon(int $minutes = 30): bool
    {
        if (! $this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->subMinutes($minutes)->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === AccountStatus::Active;
    }

    public function markRevoked(): void
    {
        $this->update([
            'status'        => AccountStatus::Revoked,
            'access_token'  => null,
            'refresh_token' => null,
        ]);
    }
}