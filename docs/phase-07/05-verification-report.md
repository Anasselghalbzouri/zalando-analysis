# Verification report — Phase 07

Every result below comes from a command that was actually run on 2026-11-05 on the Windows 11 host (Docker Desktop, WSL 2 backend). "Pass" means the observed output matched the expected behaviour; the evidence column quotes it.

## 1. Required checks
| # | Check | Command (summary) | Result | Evidence |
|---|---|---|---|---|
| 1 | Compose configuration | `docker compose config --quiet` | **Pass** | Exit 0 |
| 2 | Image build | `docker compose build` | **Pass** | `storefront-dev/php:local` and `storefront-dev/node:local` built. The PHP image reports PHP 8.4.27, extensions bcmath, intl, pcntl, pdo_mysql, redis, OPcache, zip; user `uid=1000(app)`. The Node image: v24.22.2, `uid=1000(node)` |
| 3 | Container start-up | `docker compose up -d --wait --wait-timeout 600` | **Pass after a fix** | First start in about 60 s. `queue` restarted until the framework tables existed (§3, F-1) |
| 4 | Health checks | `docker compose ps` | **Pass** | app, mysql, nginx, queue, scheduler and vite all `healthy` |
| 5 | MySQL from Laravel | `php artisan db:show` (in `app`) | **Pass** | MySQL 8.4.13, connection `mysql`, host `mysql`, database `storefront`, 9 framework tables |
| 6 | Laravel boot | `php artisan about`, `php artisan route:list` | **Pass** | Laravel 13.40.0, PHP 8.4.27, env local, timezone UTC, locale fr, drivers database. Routes: `up`, plus the framework's `storage/{path}` (not reachable through Nginx) |
| 7 | React / Vite build | `docker compose exec vite npm run build` | **Pass** | Vite 8.3.6: `dist/index.html`, `assets/index-*.css` (5.73 kB), `assets/index-*.js` (394.20 kB). React 19.3.1, react-router 8.5.1, react-redux 9.3.1, @reduxjs/toolkit 2.14.0, Tailwind 4.3.4 |
| 8 | Nginx configuration | `nginx -t` in `nginx`, in both SPA modes | **Pass** | "syntax is ok … test is successful"; nginx 1.30.6 |
| 9 | Browser access to the SPA | Headless Chrome (separate profile) `--dump-dom` on `/` and a deep link, in `vite` and `static` modes | **Pass after a fix** | `/` renders `<h1>Environnement de développement</h1>`, API base `/api/v1`, backend status "Disponible". A deep link renders "Page introuvable" from the client router. The first run failed (§3, F-2) |
| 10 | API health endpoint | `curl http://localhost:8080/up` | **Pass** | 200 "Application up"; the response includes a database query (`DiagnosingHealth` listener) |
| 11 | API routing through Nginx | `curl -H 'Accept: application/json' /api/v1/does-not-exist` | **Pass** | 404 `application/json` from Laravel ("The route api/v1/does-not-exist could not be found."), proving `/api/v1` reaches the framework. No endpoint exists yet by design |
| 12 | Git status and secret files | `git status`, `git check-ignore`, scan of the 166 files Git would add | **Pass** | `.env`, `backend/.env`, `frontend/.env` and `evidence/` ignored. Only the three `*.env.example` files are candidates. The scan found the local passwords and `APP_KEY` in no candidate file. Its only hits are the `change-me` placeholders and the `APP_KEY=base64:` pattern the entrypoint greps for. No commit exists |

## 2. Additional checks
| Check | Result | Evidence |
|---|---|---|
| Deep link refresh, dev mode | **Pass** | `GET /route-de-test-client` → 200 `index.html` |
| Deep link refresh, static mode | **Pass** | `/route-de-test-client` and `/deep/nested/route` → 200 `index.html` with `Cache-Control: no-cache` |
| Hashed assets, static mode | **Pass after a fix** | Single header `Cache-Control: public, max-age=31536000, immutable` (§3, F-3) |
| No directory listing | **Pass** | `/assets/` → 404 |
| Hidden files | **Pass** | `/.env`, `/.git/config`, `/backend/.env`, `/node_modules/.vite/../../.env` → 403 |
| No stray PHP execution | **Pass** | `/index.php`, `/api/../index.php`, `/favicon.php` → 404 |
| Upload size limit | **Pass** | 21 MB POST → 413 (limit 20 MB) |
| No CORS headers | **Pass** | Request with `Origin: http://evil.example` → no `Access-Control-*` header |
| Version disclosure | **Pass** | `Server: nginx` without a version; no `X-Powered-By` |
| HMR WebSocket through Nginx | **Pass** | Upgrade request → 101; browser console "[vite] connected." |
| File watching on the Windows drive | **Pass** | Touching a source file logged "hmr update" (polling) |
| Upstream re-resolution | **Pass** | After `docker compose restart app`, `/up` → 200 without restarting Nginx |
| Scheduler | **Pass** | `schedule:list` shows `scheduler-heartbeat` every minute; the container is healthy |
| Ports bound to localhost only | **Pass** | `127.0.0.1:8080` and `127.0.0.1:3306`; app and vite not published |
| Optional Redis profile | **Pass** | `redis` healthy; `Redis::ping()` from `app` → `true`. Stopped again |
| Optional Mailpit profile | **Pass** | UI 200 on `127.0.0.1:8025`; SMTP `mailpit:1025` reachable from `app`. Stopped again |
| **Fresh clone, README followed literally** | **Pass after a fix** | Only the 166 commit candidates were copied to a clean folder, then run as project `storefront-fresh` on ports 8081/13306/8026 with new volumes. The entrypoint logged `composer install`, the creation of `backend/.env` and `APP_KEY`. Every README step exited 0; all six services healthy; `/`, a deep link and `/up` → 200; `/api/v1/x` → 404 JSON; browser render OK; 4 tests passed; build OK. Torn down with `down -v` afterwards (§3, F-5, F-6) |
| Laravel test suite | **Pass after a fix** | `php artisan test` → 4 passed, including the database-isolation guard (§3, F-4) |

## 3. Failures found and fixed
| ID | Failure (exact) | Cause | Fix | Re-run |
|---|---|---|---|---|
| F-1 | `queue` unhealthy on first start, with a `QueryException` | The database queue driver needs the `jobs` table, and no migration had run | Ran `php artisan migrate`. It's documented as a first-run step (README) | `queue` healthy |
| F-2 | Headless Chrome showed an empty `<div id="root">`. `/node_modules/.vite/deps/react.js` and the other dependencies → **403** | The Nginx hidden-file rule `location ~ /\.` matched the `.vite` path segment and denied Vite's pre-bundled dependencies, so React never loaded in dev mode. The curl checks hadn't caught it | The rule now exempts `/.well-known/` and `/.vite/`. Hidden files are still denied (see §2) | All dependencies 200; browser renders |
| F-3 | Two `Cache-Control` headers on `/assets/*` in static mode | `expires 1y` adds its own header next to `add_header` | Removed `expires` | One header |
| F-5 | Fresh clone: the README's `docker compose up -d --build --wait` exited 1 with "container storefront-fresh-queue-1 is unhealthy" | On an empty database, `queue` (and the database cache of `scheduler`) start before the framework tables exist | The README now starts `app`, runs `migrate`, then starts everything | All three steps exit 0 from scratch |
| F-6 | Fresh clone with `MYSQL_FORWARD_PORT=3307`: "bind: An attempt was made to access a socket in a way forbidden by its access permissions" | 3307 is in a Windows reserved (excluded) port range; it was my test port, not the default | Re-ran on 13306; troubleshooting row added to the README | Pass |
| F-4 | The new guard test failed: `-'sqlite' +'mysql'` | `phpunit.xml` set `DB_CONNECTION`/`DB_DATABASE` without `force`. Compose's container variables (and `$_SERVER`, which Laravel reads first) won, so **tests ran against the development MySQL database** | `force="true"` on `<env>`, plus forced `<server>` entries | 4 passed; the dev database still has its 9 tables |

## 4. Not verified
- **Another machine, or a repository inside the WSL filesystem:** the fresh-clone run used a clean copy on this Windows host (`C:\`) only.
- **A Linux host:** there, Docker would create `frontend/dist` owned by root when nginx mounts it before the first build, and `npm run build` (user `node`) would then fail. Not an issue on Docker Desktop for Windows.
- **Linux hosts with UID/GID other than 1000:** not tested. The variables exist for it.
- **A visual check in an interactive browser:** the browser check used headless Chrome DOM dumps, not screenshots.
- **Production images and TLS:** not in Phase 07 scope (docker-architecture §9).
