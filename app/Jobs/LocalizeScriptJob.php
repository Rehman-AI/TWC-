<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\ScriptGeneratorInterface;
use App\Enums\PipelineStatus;
use App\Enums\ScriptStatus;
use App\Models\Channel;
use App\Models\ScriptVersion;
use App\Models\VideoProject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LocalizeScriptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public VideoProject $sourceProject,
        public Channel $targetChannel,
    ) {
        $this->queue = 'scripts';
    }

    public function handle(ScriptGeneratorInterface $generator): void
    {
        Log::info('Localizing script', [
            'source'  => $this->sourceProject->id,
            'target'  => $this->targetChannel->id,
            'from'    => $this->sourceProject->language,
            'to'      => $this->targetChannel->language,
        ]);

        $approvedScript = $this->sourceProject->approvedScript();
        if (! $approvedScript) {
            Log::warning('No approved script to localize', ['project' => $this->sourceProject->id]);
            return;
        }

        // Create a new project in the target channel
        $newProject = VideoProject::create([
            'channel_id'      => $this->targetChannel->id,
            'title'           => $this->sourceProject->title . ' (' . strtoupper($this->targetChannel->language) . ')',
            'slug'            => Str::slug($this->sourceProject->title) . '-' . $this->targetChannel->language . '-' . Str::random(4),
            'status'          => PipelineStatus::Scripting,
            'language'        => $this->targetChannel->language,
            'target_duration' => $this->sourceProject->target_duration,
            'priority'        => $this->sourceProject->priority,
            'notes'           => "Localized from: {$this->sourceProject->title} (Project #{$this->sourceProject->id})",
            'metadata'        => ['source_project_id' => $this->sourceProject->id],
        ]);

        // Generate localized script
        $localizedData = $generator->localize(
            $approvedScript->plain_text,
            $this->sourceProject->language,
            $this->targetChannel->language,
            $this->targetChannel->style_guide,
        );

        ScriptVersion::create([
            'video_project_id' => $newProject->id,
            'content'          => [
                'hook'        => $localizedData['hook'],
                'body'        => $localizedData['body'],
                'cta'         => $localizedData['cta'],
                'loop_ending' => $localizedData['loop_ending'],
                'scenes'      => $localizedData['scenes'] ?? [],
            ],
            'plain_text' => $localizedData['plain_text'],
            'status'     => ScriptStatus::Draft,
        ]);

        Log::info('Script localized', [
            'source'     => $this->sourceProject->id,
            'new_project' => $newProject->id,
        ]);
    }
}