# Storefront (working name)

An independent e-commerce web application: a Laravel 13 REST API and a React 19 SPA behind one Nginx origin, run locally with Docker Compose. The product name is undecided (Q-75), so "Storefront" is a neutral placeholder.

- **Status:** development environment ready (Phase 07). No shop features are implemented yet; they come from the Phase 06 backlog.
- **Scope:** checkout and payment (M21) are **out of scope**.

| Topic | Where |
|---|---|
| Requirements | [docs/requirements/](docs/requirements/SRS.md) |
| Architecture | [docs/architecture/](docs/architecture/system-overview.md) |
| Backlog and sprints | [docs/phase-06/](docs/phase-06/03-product-backlog.md) |
| Environment | [docs/phase-07/](docs/phase-07/06-phase-07-summary.md) |
| Development with Spec Kit (Phase 08) | [docs/phase-08/00-spec-kit-workflow.md](docs/phase-08/00-spec-kit-workflow.md) |

## Prerequisites
- **Windows 11** with **Docker Desktop** (WSL 2 backend), **Git** and **VS Code**. macOS and Linux with Docker Engine and Compose v2 should work too, but haven't been tested.
- **Nothing else on the host:** PHP, Composer and Node run inside the containers.
- **Free local ports:** 8080 (app), 3306 (MySQL, optional), and 8025 (Mailpit, optional).

## Setup
```bash
git clone <repository-url> storefront && cd storefront
cp .env.example .env          # then edit .env: set MYSQL_PASSWORD and MYSQL_ROOT_PASSWORD to local values
docker compose up -d --build --wait app        # MySQL + Laravel first (installs Composer packages)
docker compose exec app php artisan migrate    # framework tables: sessions, cache, jobs
docker compose up -d --wait                    # everything else: scheduler, queue, vite, nginx
```
The order matters on the first run: `queue` and `scheduler` need the `jobs` and `cache` tables, so a plain `docker compose up -d --wait` on an empty database ends with "queue is unhealthy".
- **Then open:** http://localhost:8080. The dev page should say "Backend (/up): Disponible".
- **First start:** it takes a few minutes. `app` installs Composer packages and creates `backend/.env` with an `APP_KEY`. `vite` installs npm packages.
- **Windows tip:** for much faster file access, clone the repository inside the WSL filesystem (e.g. `\\wsl$\Ubuntu-22.04\home\<you>\projects`) and open it from there in VS Code.

## Environment configuration
| File | Purpose |
|---|---|
| `.env` (from `.env.example`) | Compose: ports, MySQL credentials, UID/GID, SPA mode |
| `backend/.env` (from `backend/.env.example`) | Laravel settings. Created automatically if it's missing. DB credentials come from compose |
| `frontend/.env` (from `frontend/.env.example`, optional) | `VITE_API_BASE`. Public values only |

**Never commit a `.env` file:** they are git-ignored. Details: [docs/phase-07/04-configuration-guide.md](docs/phase-07/04-configuration-guide.md).

## Local URLs
| URL | What |
|---|---|
| http://localhost:8080 | The SPA (Vite dev server with HMR, through Nginx) |
| http://localhost:8080/api/v1/… | The REST API (no endpoints yet) |
| http://localhost:8080/up | Health check (app and database) |
| http://localhost:8025 | Mailpit, with `--profile mail` |
| `127.0.0.1:3306` | MySQL for local tools (credentials in `.env`) |

## Docker commands
```bash
docker compose up -d --wait                  # start (add --build after Dockerfile changes)
docker compose ps                            # status and health
docker compose logs -f app nginx             # follow logs
docker compose stop                          # stop, keep data
docker compose down                          # remove containers, keep volumes (database, vendor, node_modules)
docker compose down -v                       # remove everything, including the local database
docker compose --profile redis up -d redis   # optional Redis
docker compose --profile mail up -d mailpit  # optional Mailpit
```

## Useful commands
```bash
docker compose exec app php artisan about          # Laravel environment
docker compose exec app php artisan migrate        # run migrations
docker compose exec app php artisan test           # back-end tests (in-memory SQLite, never the dev DB)
docker compose exec app composer require <pkg>     # add a PHP package
docker compose exec vite npm install <pkg>         # add a JS package
docker compose exec vite npm run build             # build the SPA into frontend/dist
docker compose exec nginx nginx -t                 # validate the Nginx configuration
docker compose exec mysql mysql -u storefront -p   # MySQL shell (password from .env)
python -I tools/phase06/gen.py .                   # regenerate and check the Phase 06 backlog docs
```
- **Production-like SPA serving:** build first, then set `NGINX_SPA_MODE=static` in `.env` and run `docker compose up -d nginx`.
- **Back to development:** set `NGINX_SPA_MODE=vite` and run the same command.

## Conventions
- **Branches:** `feature/NNN-us-0xx-short-name`, one per Phase 06 story, created by Spec Kit (`/speckit-specify`, see [docs/phase-08/00-spec-kit-workflow.md](docs/phase-08/00-spec-kit-workflow.md)). Merge by pull request once the [Definition of Done](docs/phase-06/07-definition-of-done.md) is met.
- **Commits:** imperative subject naming the story, e.g. `US-059: add problem+json renderer`.
- **Line endings:** LF (`.gitattributes`). Shell scripts with CRLF break inside the containers.
- **Secrets:** only `*.env.example` files with placeholders are committed.
- **UI copy:** French, in `frontend/src/shared/i18n/messages.fr.js`.

## Troubleshooting
| Symptom | Fix |
|---|---|
| `failed to connect to the docker API … dockerDesktopLinuxEngine` | Start Docker Desktop and wait until the engine is running |
| `required variable MYSQL_… is missing a value` | Create `.env` from `.env.example` and fill in the MySQL values |
| `queue is unhealthy` on the first `up --wait`, or `queue` restarts with a `QueryException` | The database is empty. Run `docker compose exec app php artisan migrate`, then `docker compose up -d --wait`. Follow the Setup order next time |
| `nginx` stays "waiting" on first start | `app` or `vite` is still installing dependencies; check with `docker compose logs -f app vite` |
| `ports are not available … bind: An attempt was made to access a socket in a way forbidden` | Windows reserves some port ranges (check `netsh interface ipv4 show excludedportrange protocol=tcp`). Choose another port in `.env` |
| Port 8080 or 3306 already in use | Change `NGINX_PORT` or `MYSQL_FORWARD_PORT` in `.env`; with a new `NGINX_PORT`, also update `APP_URL` in `backend/.env` |
| Code changes don't reload | Keep `VITE_USE_POLLING=true` on a Windows drive, or move the repository into WSL |
| `$'\r': command not found` or `exec … no such file` in a container | A script was checked out with CRLF. Run `git config core.autocrlf input` and `git add --renormalize .`, then rebuild |
| Access denied for MySQL after changing passwords in `.env` | The passwords only apply when the volume is first created: `docker compose down -v` (deletes local data) |
| Permission errors on `backend/storage` (Linux) | Set `UID`/`GID` in `.env` to `id -u` / `id -g`, then `docker compose build app` |
| New npm package missing in the container | `docker compose exec vite npm install`. The `node_modules` volume only installs automatically when it's empty |
