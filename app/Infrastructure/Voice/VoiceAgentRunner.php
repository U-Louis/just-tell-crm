<?php

namespace App\Infrastructure\Voice;

use App\Domain\Crm\VoiceRun;

interface VoiceAgentRunner
{
    public function run(VoiceRun $run, string $actingUserName): string;
}
