<?php

namespace App\Jobs;

use App\Domain\Crm\CrmRepository;
use App\Infrastructure\Persistence\Crm\VoiceRunStore;
use App\Infrastructure\Voice\VoiceAgentRunner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

final class RunVoiceInstruction implements ShouldQueue
{
    use Queueable;

    public int $timeout = 170;

    public function __construct(public int $runId) {}

    public function handle(VoiceRunStore $runs, VoiceAgentRunner $runner, CrmRepository $crm): void
    {
        $run = $runs->find($this->runId);
        if ($run === null || $run->status === 'finished') {
            return;
        }

        $runs->markRunning($run->id);
        $user = $crm->findUser($run->actingUserId);

        try {
            $result = $runner->run($run, $user === null ? 'unknown' : $user->name);
            $runs->finish($run->id, $result);
        } catch (Throwable $exception) {
            $runs->fail($run->id, $exception->getMessage());
        }
    }
}
