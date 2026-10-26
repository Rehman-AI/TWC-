<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QaReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_project_id',
        'round',
        'overall_result',
        'score',
        'issues',
        'checklist',
        'summary',
        'reviewed_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'round'       => 'integer',
            'score'       => 'integer',
            'issues'      => 'array',
            'checklist'   => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function videoProject(): BelongsTo
    {
        return $this->belongsTo(VideoProject::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPassing(): bool
    {
        return $this->overall_result === 'pass';
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function getOpenIssueCount(): int
    {
        return collect($this->issues ?? [])->where('resolved', false)->count();
    }

    public function getIssuesByTimestamp(): array
    {
        return collect($this->issues ?? [])
            ->sortBy('timestamp')
            ->values()
            ->toArray();
    }

    public function markResolved(): void
    {
        $this->update(['resolved_at' => now()]);
    }
}