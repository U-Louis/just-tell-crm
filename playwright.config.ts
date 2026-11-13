import { defineConfig } from '@playwright/test';

const port = process.env.APP_PORT ?? '8000';

export default defineConfig({
    testDir: 'e2e',
    testMatch: /scenario-1\.spec\.ts/,
    use: {
        baseURL: `http://127.0.0.1:${port}`,
    },
    webServer: [
        {
            command: `php artisan migrate --force && php artisan db:seed --force && cd public && php -S 127.0.0.1:${port} ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`,
            url: `http://127.0.0.1:${port}`,
            reuseExistingServer: true,
            timeout: 120_000,
        },
        {
            command: 'npm run dev',
            url: 'http://127.0.0.1:5173/@vite/client',
            reuseExistingServer: true,
            timeout: 120_000,
        },
    ],
});
