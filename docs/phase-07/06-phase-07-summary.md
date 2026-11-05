# Phase 07 summary — Development environment

## 1. Outcome
The local environment runs, and every Phase 07 completion criterion was verified by an executed command ([05-verification-report.md](05-verification-report.md)):
- **Repository:** the structure is coherent (`backend/`, `frontend/`, `docker/`, `docs/`).
- **Laravel:** 13.40.0 boots on PHP 8.4.27 and reaches MySQL 8.4.13.
- **React:** the 19.3 SPA builds with Vite 8.3.
- **Docker:** all six core services start and report healthy; the optional Redis and Mailpit profiles work.
- **Nginx:** it serves the SPA in both modes (Vite and static), including deep-link refresh, and routes `/api/v1` and `/up` to Laravel.
- **Secrets:** no secret is committable, and no commit was made.
- **Documentation:** setup is documented in the [README](../../README.md) and [04-configuration-guide.md](04-configuration-guide.md).

No business feature was implemented. There are no models, controllers, business migrations, endpoints or shop pages, and checkout and payment (M21) remain out of scope.

## 2. Files
**Created:**
- **Root:**
  - `compose.yaml`, `.env.example`
  - `.gitignore`, `.gitattributes`, `.editorconfig`, `.vscode/extensions.json`
  - `README.md`
- **`docker/`:**
  - `php/Dockerfile`, `php/entrypoint.sh`, `php/conf.d/app.ini`, `php/php-fpm.d/zz-app.conf`
  - `node/Dockerfile`, `node/entrypoint.sh`
  - `nginx/templates/default.conf.template`, `nginx/snippets/spa-vite.conf`, `nginx/snippets/spa-static.conf`
- **`backend/`:**
  - the Laravel 13.11.0 skeleton (`composer create-project`)
  - `routes/api.php`, `config/cors.php` (published, then edited)
  - `composer.lock`, written by `composer install`
- **`frontend/`:**
  - `package.json`, `package-lock.json` (from `npm install`), `.npmrc`, `.env.example`
  - `index.html`, `vite.config.js`
  - `src/main.jsx`, `src/app/{api.js, store.js, router.jsx, routes/DevHomePage.jsx, routes/NotFoundPage.jsx}`
  - `src/shared/ui/ErrorBoundary.jsx`, `src/shared/i18n/messages.fr.js`, `src/styles/index.css`
  - `.gitkeep` in `src/layouts`, `src/features` and `src/shared/lib`
- **`docs/phase-07/`:** 01–06.
- **Local only (git-ignored):**
  - `.env`, with random local MySQL passwords
  - `backend/.env`, with a generated `APP_KEY`
  - `frontend/dist/`

**Modified (skeleton files):**
- `backend/bootstrap/app.php`: `api/v1` prefix and API routes.
- `backend/routes/web.php`: no routes.
- `backend/routes/console.php`: scheduler heartbeat.
- `backend/app/Providers/AppServiceProvider.php`: database check on `/up`.
- `backend/.env.example`: MySQL, French locale, port 8080, Redis and Mailpit hosts.
- `backend/phpunit.xml`: forced test database.
- `backend/tests/Feature/ExampleTest.php`
- `backend/README.md`

**Removed from the fresh skeleton:**
- `backend/CLAUDE.md` and `backend/AGENTS.md`: agent instructions to run remote install scripts. They were not followed.
- Laravel's bundled front end: `package.json`, `.npmrc`, `vite.config.js`, `resources/js`, `resources/css`, `resources/views/welcome.blade.php`.
- A `bash.exe.stackdump` crash file created by the shell during this session.

**Not changed:** Phase 01–06 documents, `tools/phase06/`, `evidence/` (the HAR is untouched).

## 3. Commands executed (main ones)
```text
Start-Process "Docker Desktop.exe"                       (engine was stopped)
composer create-project laravel/laravel backend "^13.0" --no-install --no-scripts
docker pull php:8.4-fpm-alpine nginx:1.30-alpine mysql:8.4 node:24-alpine redis:8-alpine axllent/mailpit composer:2
git init -b main ; git config core.autocrlf input
docker compose config --quiet ; docker compose build ; docker compose up -d --wait
docker compose exec app php artisan migrate | config:publish cors | about | db:show | route:list | schedule:list | test
docker compose exec vite npm run build ; docker compose exec nginx nginx -t
curl checks through http://localhost:8080 (SPA, deep links, /up, /api/v1, hidden files, PHP, uploads, CORS, HMR)
NGINX_SPA_MODE=static docker compose up -d nginx         (static mode checks, then back to vite)
docker compose -p storefront-fresh ...                   (fresh-clone run of the README in a clean copy, then down -v)
chrome --headless=new --dump-dom (separate profile)      (browser render checks, both modes)
docker compose --profile redis --profile mail up -d redis mailpit   (then stopped and removed)
git status ; git check-ignore ; secret scan of the files Git would add
```

## 4. Known issues
| # | Issue | Impact | Next step |
|---|---|---|---|
| K-1 | **Repository location:** it is on `C:\`, not in the WSL filesystem | Slower bind mounts; Vite needs polling | Move it into WSL (docker-architecture §8). Your decision |
| K-2 | **Debug output:** with `APP_DEBUG=true`, API errors include stack traces | Local only | **Resolved by US-059:** the problem+json renderer hides internals |
| K-3 | **Default `users` migration:** the skeleton's migration ran, creating `users`, `password_reset_tokens` and `sessions` | The `users` shape differs from schema.md | US-001 replaces it. The dev database is disposable: `migrate:fresh` |
| K-4 | **Queue on first start:** `queue` restarts until `php artisan migrate` has run | First start only | Documented in the README |
| K-5 | **Test database:** tests use in-memory SQLite, not MySQL | The Phase 06 DoD wants MySQL wherever constraints or concurrency matter | A MySQL test database with CI (US-058, US-053) |
| K-6 | **Auth and headers not set up:** Sanctum isn't installed; no HSTS, CSP or frame headers yet | `/sanctum` is routed but serves nothing | US-002 (auth) and US-060 (headers) |
| K-7 | **Policy settings:** `config/storefront.php`, the policy values and the feature switches don't exist yet | None until features read them | **Resolved by US-059** |
| K-8 | **Root-run images:** `nginx` (master process) and `mailpit` run as root | Local development only | The unprivileged nginx image is an option for production (docker-architecture §9) |
| K-9 | **Unpinned image:** `axllent/mailpit:latest` | Optional dev tool; may change | Pin a version when CI exists |
| K-10 | **Tooling not set up:** no ESLint, Prettier, Pint, Vitest or Playwright configuration yet | — | US-058 (CI) and the testing tasks |
| K-11 | **Storage routes:** Laravel registers `storage/{path}` routes for the local disk | Not reachable: Nginx forwards only `/api`, `/sanctum` and `/up` | Review when file uploads are designed |
| K-12 | **One database user:** apps and migrations share one MySQL user (Phase 05 PROPOSED separate users) | — | US-058 |
| K-13 | **Linux hosts:** `frontend/dist` is created by Docker as root when nginx starts before the first build | `npm run build` would fail with a permission error | Create `frontend/dist` before the first `up`, or build first. Not seen on Windows |

## 5. Unresolved decisions
| Decision | Why it matters | Owner |
|---|---|---|
| **Evidence in Git:** `evidence/` is git-ignored as a whole. Should any of it be committed? | The raw HAR and the account-page captures may hold cookies, session data or personal data. The analysis docs reference these files | You (project owner) |
| **Repository location:** move the repository into WSL? | Performance and file watching (K-1) | You |
| **Q-82:** Git host and CI platform | Blocks US-058 | Tech Lead / DevOps |
| **Q-72:** Redis in shared environments | The profile exists; drivers stay `database` until decided | PO / DevOps |
| **Q-75:** product name and branding | "Storefront (dev)" and `storefront-dev` are neutral placeholders | PO |
| **Runtime versions:** PHP **8.4** (within the "8.3+" constraint), Node **24 LTS**, MySQL **8.4 LTS**, Nginx **1.30** | Chosen as current supported releases; Phase 05 fixed only "8.3+" and "MySQL 8" | Tech Lead to confirm |

## 6. Readiness for Phase 08
**Ready.** The environment supports the Phase 06 Sprint 0 stories:
- **US-057 (local Docker environment):** implemented here and verified, including a fresh-clone run that followed the README literally. It awaits PO acceptance against its criteria.
- **Ready to build now:** US-059 (API foundation: problem+json, request ID, server time, settings), US-060 (security headers), US-061 (MySQL behaviour spike) and US-046 (formatting).
- **Needs Q-82 first:** US-058 (CI).
- **Still to do:** the Sprint 0 decision workshop on the gate questions. Q-70 must be answered before the Catalog and Sales migrations (Sprint 1–2).

Phase 08 has **not** been started.
