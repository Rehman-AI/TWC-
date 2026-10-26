<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SceneStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scene extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_project_id',
        'order',
        'narration_text',
        'visual_prompt',
        'on_screen_text',
        'duration_seconds',
        'status',
        'flow_prompt',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'order'            => 'integer',
            'duration_seconds' => 'decimal:2',
            'status'           => SceneStatus::class,
            'metadata'         => 'array',
        ];
    }

    // -- Relationships -----------------------------------------------

    public function videoProject(): BelongsTo
    {
        return $this->belongsTo(VideoProject::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    // -- Helpers ------------------------------------------------------

    public function isReady(): bool
    {
        return $this->status === SceneStatus::Ready;
    }

    public function markClipsRequested(): void
    {
        $this->update(['status' => SceneStatus::ClipsRequested]);
    }

    public function markClipsUploaded(): void
    {
        $this->update(['status' => SceneStatus::ClipsUploaded]);
    }

    public function markReady(): void
    {
        $this->update(['status' => SceneStatus::Ready]);
    }

    public function markFailed(): void
    {
        $this->update(['status' => SceneStatus::Failed]);
    }

    /** Generate a ready-to-use prompt for Google Flow */
    public function generateFlowPrompt(): string
    {
        $prompt = "Create a {$this->duration_seconds}-second vertical video clip (9:16 aspect ratio).\n\n";
        $prompt .= "Visual: {$this->visual_prompt}\n\n";

        if ($this->on_screen_text) {
            $prompt .= "On-screen text: {$this->on_screen_text}\n\n";
        }

        $prompt .= "Style: Cinematic, smooth motion, high quality, suitable for YouTube Shorts.";

        return $prompt;
    }
}