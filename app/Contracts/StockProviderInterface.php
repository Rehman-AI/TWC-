<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Support\Collection;

interface StockProviderInterface
{
    /**
     * Search for stock footage/images.
     *
     * @return Collection<int, array{
     *     id: string,
     *     url: string,
     *     preview_url: string,
     *     download_url: string,
     *     width: int,
     *     height: int,
     *     duration: ?float,
     *     type: string (video|image),
     *     source: string,
     *     photographer: ?string,
     *     license: string,
     * }>
     */
    public function search(string $query, string $orientation = 'portrait', ?int $minDuration = null, int $perPage = 10): Collection;

    /** Download a stock file to a local path. */
    public function download(string $downloadUrl, string $localPath): bool;

    /** Provider identifier. */
    public function name(): string;
}