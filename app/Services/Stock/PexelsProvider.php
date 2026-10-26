<?php

declare(strict_types=1);

namespace App\Services\Stock;

use App\Contracts\StockProviderInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PexelsProvider implements StockProviderInterface
{
    private string $apiKey;
    private const BASE_URL = 'https://api.pexels.com';

    public function __construct()
    {
        $this->apiKey = config('services.pexels.api_key', '');
    }

    public function search(string $query, string $orientation = 'portrait', ?int $minDuration = null, int $perPage = 10): Collection
    {
        if (empty($this->apiKey)) {
            Log::warning('Pexels API key not configured, returning empty results');
            return collect();
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->get(self::BASE_URL . '/videos/search', [
                'query'       => $query,
                'orientation' => $orientation,
                'per_page'    => $perPage,
                'size'        => 'medium',
            ]);

            if (! $response->successful()) {
                Log::error('Pexels API error', ['status' => $response->status(), 'body' => $response->body()]);
                return collect();
            }

            $data = $response->json();

            return collect($data['videos'] ?? [])->map(function ($video) {
                // Find the best quality portrait file
                $file = collect($video['video_files'] ?? [])
                    ->filter(fn ($f) => ($f['width'] ?? 0) <= 1080)
                    ->sortByDesc('height')
                    ->first();

                return [
                    'id'           => (string) $video['id'],
                    'url'          => $video['url'] ?? '',
                    'preview_url'  => $video['image'] ?? '',
                    'download_url' => $file['link'] ?? '',
                    'width'        => $file['width'] ?? $video['width'] ?? 1080,
                    'height'       => $file['height'] ?? $video['height'] ?? 1920,
                    'duration'     => (float) ($video['duration'] ?? 0),
                    'type'         => 'video',
                    'source'       => 'pexels',
                    'photographer' => $video['user']['name'] ?? null,
                    'license'      => 'pexels',
                ];
            });

        } catch (\Throwable $e) {
            Log::error('Pexels search failed', ['error' => $e->getMessage()]);
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
            Log::error('Pexels download failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function name(): string
    {
        return 'pexels';
    }
}