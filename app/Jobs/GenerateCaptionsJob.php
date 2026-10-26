<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\AssetType;
use App\Models\Asset;
use App\Models\Scene;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateCaptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public Scene $scene,
        public string $audioPath,
    ) {
        $this->queue = 'media';
    }

    public function handle(): void
    {
        $project = $this->scene->videoProject;

        Log::info('Generating captions', [
            'scene'   => $this->scene->id,
            'project' => $project->id,
        ]);

        // For now, generate SRT from narration text + estimated timing
        // When Whisper is wired up, this will use CaptionProviderInterface
        $srtContent = $this->generateSrtFromNarration();

        $srtPath = storage_path("app/captions/scene_{$this->scene->id}.srt");
        $dir = dirname($srtPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($srtPath, $srtContent);

        Asset::updateOrCreate(
            [
                'video_project_id' => $project->id,
                'scene_id'         => $this->scene->id,
                'type'             => AssetType::Caption,
            ],
            [
                'filename'   => "scene_{$this->scene->id}.srt",
                'local_path' => $srtPath,
                'mime_type'  => 'application/x-subrip',
                'size_bytes' => filesize($srtPath),
                'metadata'   => ['method' => 'estimated'],
            ]
        );

        Log::info('Captions generated', ['scene' => $this->scene->id]);
    }

    private function generateSrtFromNarration(): string
    {
        $text = $this->scene->narration_text;
        $duration = (float) $this->scene->duration_seconds;
        $words = explode(' ', $text);
        $totalWords = count($words);

        if ($totalWords === 0) {
            return '';
        }

        $wordsPerChunk = max(1, min(8, intdiv($totalWords, 3)));
        $chunks = array_chunk($words, $wordsPerChunk);
        $timePerChunk = $duration / count($chunks);
        $srt = '';

        foreach ($chunks as $i => $chunk) {
            $start = $i * $timePerChunk;
            $end = min(($i + 1) * $timePerChunk, $duration);

            $srt .= ($i + 1) . "\n";
            $srt .= $this->formatSrtTime($start) . ' --> ' . $this->formatSrtTime($end) . "\n";
            $srt .= implode(' ', $chunk) . "\n\n";
        }

        return $srt;
    }

    private function formatSrtTime(float $seconds): string
    {
        $hours = intdiv((int) $seconds, 3600);
        $minutes = intdiv((int) $seconds % 3600, 60);
        $secs = (int) $seconds % 60;
        $ms = (int) (($seconds - floor($seconds)) * 1000);

        return sprintf('%02d:%02d:%02d,%03d', $hours, $minutes, $secs, $ms);
    }
}