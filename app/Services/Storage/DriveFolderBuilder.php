<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Contracts\StorageProviderInterface;
use App\Models\Channel;
use App\Models\VideoProject;
use Illuminate\Support\Facades\Log;

class DriveFolderBuilder
{
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
        private StorageProviderInterface $storage,
    ) {}

    /**
     * Build the full Drive folder structure for a video project.
     *
     * @return array{project_folder_id: string, subfolders: array<string, string>}
     */
    public function buildForProject(VideoProject $project): array
    {
        $channel = $project->channel;
        $rootFolderId = config('twc.google_drive.root_folder_id');

        Log::info('Building Drive folder structure', [
            'project' => $project->id,
            'channel' => $channel->name,
        ]);

        // Channel folder
        $channelFolderId = $this->storage->createFolder($channel->name, $rootFolderId);

        // Date-based folder (YYYY-MM)
        $dateFolderId = $this->storage->createFolder(
            now()->format('Y-m'),
            $channelFolderId
        );

        // Project folder
        $projectFolderName = $project->id . '_' . $project->slug;
        $projectFolderId = $this->storage->createFolder($projectFolderName, $dateFolderId);

        // Subfolders
        $subfolders = [];
        foreach (self::SUBFOLDERS as $name) {
            $subfolders[$name] = $this->storage->createFolder($name, $projectFolderId);
        }

        // Update the project with the root folder ID
        $project->update(['drive_folder_id' => $projectFolderId]);

        return [
            'project_folder_id' => $projectFolderId,
            'subfolders'        => $subfolders,
        ];
    }

    /**
     * Build channel-level folder only.
     */
    public function buildForChannel(Channel $channel): string
    {
        $rootFolderId = config('twc.google_drive.root_folder_id');
        return $this->storage->createFolder($channel->name, $rootFolderId);
    }
}