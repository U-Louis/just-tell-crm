<?php

namespace App\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class CrmLog
{
    public static function write(Request $request, ?string $featureId, string $outcome, ?string $code): void
    {
        $actorId = null;
        if ($request->hasSession()) {
            $actorId = $request->session()->get('acting_user_id');
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
