#!/bin/sh
# First-run bootstrap for the Laravel containers (local development only).
# Only the "app" service installs dependencies; scheduler and queue start after app is healthy.
set -e

if [ "${APP_BOOTSTRAP:-false}" = "true" ]; then
    if [ ! -f vendor/autoload.php ]; then
        echo "[entrypoint] vendor/ is empty: running composer install"
        composer install --no-interaction --prefer-dist --no-progress
    fi
    if [ ! -f .env ]; then
        echo "[entrypoint] backend/.env not found: creating it from .env.example"
        cp .env.example .env
    fi
    if ! grep -q '^APP_KEY=base64:' .env; then
        echo "[entrypoint] generating APP_KEY in backend/.env"
        php artisan key:generate --no-interaction
    fi
fi

exec "$@"
