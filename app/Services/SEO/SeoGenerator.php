<?php

declare(strict_types=1);

namespace App\Services\SEO;

use App\Models\SeoMetadata;
use App\Models\VideoProject;

class SeoGenerator
{
    /**
     * Generate SEO metadata for a video project.
     */
    public function generate(VideoProject $project): SeoMetadata
    {
        $channel = $project->channel;
        $script = $project->currentScript;
        $plainText = $script?->plain_text ?? $project->title;

        // Generate title (max 100 chars, include hook)
        $seoTitle = $this->generateTitle($project->title, $script?->getHook());

        // Generate description
        $seoDescription = $this->generateDescription($plainText, $channel->niche ?? '', $channel->name);

        // Generate tags
        $tags = $this->generateTags($project->title, $channel->niche ?? '', $plainText);

        // Generate hashtags
        $hashtags = $this->generateHashtags($project->title, $channel->niche ?? '');

        // Thumbnail text suggestion
        $thumbnailText = $this->generateThumbnailText($project->title, $script?->getHook());

        // Score calculation
        $scoreBreakdown = $this->calculateScore($seoTitle, $seoDescription, $tags, $hashtags, $thumbnailText, $plainText);
        $totalScore = (int) round(collect($scoreBreakdown)->avg('score'));

        // Suggestions
        $suggestions = $this->generateSuggestions($scoreBreakdown);

        return SeoMetadata::updateOrCreate(
            ['video_project_id' => $project->id],
            [
                'seo_title'       => $seoTitle,
                'seo_description' => $seoDescription,
                'tags'            => $tags,
                'hashtags'        => $hashtags,
                'thumbnail_text'  => $thumbnailText,
                'seo_score'       => $totalScore,
                'score_breakdown' => $scoreBreakdown,
                'suggestions'     => $suggestions,
            ]
        );
    }

    private function generateTitle(string $title, ?string $hook): string
    {
        if ($hook && strlen($hook) <= 100) {
            return rtrim($hook, '.!? ') . ' #shorts';
        }
        return substr($title, 0, 90) . ' #shorts';
    }

    private function generateDescription(string $plainText, string $niche, string $channelName): string
    {
        $firstSentence = strtok($plainText, '.!?') . '.';
        $desc = $firstSentence . "\n\n";
        $desc .= "Follow @{$channelName} for more {$niche} content!\n\n";
        $desc .= "#shorts #" . strtolower(str_replace([' ', '&', ','], '', $niche));
        return $desc;
    }

    private function generateTags(string $title, string $niche, string $text): array
    {
        $tags = ['shorts', 'youtube shorts'];

        // Extract keywords from title
        $titleWords = array_filter(
            explode(' ', strtolower($title)),
            fn ($w) => strlen($w) > 3 && !in_array($w, ['this', 'that', 'with', 'from', 'your', 'will', 'have', 'been'])
        );
        $tags = array_merge($tags, array_values($titleWords));

        // Add niche tags
        if ($niche) {
            $tags[] = strtolower($niche);
            $nicheWords = explode(' ', strtolower($niche));
            $tags = array_merge($tags, array_filter($nicheWords, fn ($w) => strlen($w) > 2));
        }

        // Add trending-style tags
        $tags = array_merge($tags, ['viral', 'trending', '2025']);

        return array_values(array_unique(array_slice($tags, 0, 30)));
    }

    private function generateHashtags(string $title, string $niche): array
    {
        $hashtags = ['#shorts', '#viral', '#trending'];

        if ($niche) {
            $hashtags[] = '#' . strtolower(str_replace([' ', '&', ','], '', $niche));
        }

        // Extract key words from title for hashtags
        $words = array_filter(
            explode(' ', strtolower($title)),
            fn ($w) => strlen($w) > 4
        );
        foreach (array_slice($words, 0, 3) as $word) {
            $hashtags[] = '#' . preg_replace('/[^a-z0-9]/', '', $word);
        }

        return array_values(array_unique(array_slice($hashtags, 0, 10)));
    }

    private function generateThumbnailText(string $title, ?string $hook): string
    {
        // Thumbnail text should be 3-5 words, high impact
        $source = $hook ?? $title;
        $words = explode(' ', $source);
        $keyWords = array_filter($words, fn ($w) => strlen($w) > 3 && strtoupper($w) !== $w);
        return strtoupper(implode(' ', array_slice(array_values($keyWords), 0, 4)));
    }

    private function calculateScore(string $title, string $description, array $tags, array $hashtags, string $thumbnailText, string $text): array
    {
        return [
            'title' => [
                'score' => $this->scoreTitle($title),
                'label' => 'Title Quality',
            ],
            'description' => [
                'score' => $this->scoreDescription($description),
                'label' => 'Description Quality',
            ],
            'tags' => [
                'score' => min(100, count($tags) * 10),
                'label' => 'Tag Coverage',
            ],
            'hashtags' => [
                'score' => min(100, count($hashtags) * 15),
                'label' => 'Hashtag Usage',
            ],
            'thumbnail' => [
                'score' => strlen($thumbnailText) > 5 ? 80 : 40,
                'label' => 'Thumbnail Text',
            ],
            'engagement' => [
                'score' => $this->scoreEngagement($text),
                'label' => 'Engagement Signals',
            ],
        ];
    }

    private function scoreTitle(string $title): int
    {
        $score = 50;
        if (strlen($title) >= 30 && strlen($title) <= 70) $score += 20;
        if (preg_match('/[!?]/', $title)) $score += 10;
        if (str_contains(strtolower($title), '#shorts')) $score += 10;
        if (preg_match('/\d/', $title)) $score += 10;
        return min(100, $score);
    }

    private function scoreDescription(string $desc): int
    {
        $score = 40;
        if (strlen($desc) >= 50) $score += 20;
        if (str_contains($desc, '#')) $score += 15;
        if (str_contains($desc, "\n")) $score += 10;
        if (preg_match('/@/', $desc)) $score += 15;
        return min(100, $score);
    }

    private function scoreEngagement(string $text): int
    {
        $score = 40;
        $lower = strtolower($text);
        if (str_contains($lower, 'follow')) $score += 15;
        if (str_contains($lower, 'comment')) $score += 15;
        if (str_contains($lower, 'save')) $score += 10;
        if (str_contains($lower, 'share')) $score += 10;
        if (preg_match('/\?/', $text)) $score += 10;
        return min(100, $score);
    }

    private function generateSuggestions(array $breakdown): array
    {
        $suggestions = [];
        foreach ($breakdown as $key => $item) {
            if ($item['score'] < 60) {
                $suggestions[] = match ($key) {
                    'title'       => 'Add numbers, power words, or emojis to your title for better CTR.',
                    'description' => 'Add a longer description with hashtags, @ mentions, and a CTA.',
                    'tags'        => 'Add more relevant tags (aim for 20-30).',
                    'hashtags'    => 'Add 5-10 hashtags including #shorts and niche-specific tags.',
                    'thumbnail'   => 'Create punchy thumbnail text (3-5 words, all caps).',
                    'engagement'  => 'Add engagement hooks: questions, follow CTAs, save prompts.',
                    default       => "Improve {$key} score.",
                };
            }
        }
        return $suggestions;
    }
}