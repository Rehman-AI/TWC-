<?php

declare(strict_types=1);

namespace App\Contracts;

interface ScriptGeneratorInterface
{
    /**
     * Generate a structured script for a video.
     *
     * @param array $params {
     *     topic: string,
     *     niche: string,
     *     language: string,
     *     target_duration: int (seconds),
     *     style_guide: ?array,
     *     banned_words: ?array,
     *     tone: ?string,
     * }
     * @return array {
     *     hook: string,
     *     body: string,
     *     cta: string,
     *     loop_ending: string,
     *     scenes: array<{narration: string, visual_prompt: string, on_screen_text: ?string, duration: float}>,
     *     plain_text: string,
     * }
     */
    public function generate(array $params): array;

    /** Generate hook variants for A/B testing. */
    public function generateHookVariants(string $script, int $count = 5): array;

    /** Adapt/localize a script to a different language (not literal translation). */
    public function localize(string $script, string $sourceLanguage, string $targetLanguage, ?array $styleGuide = null): array;
}