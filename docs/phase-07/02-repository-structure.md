# Repository structure — Phase 07

The layout follows docker-architecture §7 and the directory structures in backend-architecture §16 and frontend-architecture §16. **No existing file was moved.**

```text
.
├── compose.yaml              Local development stack (all services)
├── .env.example              Compose settings: ports, MySQL credentials (placeholders), SPA mode
├── .gitignore, .gitattributes, .editorconfig
├── .vscode/extensions.json   Recommended VS Code extensions
├── README.md                 Setup, commands, troubleshooting
├── CLAUDE.md.txt             Project rules (Phase 01)
├── backend/                  Laravel 13 API (PHP 8.4 in Docker)
│   ├── app/                  Only the skeleton files. Modules come with the Phase 06 stories (app/Modules/<Module>/)
│   ├── bootstrap/app.php     Routing: api prefix api/v1, health /up
│   ├── config/cors.php       No CORS paths (same origin)
│   ├── routes/               api.php (empty), web.php (empty), console.php (scheduler heartbeat)
│   ├── tests/                Example tests guarding the test-database isolation, /up and /api/v1 routing
│   └── .env.example          Laravel settings; DB credentials come from compose
├── frontend/                 React 19 SPA (Vite 8, Tailwind 4, React Router 8, Redux Toolkit 2)
│   ├── index.html, vite.config.js, package.json, package-lock.json, .npmrc, .env.example
│   └── src/
│       ├── main.jsx          Providers: ErrorBoundary, Redux store, router
│       ├── app/              store.js, api.js (root RTK Query slice), router.jsx, routes/ (dev placeholders)
│       ├── layouts/          (empty; StorefrontLayout etc. come with US-043)
│       ├── features/         (empty; one folder per feature module)
│       ├── shared/           ui/ErrorBoundary.jsx, i18n/messages.fr.js, lib/ (empty)
│       └── styles/index.css  Tailwind entry
├── docker/
│   ├── php/                  Dockerfile, entrypoint.sh, conf.d/app.ini, php-fpm.d/zz-app.conf
│   ├── node/                 Dockerfile, entrypoint.sh
│   └── nginx/                templates/default.conf.template, snippets/spa-vite.conf, snippets/spa-static.conf
├── docs/                     Phases 01–07
├── tools/phase06/            Backlog source and generator (Phase 06)
└── evidence/                 Reference captures. Git-ignored (see 06-phase-07-summary §5)
```

## Separation of concerns
| Area | Location | Notes |
|---|---|---|
| Backend | `backend/` | Self-contained Laravel app. Mounted at `/var/www/html` in `app`, `scheduler` and `queue` |
| Frontend | `frontend/` | Self-contained Vite app. Mounted at `/app` in `vite`; its build output `frontend/dist/` is served by Nginx in static mode |
| Infrastructure | `compose.yaml`, `docker/` | No application code |
| Documentation | `docs/` | Unchanged except for the new `docs/phase-07/` |

## Placeholders, not features
The SPA has two routes only:
- `/`: a development status page showing the API base and the result of `/up`.
- `*`: a not-found page, used to prove that client-side routes survive a refresh.

The API has no endpoint. Its route files carry a comment pointing to api-contracts.md. No model, controller, business migration, product, campaign, cart or checkout page was created.

## Development conventions (initial)
- **Branches:** `main` is protected by convention. Work happens on `feature/US-0xx-short-name` branches named after the Phase 06 story, and merges by pull request once the Definition of Done ([07-definition-of-done.md](../phase-06/07-definition-of-done.md)) is met.
- **Commits:** imperative subject that names the story, e.g. `US-059: add problem+json renderer`. One logical change per commit.
- **Line endings:** LF everywhere (`.gitattributes`). Don't change `core.autocrlf` back to `true` for this repository.
- **Tools run in containers:** `docker compose exec app …` for PHP, `docker compose exec vite …` for Node. Host PHP and Node versions don't matter.
- **Secrets:** only `*.env.example` files are committed, with placeholders. Real values stay in the ignored `.env` files.
- **Copy:** UI strings live in `frontend/src/shared/i18n/messages.fr.js` (French only).
