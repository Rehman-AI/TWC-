<?php

declare(strict_types=1);

namespace App\Contracts;

interface CaptionProviderInterface
{
    /**
     * Transcribe audio and generate word-level captions.
     *
     * @return array {
     *     srt: string,
     *     words: array<{word: string, start: float, end: float, confidence: float}>,
     *     full_text: string,
     *     language: string,
     * }
     */
    public function transcribe(string $audioPath, string $language = 'en'): array;

    /** Provider identifier. */
    public function name(): string;
}