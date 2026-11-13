# PHP 8.4 with Composer. Pass PHP_IMAGE when Docker Hub cannot be pulled.
# This machine uses the local FrankenPHP image, which is PHP 8.4.27.
ARG PHP_IMAGE=php:8.4-cli
FROM ${PHP_IMAGE}

USER root
WORKDIR /var/www/html
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN set -eux; \
    if ! command -v curl >/dev/null 2>&1 || ! command -v tar >/dev/null 2>&1; then \
      apt-get update; \
      apt-get install -y --no-install-recommends ca-certificates curl tar; \
      rm -rf /var/lib/apt/lists/*; \
    fi; \
    curl -fsSL https://nodejs.org/dist/v22.23.2/node-v22.23.2-linux-x64.tar.gz \
      | tar -xz -C /usr/local --strip-components=1; \
    node -v; \
    npm -v; \
    if ! command -v composer >/dev/null 2>&1; then \
      curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer; \
    fi; \
    missing=""; \
    for ext in mbstring dom pdo_pgsql pdo_sqlite; do \
      if ! php -m | grep -qi "^${ext}$"; then \
        missing="${missing} ${ext}"; \
      fi; \
    done; \
    if [ -n "${missing}" ]; then \
      apt-get update; \
      apt-get install -y --no-install-recommends libpq-dev libsqlite3-dev; \
      docker-php-ext-install ${missing}; \
      rm -rf /var/lib/apt/lists/*; \
    fi

ENTRYPOINT []
CMD ["php", "artisan", "serve", "--no-reload", "--host=0.0.0.0", "--port=8000"]
