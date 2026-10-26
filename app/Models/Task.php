<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskStatus;
use App\Enums\TaskType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'type'         => TaskType::class,
        'status'       => TaskStatus::class,
        'deadline'     => 'datetime',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    // -- Relationships --
    public function project(): BelongsTo { return $this->belongsTo(VideoProject::class, 'project_id'); }
    public function channel(): BelongsTo { return $this->belongsTo(Channel::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function assigner(): BelongsTo { return $this->belongsTo(User::class, 'assigned_by'); }

    // -- Scopes --
    public function scopeForUser(Builder $q, User $u): Builder { return $q->where('assigned_to', $u->id); }
    public function scopePending(Builder $q): Builder { return $q->where('status', TaskStatus::Pending); }
    public function scopeInProgress(Builder $q): Builder { return $q->where('status', TaskStatus::InProgress); }
    public function scopeDone(Builder $q): Builder { return $q->where('status', TaskStatus::Done); }
    public function scopeOverdue(Builder $q): Builder
    {
        return $q->where('deadline', '<', now())->where('status', '!=', TaskStatus::Done);
    }

    // -- Helpers --
    public function isOverdue(): bool
    {
        return $this->deadline && $this->deadline->isPast() && $this->status !== TaskStatus::Done;
    }

    public function markInProgress(): void
    {
        $this->update(['status' => TaskStatus::InProgress, 'started_at' => $this->started_at ?? now()]);
    }

    public function markDone(): void
    {
        $this->update(['status' => TaskStatus::Done, 'completed_at' => now()]);

        // When VIDEO is done and has a link -> auto-assign to all platform heads
        if ($this->type === TaskType::Video && $this->video_drive_url) {
            $this->autoAssignToPlatformHeads();
        }

        $this->project->checkCompletion();
    }

    /**
     * When video maker pastes CapCut link -> auto-create tasks for
     * each platform head based on channel's target_platforms setting.
     */
    private function autoAssignToPlatformHeads(): void
    {
        $project = $this->project;
        $channel = $this->channel;

        // Which platforms does this channel post to?
        $targetPlatforms = $channel->target_platforms ?? [];
        if (empty($targetPlatforms)) {
            // Fallback: use all active platforms
            $targetPlatforms = Platform::active()->pluck('slug')->toArray();
        }

        foreach ($targetPlatforms as $platformSlug) {
            // Skip if already assigned
            if (Task::where('project_id', $project->id)->where('type', $platformSlug)->exists()) {
                continue;
            }

            // Find user who has upload permission for this platform
            $permission = 'upload_' . $platformSlug;
            $head = User::whereNotNull('custom_role_id')
                ->whereHas('customRole', function ($q) use ($permission) {
                    $q->where('is_active', true)
                      ->whereJsonContains('permissions', $permission);
                })
                ->first();

            if (! $head) continue;

            Task::create([
                'project_id'      => $project->id,
                'channel_id'      => $channel->id,
                'type'            => $platformSlug,
                'status'          => TaskStatus::Pending->value,
                'assigned_to'     => $head->id,
                'assigned_by'     => $this->assigned_by,
                'deadline'        => today()->endOfDay(),
                'video_drive_url' => $this->video_drive_url, // pass the link to platform heads
            ]);
        }
    }
}