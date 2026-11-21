<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VoiceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('voice.token');
        $presented = $request->bearerToken();
        if (! is_string($expected) || $expected === '' || ! is_string($presented) || ! hash_equals($expected, $presented)) {
            return response()->json([
                'code' => 'voice.token.required',
                'feature_id' => 'F38',
            ], 401);
        }

        if ($request->is('voice/tools/*')) {
            $runId = $request->header('X-Voice-Run');
            if (! is_string($runId) || ! ctype_digit($runId)) {
                return response()->json([
                    'code' => 'voice.run.required',
                    'feature_id' => 'F38',
                ], 401);
            }
        }

        return $next($request);
    }
}
