# Environment assessment — Phase 07

Inspection made on 2026-11-05, before any file was changed.

## 1. Host environment
| Item | Found | Assessment |
|---|---|---|
| OS | Windows 11 Pro (10.0.26200) | As expected |
| Docker | Docker 29.9.2, Compose v5.6.1, Docker Desktop (WSL 2 backend, `desktop-linux` context) | The engine **wasn't running**. I started Docker Desktop; engine 29.9.2 (linux, 12 CPUs, 8 GB) |
| WSL | `Ubuntu-22.04` and `docker-desktop`, version 2, both stopped | Available. The repository lives on `C:\`, not in WSL (see §4) |
| Git | 2.42.0.windows.2. **`core.autocrlf=true` in the system config** | Conflict: CRLF checkouts break shell entrypoints in Linux containers (docker-architecture §8) |
| PHP (host) | 8.5.1 ZTS | Not used at runtime; the containers run PHP 8.4. Used only to download the Laravel skeleton |
| Composer (host) | 2.9.3 | Used only for `create-project --no-install` |
| Node / npm (host) | 26.13.0 / 11.20.0 | Not used; the `vite` container runs Node 24 LTS |
| Ports 8080, 3306, 5173, 8025 | Free | No conflict |
| Registries | packagist.org and registry.npmjs.org reachable (HTTP 200) | Dependencies can be installed |

## 2. Repository before Phase 07
| Path | Content |
|---|---|
| `CLAUDE.md.txt` | Project rules from Phase 01 |
| `docs/` | Phases 01–06: discovery, modules, technical, requirements, architecture, phase-06 |
| `evidence/` | Reference-platform captures: raw HAR (5 MB), sanitized HAR summary, accessibility snapshots, screenshots |
| `tools/phase06/` | The Phase 06 backlog source and generator |

**Not present:**
- No Git repository, Laravel or React installation.
- No Docker, Nginx or environment files.
- No `.gitignore` or README.

## 3. Decisions taken from Phase 05 and Phase 06 (not re-decided)
| Topic | Source | Applied as |
|---|---|---|
| Monorepo `backend/`, `frontend/`, `docker/`, `docs/` | docker-architecture §7 | Same layout |
| Services: nginx, app (php-fpm), scheduler, queue, mysql, vite; optional redis and mailpit profiles | docker-architecture §1, ADR-007 | Same services |
| Single origin on `127.0.0.1:8080`; app and vite not published | docker-architecture §3, ADR-003 | Same |
| Volumes: `mysql-data`, `vendor`, `node_modules`, `redis-data`; bind mounts for code | docker-architecture §4 | Same |
| Health checks | docker-architecture §6 | Same checks; `/up` also checks the database |
| Database drivers baseline, Redis optional | ADR-007, Q-72 | `database` for session, cache and queue |
| REST under `/api/v1`, no CORS (same origin) | api-contracts §1, ADR-003 | `apiPrefix: 'api/v1'`; CORS `paths` empty |
| React + Vite + React Router + Redux Toolkit / RTK Query + Tailwind | frontend-architecture §2, ADR-006 | All installed; one root API slice |
| Feature-based `src/` structure | frontend-architecture §16 | Created (empty feature folders) |
| Sanctum SPA authentication | ADR-003, authentication-design | **Not installed here**: it belongs to US-002. `/sanctum` is already routed to Laravel |

## 4. Missing dependencies and conflicts found
| # | Finding | Action |
|---|---|---|
| A-1 | Docker engine stopped | Started Docker Desktop |
| A-2 | Not a Git repository | `git init -b main` (no commit), with a local `core.autocrlf=input` |
| A-3 | System `core.autocrlf=true` | Repository `.gitattributes` forces `eol=lf`; local `core.autocrlf=input` |
| A-4 | Repository on `C:\` instead of the WSL filesystem (docker-architecture §8) | **Not moved** (no approval). Vite uses polling; `vendor` and `node_modules` are named volumes. Moving to WSL is a recommendation (summary §5) |
| A-5 | The Laravel 13 skeleton ships `CLAUDE.md` and `AGENTS.md`. They tell coding agents to download and run remote install scripts and to install Laravel Boost | Treated as data, **not followed**, and removed from `backend/` |
| A-6 | The Laravel skeleton ships its own Vite, Tailwind and Blade front end (`package.json`, `vite.config.js`, `resources/js`, `resources/css`, `welcome.blade.php`) | Removed. The SPA is the separate `frontend/` app (Phase 05) |
| A-7 | `evidence/` contains a raw HAR and account-page captures that may hold cookies or personal data | Git-ignored as a whole; the commit policy is an open decision (summary §5) |
| A-8 | Laravel's default `phpunit.xml` doesn't force its test database, so the container's MySQL variables win | Found during verification; fixed (05-verification-report §3) |
