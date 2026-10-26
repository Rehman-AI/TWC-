<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\StorageProviderInterface;
use App\Enums\AssetType;
use App\Models\Asset;
use App\Models\VideoProject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateEditPackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public VideoProject $videoProject,
    ) {
        $this->queue = 'media';
    }

    public function handle(StorageProviderInterface $storage): void
    {
        Log::info('Generating Edit Pack', ['project' => $this->videoProject->id]);

        $project = $this->videoProject->load(['channel', 'scenes', 'currentScript', 'assets']);
        $channel = $project->channel;
        $script = $project->currentScript;

        // Build the Edit Pack content
        $editPack = [
            'project' => [
                'id'              => $project->id,
                'title'           => $project->title,
                'language'        => $project->language,
                'target_duration' => $project->target_duration,
            ],
            'channel' => [
                'name'       => $channel->name,
                'niche'      => $channel->niche,
                'style_guide' => $channel->style_guide,
                'brand_kit'  => $channel->brand_kit,
            ],
            'script' => $script ? [
                'version'    => $script->version,
                'hook'       => $script->getHook(),
                'body'       => $script->getBody(),
                'cta'        => $script->getCta(),
                'loop_ending' => $script->getLoopEnding(),
                'plain_text' => $script->plain_text,
                'word_count' => $script->word_count,
            ] : null,
            'scenes' => $project->scenes->map(fn ($scene) => [
                'order'          => $scene->order,
                'narration'      => $scene->narration_text,
                'visual_prompt'  => $scene->visual_prompt,
                'flow_prompt'    => $scene->flow_prompt ?? $scene->generateFlowPrompt(),
                'on_screen_text' => $scene->on_screen_text,
                'duration'       => $scene->duration_seconds,
            ])->toArray(),
            'seo_draft' => [
                'title_suggestion' => $project->title,
                'hashtags'         => ['#shorts', '#' . strtolower(str_replace(' ', '', $channel->niche ?? 'content'))],
            ],
            'generated_at' => now()->toIso8601String(),
        ];

        // Save as JSON to local cache
        $localPath = storage_path("app/edit-packs/{$project->id}_edit_pack.json");
        $dir = dirname($localPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($localPath, json_encode($editPack, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Upload to Drive (07_Edit_Pack folder)
        $driveResult = $storage->uploadFile(
            $localPath,
            "edit_pack_{$project->slug}.json",
            $project->drive_folder_id,
            'application/json'
        );

        // Track the asset
        Asset::updateOrCreate(
            [
                'video_project_id' => $project->id,
                'type'             => AssetType::EditPack,
                'filename'         => "edit_pack_{$project->slug}.json",
            ],
            [
                'drive_file_id' => $driveResult['id'] ?? null,
                'drive_url'     => $driveResult['webViewLink'] ?? null,
                'local_path'    => $localPath,
                'mime_type'     => 'application/json',
                'size_bytes'    => filesize($localPath),
            ]
        );

        Log::info('Edit Pack generated', [
            'project' => $project->id,
            'scenes'  => $project->scenes->count(),
        ]);
    }
}