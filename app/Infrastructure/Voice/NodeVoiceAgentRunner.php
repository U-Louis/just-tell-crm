<?php

namespace App\Infrastructure\Voice;

use App\Domain\Crm\VoiceRun;
use RuntimeException;
use Symfony\Component\Process\Process;

final class NodeVoiceAgentRunner implements VoiceAgentRunner
{
    public function run(VoiceRun $run, string $actingUserName): string
    {
        $environment = getenv();
        if ($environment === false) {
            $environment = [];
        }

        $environment['CURSOR_API_KEY'] = (string) config('voice.cursor_key');
        $environment['CURSOR_MODEL'] = (string) config('voice.cursor_model');
        $environment['VOICE_TOKEN'] = (string) config('voice.token');
        $environment['VOICE_APP_URL'] = (string) config('voice.app_url');

        $process = new Process(
            ['node', '--experimental-strip-types', base_path('voice/run-agent.ts')],
            base_path(),
            $environment,
            json_encode([
                'runId' => $run->id,
                'transcript' => $run->transcript,
                'actingUserId' => $run->actingUserId,
                'actingUserName' => $actingUserName,
                'cwd' => base_path(),
            ], JSON_THROW_ON_ERROR),
            160,
        );
        $process->run();

        $payload = $this->payload($process->getOutput());
        if (! $process->isSuccessful() || ($payload['status'] ?? '') !== 'finished') {
            $message = is_string($payload['result'] ?? null) ? $payload['result'] : trim($process->getErrorOutput());
            throw new RuntimeException($message !== '' ? $message : 'The voice agent failed.');
        }

        return is_string($payload['result'] ?? null) ? $payload['result'] : '';
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $output): array
    {
        $lines = preg_split("/\r\n|\n|\r/", trim($output)) ?: [];
        foreach (array_reverse($lines) as $line) {
            $decoded = json_decode($line, true);
            if (is_array($decoded) && is_string($decoded['status'] ?? null)) {
                return $decoded;
            }
        }

        return [];
    }
}
