<?php

namespace App\Http\Controllers\Crm;

use App\Http\ActingUser;
use App\Http\CrmLog;
use App\Infrastructure\Persistence\Crm\VoiceRunStore;
use App\Infrastructure\Voice\WhisperClient;
use App\Jobs\RunVoiceInstruction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class VoiceController
{
    public function store(Request $request, ActingUser $acting, WhisperClient $whisper, VoiceRunStore $runs): RedirectResponse
    {
        $actor = $acting->resolve($request);
        if ($actor === null) {
            return $this->refuse($request, 'user.required');
        }

        $clip = $request->file('clip');
        $size = $clip?->getSize();
        if ($clip === null || ! is_int($size) || $size < 1) {
            return $this->refuse($request, 'voice.clip.required');
        }

        $bytes = file_get_contents($clip->getRealPath());
        if ($bytes === false || $bytes === '') {
            return $this->refuse($request, 'voice.clip.required');
        }

        try {
            $transcript = trim($whisper->transcribe($bytes, $clip->getClientOriginalName() ?: 'clip.webm'));
        } catch (RuntimeException) {
            return $this->refuse($request, 'voice.transcribe.failed');
        }

        if ($transcript === '') {
            return $this->refuse($request, 'voice.transcript.empty');
        }

        $run = $runs->create($request->session()->getId(), $actor->id, $transcript);
        RunVoiceInstruction::dispatch($run->id);
        CrmLog::write($request, 'F38', 'success', null);

        return back();
    }

    private function refuse(Request $request, string $code): RedirectResponse
    {
        CrmLog::write($request, 'F38', 'refused', $code);

        return back()->with('error', [
            'code' => $code,
            'feature_id' => 'F38',
        ]);
    }
}
