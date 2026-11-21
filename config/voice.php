<?php

return [

    'token' => env('VOICE_TOKEN'),

    'whisper_url' => env('WHISPER_URL', 'http://whisper:8080'),

    'whisper_token' => env('WHISPER_TOKEN'),

    'cursor_key' => env('CURSOR_API_KEY'),

    'cursor_model' => env('CURSOR_MODEL', 'composer-2.5'),

    'app_url' => env('VOICE_APP_URL', 'http://app:8000'),

];
