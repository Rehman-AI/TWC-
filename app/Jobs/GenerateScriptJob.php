<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\ScriptGeneratorInterface;
use App\Enums\PipelineStatus;
use App\Enums\ScriptStatus;
use App\Models\ScriptVersion;
use App\Models\VideoProject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateScriptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public VideoProject $videoProject,
        public array $params = [],
    ) {
        $this->queue = 'scripts';
    }

    public function handle(ScriptGeneratorInterface $generator): void
    {
        Log::info('Generating script', ['project' => $this->videoProject->id]);

        $channel = $this->videoProject->channel;

        $scriptData = $generator->generate(array_merge([
            'topic'           => $this->videoProject->title,
            'niche'           => $channel->niche ?? '',
            'language'        => $this->videoProject->language,
            'target_duration' => $this->videoProject->target_duration,
            'style_guide'     => $channel->style_guide,
            'banned_words'    => $channel->banned_words,
        ], $this->params));

        ScriptVersion::create([
            'video_project_id' => $this->videoProject->id,
            'content'          => [
                'hook'        => $scriptData['hook'],
                'body'        => $scriptData['body'],
                'cta'         => $scriptData['cta'],
                'loop_ending' => $scriptData['loop_ending'],
                'scenes'      => $scriptData['scenes'],
            ],
            'plain_text'       => $scriptData['plain_text'],
            'status'           => ScriptStatus::Draft,
        ]);

        $this->videoProject->update(['status' => PipelineStatus::Scripting]);

        Log::info('Script generated', ['project' => $this->videoProject->id]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Script generation failed', [
            'project' => $this->videoProject->id,
            'error'   => $exception->getMessage(),
        ]);
    }
}