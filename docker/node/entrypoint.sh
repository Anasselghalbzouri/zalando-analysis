#!/bin/sh
# Installs front-end dependencies into the node_modules volume on first start.
# After changing package.json, run: docker compose exec vite npm install
set -e

if [ ! -f node_modules/.package-lock.json ]; then
    if [ -f package-lock.json ]; then
        echo "[entrypoint] node_modules is empty: running npm ci"
        npm ci --no-audit --no-fund
    else
        echo "[entrypoint] no package-lock.json: running npm install"
        npm install --no-audit --no-fund
    fi
fi

exec "$@"
