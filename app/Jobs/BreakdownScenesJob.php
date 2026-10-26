<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\PipelineStatus;
use App\Enums\SceneStatus;
use App\Models\Scene;
use App\Models\ScriptVersion;
use App\Models\VideoProject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BreakdownScenesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public VideoProject $videoProject,
    ) {
        $this->queue = 'scripts';
    }

    public function handle(): void
    {
        Log::info('Breaking down script into scenes', ['project' => $this->videoProject->id]);

        $script = $this->videoProject->approvedScript();

        if (! $script) {
            Log::warning('No approved script found', ['project' => $this->videoProject->id]);
            return;
        }

        $scenesData = $script->getScenes();

        if (empty($scenesData)) {
            // If the script has no pre-structured scenes, create basic ones
            $scenesData = $this->breakdownFromPlainText($script);
        }

        // Clear existing scenes
        $this->videoProject->scenes()->delete();

        foreach ($scenesData as $index => $sceneData) {
            Scene::create([
                'video_project_id' => $this->videoProject->id,
                'order'            => $index + 1,
                'narration_text'   => $sceneData['narration'] ?? $sceneData['narration_text'] ?? '',
                'visual_prompt'    => $sceneData['visual_prompt'] ?? '',
                'on_screen_text'   => $sceneData['on_screen_text'] ?? null,
                'duration_seconds' => $sceneData['duration'] ?? $sceneData['duration_seconds'] ?? 5,
                'status'           => SceneStatus::Pending,
                'flow_prompt'      => null, // Generated on demand
            ]);
        }

        $this->videoProject->update(['status' => PipelineStatus::ScenesCreated]);

        Log::info('Scenes created', [
            'project' => $this->videoProject->id,
            'count'   => count($scenesData),
        ]);
    }

    private function breakdownFromPlainText(ScriptVersion $script): array
    {
        $text = $script->plain_text ?? '';
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $duration = $this->videoProject->target_duration;
        $perScene = max(2, intdiv(count($sentences), 4));
        $chunks = array_chunk($sentences, $perScene);
        $sceneDuration = round($duration / count($chunks), 2);

        return array_map(function ($chunk, $i) use ($sceneDuration) {
            $narration = implode(' ', $chunk);
            return [
                'narration'      => $narration,
                'visual_prompt'  => 'Cinematic footage matching: ' . substr($narration, 0, 100),
                'on_screen_text' => $i === 0 ? strtoupper(substr($narration, 0, 40)) : null,
                'duration'       => $sceneDuration,
            ];
        }, $chunks, array_keys($chunks));
    }
}