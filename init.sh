#!/usr/bin/env bash
# Boot Just Tell CRM in Docker. A second run migrates and serves. It does not drop the database.
set -euo pipefail
cd "$(dirname "$0")"

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker is required. PHP, Composer, and Node are not installed on the host for this boot." >&2
  exit 1
fi

if [[ ! -f .env ]]; then
  cp .env.example .env
fi

if docker image inspect php:8.4-cli >/dev/null 2>&1; then
  PHP_IMAGE=php:8.4-cli
elif docker image inspect ratatosk-app:latest >/dev/null 2>&1; then
  PHP_IMAGE=ratatosk-app:latest
else
  docker pull php:8.4-cli
  PHP_IMAGE=php:8.4-cli
fi
export PHP_IMAGE

# CPU stack is docker-compose.yml. ./init.sh gpu adds docker-compose.gpu.yml.
compose_args=(-f docker-compose.yml)
if [[ "${1:-}" == "gpu" ]]; then
  compose_args+=(-f docker-compose.gpu.yml)
fi

compose() {
  docker compose "${compose_args[@]}" "$@"
}

compose build
compose up -d postgres

ready=0
for _ in $(seq 1 30); do
  if compose exec -T postgres pg_isready -U just_tell -d just_tell_crm >/dev/null 2>&1; then
    ready=1
    break
  fi
  sleep 1
done
if [[ "$ready" -ne 1 ]]; then
  echo "Postgres did not become ready." >&2
  exit 1
fi

if ! grep -q '^APP_KEY=base64:' .env; then
  key="$(compose run --rm --no-deps --entrypoint php app -r 'echo "base64:".base64_encode(random_bytes(32));')"
  sed -i "s|^APP_KEY=.*|APP_KEY=${key}|" .env
fi

compose run --rm --no-deps app composer install --no-interaction --prefer-dist
compose run --rm --no-deps app npm ci
compose run --rm app php artisan migrate --force
compose run --rm app php artisan db:seed --force
compose up -d app vite queue

printf 'origin http://127.0.0.1:8000\n'
