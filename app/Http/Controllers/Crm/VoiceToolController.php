<?php

namespace App\Http\Controllers\Crm;

use App\Http\Voice\VoiceCatalog;
use App\Http\Voice\VoiceTools;
use App\Infrastructure\Persistence\Crm\VoiceRunStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class VoiceToolController
{
    public function catalog(): JsonResponse
    {
        return response()->json([
            'tools' => VoiceCatalog::tools(),
        ]);
    }

    public function call(Request $request, string $tool, VoiceTools $tools, VoiceRunStore $runs): JsonResponse
    {
        $run = $runs->find((int) $request->header('X-Voice-Run'));
        if ($run === null) {
            return response()->json([
                'code' => 'voice.run.missing',
                'feature_id' => 'F38',
            ], 404);
        }

        $input = $request->json()->all();
        if (! is_array($input)) {
            $input = [];
        }

        return $tools->call($request, $run, $tool, $input);
    }
}
