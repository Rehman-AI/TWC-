<?php

declare(strict_types=1);

namespace App\Services\TTS;

use App\Contracts\TTSProviderInterface;

class MockTTSProvider implements TTSProviderInterface
{
    public function generate(string $text, string $voice, string $language = 'en'): array
    {
        $wordCount = str_word_count($text);
        $duration = $wordCount / 2.5; // ~150 words per minute

        return [
            'path'             => storage_path('app/tts/mock_' . md5($text) . '.mp3'),
            'duration_seconds' => round($duration, 2),
            'mime_type'        => 'audio/mpeg',
            'size_bytes'       => $wordCount * 1024,
        ];
    }

    public function voices(string $language = 'en'): array
    {
        return [
            ['id' => 'en-US-ChristopherNeural', 'name' => 'Christopher', 'language' => 'en', 'gender' => 'male'],
            ['id' => 'en-US-JennyNeural',       'name' => 'Jenny',       'language' => 'en', 'gender' => 'female'],
            ['id' => 'en-GB-RyanNeural',         'name' => 'Ryan',        'language' => 'en', 'gender' => 'male'],
        ];
    }

    public function name(): string
    {
        return 'mock_tts';
    }
}