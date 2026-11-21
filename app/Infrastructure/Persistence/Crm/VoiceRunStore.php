<?php

namespace App\Infrastructure\Persistence\Crm;

use App\Domain\Crm\VoiceRun;

final class VoiceRunStore
{
    public function create(string $sessionId, int $actingUserId, string $transcript): VoiceRun
    {
        $record = VoiceRunRecord::query()->create([
            'session_id' => $sessionId,
            'acting_user_id' => $actingUserId,
            'transcript' => $transcript,
            'status' => 'queued',
            'result' => null,
            'applied' => false,
        ]);

        return $this->map($record);
    }

    public function find(int $id): ?VoiceRun
    {
        $record = VoiceRunRecord::query()->find($id);

        return $record === null ? null : $this->map($record);
    }

    public function latestForSession(string $sessionId): ?VoiceRun
    {
        $record = VoiceRunRecord::query()
            ->where('session_id', $sessionId)
            ->orderByDesc('id')
            ->first();

        return $record === null ? null : $this->map($record);
    }

    public function markRunning(int $id): void
    {
        VoiceRunRecord::query()->whereKey($id)->update(['status' => 'running']);
    }

    public function finish(int $id, string $result): void
    {
        VoiceRunRecord::query()->whereKey($id)->update([
            'status' => 'finished',
            'result' => $result,
        ]);
    }

    public function fail(int $id, string $result): void
    {
        VoiceRunRecord::query()->whereKey($id)->update([
            'status' => 'failed',
            'result' => $result,
        ]);
    }

    public function setActingUser(int $id, int $userId): void
    {
        VoiceRunRecord::query()->whereKey($id)->update(['acting_user_id' => $userId]);
    }

    public function markApplied(int $id): void
    {
        VoiceRunRecord::query()->whereKey($id)->update(['applied' => true]);
    }

    private function map(VoiceRunRecord $record): VoiceRun
    {
        return new VoiceRun(
            $record->id,
            $record->session_id,
            $record->acting_user_id,
            $record->transcript,
            $record->status,
            $record->result,
            (bool) $record->applied,
        );
    }
}
