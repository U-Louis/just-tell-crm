<?php

namespace App\Infrastructure\Voice;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class WhisperClient
{
    public function transcribe(string $bytes, string $filename): string
    {
        $response = Http::withToken((string) config('voice.whisper_token'))
            ->attach('file', $bytes, $filename)
            ->post(rtrim((string) config('voice.whisper_url'), '/').'/v1/transcribe');

        if (! $response->successful()) {
            throw new RuntimeException('Whisper refused the clip.');
        }

        $text = $response->json('text');
        if (! is_string($text)) {
            throw new RuntimeException('Whisper returned no text.');
        }

        return $text;
    }
}
