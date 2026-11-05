# Configuration guide — Phase 07

## 1. Environment files
| File | Committed | Read by | Contains |
|---|---|---|---|
| `.env.example` → `.env` | example only | Docker Compose | UID/GID, published ports, MySQL database, user and passwords, `NGINX_SPA_MODE`, `VITE_USE_POLLING` |
| `backend/.env.example` → `backend/.env` | example only | Laravel | App name, URL, locale, drivers, Redis and mail settings. **No DB credentials**: compose injects `DB_*` |
| `frontend/.env.example` → `frontend/.env` (optional) | example only | Vite at build time | `VITE_API_BASE` (default `/api/v1`). Anything `VITE_*` is public |

**Rules:**
- `.env` and `.env.*` are git-ignored at every level, except `*.env.example`.
- Example files hold placeholders only (`change-me-…`). They are never real or production credentials.
- **Precedence:** a variable set on the container (compose `environment:`) wins over `backend/.env`. That's how the MySQL credentials have one source: the root `.env`.
- **Tests:** they never use the development database. `backend/phpunit.xml` forces an in-memory SQLite database (both `<env>` and `<server>`, because Laravel reads `$_SERVER` first). A MySQL test database comes with CI (US-058).

## 2. Creating the local files
```text
1. Copy .env.example to .env (repository root).
2. In .env, replace MYSQL_PASSWORD and MYSQL_ROOT_PASSWORD with your own local values.
   On Linux or WSL, also set UID and GID to the output of `id -u` and `id -g`.
3. Optional: copy backend/.env.example to backend/.env.
   If you skip it, the app container creates it on first start and generates APP_KEY.
4. Optional: copy frontend/.env.example to frontend/.env to override VITE_API_BASE.
```
Then start the stack in the order given in the [README](../../README.md#setup): `app` first, then `php artisan migrate`, then everything else. A plain `up --wait` on an empty database fails, because `queue` needs the `jobs` table.

The MySQL passwords are applied only when the `mysql-data` volume is first created. To change them later, run `docker compose down -v`, which **deletes the local database**.

## 3. Variables
### Root `.env` (compose)
| Variable | Default in example | Purpose |
|---|---|---|
| `UID`, `GID` | 1000 | Owner of files written by the PHP container |
| `NGINX_PORT` | 8080 | Published HTTP port (also the HMR client port) |
| `MYSQL_FORWARD_PORT` | 3306 | Published MySQL port for local tools (127.0.0.1 only) |
| `MAILPIT_UI_PORT` | 8025 | Mailpit UI (profile `mail`) |
| `MYSQL_DATABASE`, `MYSQL_USER` | storefront | Application database and user |
| `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD` | `change-me-…` | Local only. Compose refuses to start when any MySQL value is unset |
| `NGINX_SPA_MODE` | vite | `vite` (dev server, HMR) or `static` (serve `frontend/dist`) |
| `VITE_USE_POLLING` | true | File watching on Windows-drive bind mounts |

### `backend/.env` (Laravel)
| Group | Values | Notes |
|---|---|---|
| Application | `APP_NAME="Storefront (dev)"`, `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=http://localhost:8080`, `APP_LOCALE=fr` | The product name is undecided (Q-75). The timezone is UTC (config/app.php) |
| Database | `DB_CONNECTION=mysql`, `DB_CHARSET=utf8mb4`, `DB_COLLATION=utf8mb4_0900_ai_ci` | Host, name, user and password come from compose |
| Session, cache, queue | `database` | Baseline (ADR-007). Switching to `redis` requires the `redis` profile |
| Redis | `REDIS_HOST=redis`, `REDIS_CLIENT=phpredis` | Optional |
| Mail | `MAIL_MAILER=log` | For Mailpit: `smtp`, `mailpit`, `1025` |

**Not configured yet:** the storefront policy values and feature switches (`STOREFRONT_*`, `FEATURE_*`, system-overview §7). They belong to `config/storefront.php` in US-059. Each one is a PO decision with a Q-ID and must not get a silent default.

## 4. CORS and same origin
- **Single origin:** the SPA and the API share `http://localhost:8080` (ADR-003).
- **CORS:** `backend/config/cors.php` has no `paths`, so Laravel sends no CORS headers. A cross-origin request with an `Origin` header got none (verified).
- **Adding an origin:** add one only through an approved decision.

## 5. Switching the SPA mode
```text
Production-like check:  docker compose exec vite npm run build
                        set NGINX_SPA_MODE=static in .env, then: docker compose up -d nginx
Back to development:    set NGINX_SPA_MODE=vite in .env, then:   docker compose up -d nginx
```

## 6. Optional services
| Need | Command | Then |
|---|---|---|
| Redis | `docker compose --profile redis up -d redis` | Set `CACHE_STORE`, `QUEUE_CONNECTION` and/or `SESSION_DRIVER` to `redis` in `backend/.env`, then restart app, queue and scheduler |
| Mailpit | `docker compose --profile mail up -d mailpit` | Set `MAIL_MAILER=smtp` (host `mailpit`, port 1025); open http://localhost:8025 |
