<?php

namespace App\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class CrmLog
{
    public static function write(Request $request, ?string $featureId, string $outcome, ?string $code, ?int $actorId = null): void
    {
        if ($actorId === null && $request->hasSession()) {
            $sessionActor = $request->session()->get('acting_user_id');
            $actorId = is_numeric($sessionActor) ? (int) $sessionActor : null;
        }

        Log::info('crm.action', [
            'feature_id' => $featureId,
            'correlation_id' => $request->attributes->get('correlation_id'),
            'actor_id' => $actorId,
            'outcome' => $outcome,
            'code' => $code,
        ]);
    }
}
