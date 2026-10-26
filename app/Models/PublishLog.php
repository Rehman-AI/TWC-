<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlatformEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_project_id',
        'platform_account_id',
        'platform',
        'platform_video_id',
        'platform_url',
        'status',
        'scheduled_at',
        'published_at',
        'ai_disclosure_applied',
        'upload_metadata',
        'error_message',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'platform'              => PlatformEnum::class,
            'scheduled_at'          => 'datetime',
            'published_at'          => 'datetime',
            'approved_at'           => 'datetime',
            'ai_disclosure_applied' => 'boolean',
            'upload_metadata'       => 'array',
        ];
    }

    public function videoProject(): BelongsTo
    {
        return $this->belongsTo(VideoProject::class);
    }

    public function platformAccount(): BelongsTo
    {
        return $this->belongsTo(PlatformAccount::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_at?->isFuture();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_approval';
    }
}