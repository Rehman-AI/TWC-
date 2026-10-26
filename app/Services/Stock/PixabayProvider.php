<?php

declare(strict_types=1);

namespace App\Services\Stock;

use App\Contracts\StockProviderInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PixabayProvider implements StockProviderInterface
{
    private string $apiKey;
    private const BASE_URL = 'https://pixabay.com/api/videos';

    public function __construct()
    {
        $this->apiKey = config('services.pixabay.api_key', '');
    }

    public function search(string $query, string $orientation = 'portrait', ?int $minDuration = null, int $perPage = 10): Collection
    {
        if (empty($this->apiKey)) {
            Log::warning('Pixabay API key not configured, returning empty results');
            return collect();
        }

        try {
            $response = Http::get(self::BASE_URL, [
                'key'      => $this->apiKey,
                'q'        => $query,
                'per_page' => $perPage,
            ]);

            if (! $response->successful()) {
                Log::error('Pixabay API error', ['status' => $response->status()]);
                return collect();
            }

            $data = $response->json();

            return collect($data['hits'] ?? [])->map(function ($video) {
                $medium = $video['videos']['medium'] ?? $video['videos']['small'] ?? [];

                return [
                    'id'           => (string) $video['id'],
                    'url'          => $video['pageURL'] ?? '',
                    'preview_url'  => "https://i.vimeocdn.com/video/{$video['picture_id']}_640x360.jpg",
                    'download_url' => $medium['url'] ?? '',
                    'width'        => $medium['width'] ?? 1280,
                    'height'       => $medium['height'] ?? 720,
                    'duration'     => (float) ($video['duration'] ?? 0),
                    'type'         => 'video',
                    'source'       => 'pixabay',
                    'photographer' => $video['user'] ?? null,
                    'license'      => 'pixabay',
                ];
            });

        } catch (\Throwable $e) {
            Log::error('Pixabay search failed', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    public function download(string $downloadUrl, string $localPath): bool
    {
        try {
            $response = Http::timeout(120)->get($downloadUrl);
            if ($response->successful()) {
                $dir = dirname($localPath);
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                file_put_contents($localPath, $response->body());
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            Log::error('Pixabay download failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function name(): string
    {
        return 'pixabay';
    }
}