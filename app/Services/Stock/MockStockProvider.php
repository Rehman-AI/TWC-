<?php

declare(strict_types=1);

namespace App\Services\Stock;

use App\Contracts\StockProviderInterface;
use Illuminate\Support\Collection;

class MockStockProvider implements StockProviderInterface
{
    public function search(string $query, string $orientation = 'portrait', ?int $minDuration = null, int $perPage = 10): Collection
    {
        $results = [];
        for ($i = 1; $i <= min($perPage, 5); $i++) {
            $results[] = [
                'id'           => "mock_{$i}",
                'url'          => "https://www.pexels.com/video/mock-{$i}",
                'preview_url'  => "https://images.pexels.com/videos/mock-{$i}/preview.jpg",
                'download_url' => "https://videos.pexels.com/videos/mock-{$i}.mp4",
                'width'        => 1080,
                'height'       => 1920,
                'duration'     => rand(5, 30) * 1.0,
                'type'         => 'video',
                'source'       => 'mock_pexels',
                'photographer' => 'Mock Photographer',
                'license'      => 'pexels',
            ];
        }
        return collect($results);
    }

    public function download(string $downloadUrl, string $localPath): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'mock_stock';
    }
}