<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\StorageProviderInterface;
use App\Models\VideoProject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateDriveFoldersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    private const SUBFOLDERS = [
        '01_Script',
        '02_Scenes',
        '03_Flow_Clips',
        '04_Voiceover',
        '05_Captions',
        '06_Music',
        '07_Edit_Pack',
        '08_Final_Export',
        '09_QA_Reports',
        '10_SEO',
        '11_Publish',
    ];

    public function __construct(
        public VideoProject $videoProject,
    ) {
        $this->queue = 'system';
    }

    public function handle(StorageProviderInterface $storage): void
    {
        $channel = $this->videoProject->channel;

        Log::info('Creating Drive folders', [
            'project' => $this->videoProject->id,
            'channel' => $channel->name,
        ]);

        $rootFolderId = config('twc.google_drive.root_folder_id');

        // Create channel folder if needed
        $channelFolderId = $storage->createFolder($channel->name, $rootFolderId);

        // Create project folder
        $projectFolderName = $this->videoProject->id . '_' . $this->videoProject->slug;
        $projectFolderId = $storage->createFolder($projectFolderName, $channelFolderId);

        // Create all subfolders
        foreach (self::SUBFOLDERS as $subfolder) {
            $storage->createFolder($subfolder, $projectFolderId);
        }

        // Store the root project folder ID
        $this->videoProject->update(['drive_folder_id' => $projectFolderId]);

        Log::info('Drive folders created', [
            'project'   => $this->videoProject->id,
            'folder_id' => $projectFolderId,
        ]);
    }
}