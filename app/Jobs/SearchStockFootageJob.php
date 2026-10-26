<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\StockProviderInterface;
use App\Models\Scene;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SearchStockFootageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Scene $scene,
    ) {
        $this->queue = 'media';
    }

    public function handle(StockProviderInterface $stock): void
    {
        Log::info('Searching stock footage', [
            'scene'  => $this->scene->id,
            'prompt' => substr($this->scene->visual_prompt, 0, 100),
        ]);

        // Extract key terms from the visual prompt
        $query = $this->extractSearchQuery($this->scene->visual_prompt);
        $duration = (int) ceil((float) $this->scene->duration_seconds);

        $results = $stock->search($query, 'portrait', $duration, 5);

        // Store results in scene metadata
        $this->scene->update([
            'metadata' => array_merge($this->scene->metadata ?? [], [
                'stock_results'   => $results->toArray(),
                'stock_query'     => $query,
                'stock_provider'  => $stock->name(),
                'searched_at'     => now()->toIso8601String(),
            ]),
        ]);

        Log::info('Stock footage search complete', [
            'scene'   => $this->scene->id,
            'results' => $results->count(),
        ]);
    }

    private function extractSearchQuery(string $prompt): string
    {
        // Strip common filler words to get better search results
        $stripped = preg_replace('/\b(cinematic|footage|shot|close-up|dramatic|high quality|vertical|9:16|smooth|motion|suitable|for|youtube|shorts|related|to|of|the|and|a|an|in|on|with|from)\b/i', '', $prompt);
        $stripped = preg_replace('/\s+/', ' ', trim($stripped));

        // Limit to first 5 meaningful words
        $words = array_filter(explode(' ', $stripped), fn ($w) => strlen($w) > 2);
        return implode(' ', array_slice($words, 0, 5));
    }
}