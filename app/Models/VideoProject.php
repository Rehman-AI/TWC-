<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PipelineStatus;
use App\Enums\VideoPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class VideoProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'title',
        'slug',
        'status',
        'assigned_to',
        'language',
        'target_duration',
        'priority',
        'due_date',
        'notes',
        'metadata',
        'drive_folder_id',
    ];

    protected function casts(): array
    {
        return [
            'status'          => PipelineStatus::class,
            'priority'        => VideoPriority::class,
            'due_date'        => 'date',
            'target_duration' => 'integer',
            'metadata'        => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->title) . '-' . Str::random(6);
            }
            if (empty($project->language)) {
                $project->language = $project->channel?->language ?? 'en';
            }
        });
    }

    // -- Relationships -----------------------------------------------

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scriptVersions(): HasMany
    {
        return $this->hasMany(ScriptVersion::class)->orderByDesc('version');
    }

    public function currentScript(): HasOne
    {
        return $this->hasOne(ScriptVersion::class)->latestOfMany('version');
    }

    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class)->orderBy('order');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function seoMetadata(): HasOne
    {
        return $this->hasOne(SeoMetadata::class);
    }

    public function policyReviews(): HasMany
    {
        return $this->hasMany(PolicyReview::class)->orderByDesc('created_at');
    }

    public function latestPolicyReview(): HasOne
    {
        return $this->hasOne(PolicyReview::class)->latestOfMany();
    }

    public function qaReviews(): HasMany
    {
        return $this->hasMany(QaReview::class)->orderByDesc('round');
    }

    public function latestQaReview(): HasOne
    {
        return $this->hasOne(QaReview::class)->latestOfMany();
    }

    public function publishLogs(): HasMany
    {
        return $this->hasMany(PublishLog::class)->orderByDesc('created_at');
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Task::class, 'project_id');
    }

    /** Check if all tasks are done and update project status accordingly */
    public function checkCompletion(): void
    {
        $allTasks = $this->tasks()->count();
        $doneTasks = $this->tasks()->where('status', 'done')->count();

        if ($allTasks > 0 && $allTasks === $doneTasks) {
            $this->update(['status' => \App\Enums\ProjectStatus::Complete]);
            return;
        }

        // Determine current phase based on which tasks exist and are done
        $scriptDone = $this->tasks()->where('type', 'script')->where('status', 'done')->exists();
        $videoDone = $this->tasks()->where('type', 'video')->where('status', 'done')->exists();
        $hasPostingTasks = $this->tasks()->whereIn('type', ['youtube', 'facebook', 'instagram', 'tiktok'])->exists();

        if ($videoDone && $hasPostingTasks) {
            $this->update(['status' => \App\Enums\ProjectStatus::Posting]);
        } elseif ($scriptDone) {
            $this->update(['status' => \App\Enums\ProjectStatus::VideoMaking]);
        } else {
            $this->update(['status' => \App\Enums\ProjectStatus::Scripting]);
        }
    }
    // -- Pipeline helpers ---------------------------------------------

    public function transitionTo(PipelineStatus $status): bool
    {
        if (! $this->status->canTransitionTo($status)) {
            return false;
        }

        $this->update(['status' => $status]);
        return true;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status->isActive();
    }

    public function totalSceneDuration(): float
    {
        return $this->scenes()->sum('duration_seconds');
    }

    /** Get the latest approved script */
    public function approvedScript(): ?ScriptVersion
    {
        return $this->scriptVersions()
            ->where('status', 'approved')
            ->first();
    }
}