<?php

require __DIR__.'/../vendor/autoload.php';

// PHPUnit's force attribute updates putenv and $_ENV, not $_SERVER.
// Laravel reads $_SERVER first, so a container value would still win.
// A container DB_CONNECTION would make RefreshDatabase wipe Postgres.
// Copy the PHPUnit values across.
foreach ([
    'APP_ENV',
    'DB_CONNECTION',
    'DB_DATABASE',
    'VOICE_TOKEN',
    'WHISPER_URL',
    'WHISPER_TOKEN',
    'VOICE_APP_URL',
    'CURSOR_API_KEY',
    'CURSOR_MODEL',
] as $name) {
    $value = getenv($name);

    if ($value === false) {
        continue;
    }

    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}
