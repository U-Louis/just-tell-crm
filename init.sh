#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
port="${APP_PORT:-8000}"

php -m | grep -q '^mbstring$' || {
  echo "php is missing the mbstring extension." >&2
  exit 1
}
php -m | grep -q '^dom$' || {
  echo "php is missing the dom extension." >&2
  exit 1
}
php -m | grep -q '^pdo_pgsql$' || {
  echo "php is missing the pdo_pgsql extension." >&2
  exit 1
}

if [[ ! -f .env ]]; then
  cp .env.example .env
fi
if ! grep -q '^APP_KEY=base64:' .env; then
  key="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
  sed -i "s|^APP_KEY=.*|APP_KEY=${key}|" .env
fi

if command -v docker >/dev/null 2>&1 && [[ -f compose.yaml ]]; then
  docker compose up -d
  ready=0
  for _ in $(seq 1 30); do
    if docker compose exec -T postgres pg_isready -U just_tell -d just_tell_crm >/dev/null 2>&1; then
      ready=1
      break
    fi
    sleep 1
  done
  if [[ "$ready" -ne 1 ]]; then
    echo "Postgres did not become ready." >&2
    exit 1
  fi
fi

composer install --no-interaction
npm ci
php artisan migrate --force
php artisan db:seed --force
printf 'origin http://127.0.0.1:%s\n' "$port"
# Serve with the current php. `php artisan serve` re-executes PHP_BINARY and drops a wrapper's ini flags.
# The router uses getcwd() as the public directory.
(
  cd public
  php -S "127.0.0.1:${port}" ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
) &
npm run dev
