<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ScriptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ScriptVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_project_id',
        'version',
        'content',
        'plain_text',
        'word_count',
        'estimated_duration',
        'status',
        'reviewed_by',
        'reviewed_at',
        'reviewer_notes',
    ];

    protected function casts(): array
    {
        return [
            'content'            => 'array',
            'version'            => 'integer',
            'word_count'         => 'integer',
            'estimated_duration' => 'integer',
            'status'             => ScriptStatus::class,
            'reviewed_at'        => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $script) {
            // Auto-increment version per project
            if (empty($script->version)) {
                $script->version = ($script->videoProject->scriptVersions()->max('version') ?? 0) + 1;
            }

            // Auto-calculate word count from plain text
            if ($script->plain_text && empty($script->word_count)) {
                $script->word_count = str_word_count($script->plain_text);
            }

            // Estimate duration: ~150 words per minute for narration
            if ($script->word_count && empty($script->estimated_duration)) {
                $script->estimated_duration = (int) ceil($script->word_count / 2.5);
            }
        });
    }

    // -- Relationships -----------------------------------------------

    public function videoProject(): BelongsTo
    {
        return $this->belongsTo(VideoProject::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // -- Helpers ------------------------------------------------------

    public function approve(User $reviewer, ?string $notes = null): void
    {
        $this->update([
            'status'         => ScriptStatus::Approved,
            'reviewed_by'    => $reviewer->id,
            'reviewed_at'    => now(),
            'reviewer_notes' => $notes,
        ]);
    }

    public function reject(User $reviewer, string $notes): void
    {
        $this->update([
            'status'         => ScriptStatus::Rejected,
            'reviewed_by'    => $reviewer->id,
            'reviewed_at'    => now(),
            'reviewer_notes' => $notes,
        ]);
    }

    public function isApproved(): bool
    {
        return $this->status === ScriptStatus::Approved;
    }

    /** Get structured content sections */
    public function getHook(): ?string
    {
        return $this->content['hook'] ?? null;
    }

    public function getBody(): ?string
    {
        return $this->content['body'] ?? null;
    }

    public function getCta(): ?string
    {
        return $this->content['cta'] ?? null;
    }

    public function getLoopEnding(): ?string
    {
        return $this->content['loop_ending'] ?? null;
    }

    public function getScenes(): array
    {
        return $this->content['scenes'] ?? [];
    }
}