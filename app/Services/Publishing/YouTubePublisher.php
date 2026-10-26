<?php

declare(strict_types=1);

namespace App\Services\Publishing;

use App\Enums\AuditAction;
use App\Enums\PipelineStatus;
use App\Models\PlatformAccount;
use App\Models\PublishLog;
use App\Models\SeoMetadata;
use App\Models\VideoProject;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubePublisher
{
    private const UPLOAD_URL = 'https://www.googleapis.com/upload/youtube/v3/videos';
    private const API_URL = 'https://www.googleapis.com/youtube/v3/videos';

    /**
     * Publish a video to YouTube Shorts.
     */
    public function publish(VideoProject $project, PlatformAccount $account, ?string $localVideoPath = null, ?\DateTimeInterface $scheduledAt = null): PublishLog
    {
        $project->load(['channel', 'seoMetadata']);
        $seo = $project->seoMetadata;

        // Check if we have a valid token
        if ($account->isTokenExpired()) {
            return $this->createLog($project, $account, 'failed', null, null, 'OAuth token expired. Please reconnect.');
        }

        // Build metadata
        $metadata = $this->buildVideoMetadata($project, $seo, $scheduledAt);

        // Check if AI disclosure is needed
        $aiDisclosure = $project->assets()
            ->whereIn('type', ['ai_clip', 'voiceover'])
            ->exists();

        Log::info('Publishing to YouTube', [
            'project'       => $project->id,
            'account'       => $account->id,
            'ai_disclosure' => $aiDisclosure,
            'scheduled'     => $scheduledAt?->toIso8601String(),
        ]);

        try {
            if (! $localVideoPath || ! file_exists($localVideoPath)) {
                return $this->createLog($project, $account, 'failed', null, null, 'No video file found for upload.', $aiDisclosure);
            }

            // Upload via YouTube Data API v3
            $response = Http::withToken($account->access_token)
                ->attach('video', file_get_contents($localVideoPath), 'video.mp4')
                ->post(self::UPLOAD_URL . '?' . http_build_query([
                    'uploadType' => 'multipart',
                    'part'       => 'snippet,status',
                ]), [
                    'snippet' => $metadata['snippet'],
                    'status'  => $metadata['status'],
                ]);

            if (! $response->successful()) {
                throw new \RuntimeException('YouTube upload failed: ' . $response->body());
            }

            $videoData = $response->json();
            $videoId = $videoData['id'] ?? null;

            // If AI disclosure needed, set the label
            if ($aiDisclosure && $videoId) {
                $this->applyAiDisclosure($account, $videoId);
            }

            $publishLog = $this->createLog(
                $project,
                $account,
                $scheduledAt ? 'scheduled' : 'published',
                $videoId,
                $videoId ? "https://youtube.com/shorts/{$videoId}" : null,
                null,
                $aiDisclosure,
                $scheduledAt,
            );

            // Update project status
            $project->update(['status' => PipelineStatus::Published]);

            // Audit log
            app(AuditLogger::class)->log(
                AuditAction::VideoPublished,
                $project,
                [
                    'platform'      => 'youtube',
                    'video_id'      => $videoId,
                    'ai_disclosure'  => $aiDisclosure,
                ],
            );

            return $publishLog;

        } catch (\Throwable $e) {
            Log::error('YouTube publish failed', [
                'project' => $project->id,
                'error'   => $e->getMessage(),
            ]);

            return $this->createLog($project, $account, 'failed', null, null, $e->getMessage(), $aiDisclosure);
        }
    }

    private function buildVideoMetadata(VideoProject $project, ?SeoMetadata $seo, ?\DateTimeInterface $scheduledAt): array
    {
        return [
            'snippet' => [
                'title'       => $seo?->seo_title ?? $project->title,
                'description' => $seo?->seo_description ?? '',
                'tags'        => $seo?->tags ?? [],
                'categoryId'  => '22', // People & Blogs
            ],
            'status' => [
                'privacyStatus'          => $scheduledAt ? 'private' : 'public',
                'publishAt'              => $scheduledAt?->toIso8601String(),
                'selfDeclaredMadeForKids' => false,
                'madeForKids'            => false,
            ],
        ];
    }

    private function applyAiDisclosure(PlatformAccount $account, string $videoId): void
    {
        try {
            Http::withToken($account->access_token)
                ->put(self::API_URL . '?' . http_build_query(['part' => 'status']), [
                    'id' => $videoId,
                    'status' => [
                        'selfDeclaredMadeForKids' => false,
                    ],
                ]);

            Log::info('AI disclosure applied', ['video_id' => $videoId]);
        } catch (\Throwable $e) {
            Log::warning('Failed to apply AI disclosure', ['error' => $e->getMessage()]);
        }
    }

    private function createLog(
        VideoProject $project,
        PlatformAccount $account,
        string $status,
        ?string $videoId,
        ?string $url,
        ?string $error = null,
        bool $aiDisclosure = false,
        ?\DateTimeInterface $scheduledAt = null,
    ): PublishLog {
        return PublishLog::create([
            'video_project_id'     => $project->id,
            'platform_account_id'  => $account->id,
            'platform'             => $account->platform,
            'platform_video_id'    => $videoId,
            'platform_url'         => $url,
            'status'               => $status,
            'scheduled_at'         => $scheduledAt,
            'published_at'         => $status === 'published' ? now() : null,
            'ai_disclosure_applied' => $aiDisclosure,
            'error_message'        => $error,
        ]);
    }
}