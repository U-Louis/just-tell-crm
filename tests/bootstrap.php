<?php

require __DIR__.'/../vendor/autoload.php';

// PHPUnit's force attribute updates putenv and $_ENV, not $_SERVER.
// Laravel reads $_SERVER first, so a container DB_* value would still win
// and RefreshDatabase would wipe Postgres. Copy the PHPUnit values across.
foreach (['APP_ENV', 'DB_CONNECTION', 'DB_DATABASE'] as $name) {
    $value = getenv($name);

    if ($value === false) {
        continue;
    }

    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}
