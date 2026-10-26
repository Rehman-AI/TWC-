<?php

declare(strict_types=1);

namespace App\Contracts;

interface TTSProviderInterface
{
    /**
     * Generate speech audio from text.
     *
     * @return array { path: string, duration_seconds: float, mime_type: string, size_bytes: int }
     */
    public function generate(string $text, string $voice, string $language = 'en'): array;

    /** List available voices for this provider. */
    public function voices(string $language = 'en'): array;

    /** Provider identifier. */
    public function name(): string;
}