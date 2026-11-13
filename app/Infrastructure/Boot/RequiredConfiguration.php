<?php

namespace App\Infrastructure\Boot;

use RuntimeException;

final class RequiredConfiguration
{
    public function assert(): void
    {
        $key = config('app.key');
        if (! is_string($key) || $key === '') {
            throw new RuntimeException('APP_KEY is missing.');
        }

        $connection = config('database.default');
        if (! is_string($connection) || $connection === '') {
            throw new RuntimeException('Database connection is missing.');
        }

        if (! app()->environment('testing') && $connection !== 'pgsql') {
            throw new RuntimeException('Database connection must be pgsql.');
        }
    }
}
