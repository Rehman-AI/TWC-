<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\TTSProviderInterface;
use App\Enums\AssetType;
use App\Models\Asset;
use App\Models\Scene;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateTTSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public Scene $scene,
        public string $voice = 'en-US-ChristopherNeural',
    ) {
        $this->queue = 'media';
    }

    public function handle(TTSProviderInterface $tts): void
    {
        if (empty($this->scene->narration_text)) {
            Log::warning('Scene has no narration text', ['scene' => $this->scene->id]);
            return;
        }

        $project = $this->scene->videoProject;
        $language = $project->language ?? 'en';

        Log::info('Generating TTS', [
            'scene'    => $this->scene->id,
            'project'  => $project->id,
            'voice'    => $this->voice,
            'language' => $language,
        ]);

        $result = $tts->generate(
            $this->scene->narration_text,
            $this->voice,
            $language,
        );

        // Track the asset
        Asset::updateOrCreate(
            [
                'video_project_id' => $project->id,
                'scene_id'         => $this->scene->id,
                'type'             => AssetType::Voiceover,
            ],
            [
                'filename'   => basename($result['path']),
                'local_path' => $result['path'],
                'mime_type'  => $result['mime_type'],
                'size_bytes' => $result['size_bytes'],
                'metadata'   => [
                    'voice'             => $this->voice,
                    'duration_seconds'  => $result['duration_seconds'],
                    'provider'          => $tts->name(),
                ],
            ]
        );

        // After TTS, auto-queue caption generation
        GenerateCaptionsJob::dispatch($this->scene, $result['path']);

        Log::info('TTS generated, caption job dispatched', ['scene' => $this->scene->id]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('TTS generation failed', [
            'scene' => $this->scene->id,
            'error' => $exception->getMessage(),
        ]);
        $this->scene->markFailed();
    }
}