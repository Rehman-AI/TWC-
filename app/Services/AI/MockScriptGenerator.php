<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Contracts\ScriptGeneratorInterface;

class MockScriptGenerator implements ScriptGeneratorInterface
{
    public function generate(array $params): array
    {
        $topic = $params['topic'] ?? 'Amazing Topic';
        $duration = $params['target_duration'] ?? 60;
        $sceneCount = max(2, intdiv($duration, 15));

        $hook = "Did you know this incredible fact about {$topic}? Stay till the end!";
        $body = "Here's what makes {$topic} so fascinating. First, it completely changes how we think about the world. Second, top experts are calling it a game-changer. And third, it could affect YOU directly.";
        $cta = "Follow for more mind-blowing facts like this!";
        $loopEnding = "And that's the craziest part about {$topic}...";

        $scenes = [];
        $sceneDuration = round($duration / $sceneCount, 2);
        $narrations = [
            $hook,
            ...array_fill(0, $sceneCount - 2, "This is why {$topic} matters more than you think."),
            $cta,
        ];

        for ($i = 0; $i < $sceneCount; $i++) {
            $scenes[] = [
                'narration'      => $narrations[$i] ?? "Scene " . ($i + 1) . " about {$topic}.",
                'visual_prompt'  => "Cinematic footage related to {$topic}, scene " . ($i + 1) . ". Dynamic angles, vibrant colors, 9:16 vertical.",
                'on_screen_text' => $i === 0 ? strtoupper($topic) : null,
                'duration'       => $sceneDuration,
            ];
        }

        $plainText = $hook . ' ' . $body . ' ' . $cta . ' ' . $loopEnding;

        return [
            'hook'        => $hook,
            'body'        => $body,
            'cta'         => $cta,
            'loop_ending' => $loopEnding,
            'scenes'      => $scenes,
            'plain_text'  => $plainText,
        ];
    }

    public function generateHookVariants(string $script, int $count = 5): array
    {
        $variants = [];
        $prefixes = ['Did you know', 'You won''t believe', 'This is why', 'Wait until you see', 'Nobody talks about'];
        for ($i = 0; $i < $count; $i++) {
            $variants[] = $prefixes[$i % count($prefixes)] . '... ' . substr($script, 0, 80);
        }
        return $variants;
    }

    public function localize(string $script, string $sourceLanguage, string $targetLanguage, ?array $styleGuide = null): array
    {
        return [
            'hook'        => "[{$targetLanguage}] Translated hook from {$sourceLanguage}",
            'body'        => "[{$targetLanguage}] " . $script,
            'cta'         => "[{$targetLanguage}] Follow for more!",
            'loop_ending' => "[{$targetLanguage}] And that was the craziest part...",
            'scenes'      => [],
            'plain_text'  => "[{$targetLanguage}] " . $script,
        ];
    }
}