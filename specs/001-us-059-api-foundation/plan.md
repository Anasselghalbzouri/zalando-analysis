# Implementation Plan: US-059 API foundation

**Branch**: `feature/001-us-059-api-foundation` | **Date**: 2026-11-05 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `specs/001-us-059-api-foundation/spec.md`

## Summary

This plan gives every `/api/v1` endpoint the same base:
- one problem+json renderer over a 20-code catalogue (error-format §2 + erratum E-01)
- a request ID reused from a safe inbound header or generated, and carried into the logs
- `meta.server_time` from a per-request clock (ADR-009)
- the rejection of unknown query parameters through a base Form Request
- four empty route groups by auth code
- `config/storefront.php` with `GET /api/v1/storefront/settings`

In production a missing policy value stops startup: `/api/v1` requests get a 503 `settings_incomplete` problem body, and `/up` and web routes get a plain 503. In other environments only the endpoint and the readers of that value fail closed (research R-01). No new dependency and no table.

## Technical Context

**Language/Version**: PHP 8.4 (container; constraint ^8.3), Laravel 13.40

**Primary Dependencies**: Laravel framework only: exception configuration, middleware groups, Form Requests, `Context`, Carbon. **No new package**.

**Storage**: none (the story has no tables). Configuration comes from the environment.

**Testing**: PHPUnit 12 feature and unit tests, run with `docker compose exec app php artisan test`. In-memory SQLite is enough, because no database constraint is involved (Principle V).

**Target Platform**: the Phase 07 Docker stack (nginx → PHP-FPM `app`) on `localhost:8080`

**Project Type**: web application (Laravel API in `backend/`, React SPA in `frontend/`; this feature is backend-only)

**Performance Goals**: no new target. The middleware adds only constant-time work per request (NFR-PERF stories own the budgets).

**Constraints**:
- no internals in error bodies, even with `APP_DEBUG=true`
- no silent policy defaults
- no personal data or query strings in logs

**Scale/Scope**: 1 endpoint, 20 error codes, 12 policy values, 2 feature switches, 4 route groups

There's no NEEDS CLARIFICATION left: Q-87 was answered (option A, erratum E-01), and the spec's two recorded tensions are settled in research R-01 and R-09.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Check | Result |
|---|---|---|
| I. Traceable, no invention | Spec cites US-059, NFR-OBS-01, NFR-TECH-01, AC-1…5, AC-NFR-OBS-01-1. The only addition beyond the docs (405/413/415 codes) went through Q-87 and erratum E-01 first. The policy Q-IDs stay open | Pass |
| II. Scope boundaries | No checkout, order or payment code. No Cart/Sales domain classes created early | Pass |
| III. Architecture conformance | `app/Modules/Storefront/` + `app/Support/` as in backend-architecture §16; thin controller, validation in a Form Request, rules in `StorefrontSettings`; problem+json per error-format; `meta.server_time` on success; production startup check per backend §12. No auth package (Sanctum comes with US-001/002) | Pass |
| IV. Data integrity | No data, no migrations. Times in UTC | Pass (N/A) |
| V. Test-first | Every acceptance scenario maps to a test written first. Time-travel on the request clock. No MySQL-specific behaviour, so SQLite is acceptable | Pass |
| VI. Security and privacy | Inbound request ID restricted to a safe set; bodies never show internals; logs hold no query strings, bodies, tokens or session IDs; settings `no-store`; `.env.example` holds placeholders only | Pass |
| VII. Decisions as configuration | All 11 PO values without defaults and fail closed; `listing_page_size` = 60 is CONFIRMED; both switches off until PO validation | Pass |
| VIII. French-first | Problem titles, details and field messages in `lang/fr/problems.php`, labelled PROPOSED. No UI strings | Pass |
| IX. Simplicity | No new dependency, no per-code exception classes, empty route groups only because T-059-B1 asks for them, the backend-only switches left to their stories | Pass |

**Post-design re-check (after Phase 1):** Pass. The design in research.md, data-model.md and contracts/ adds no table, endpoint, module or library beyond the above. `RequestClock` is the ADR-009 / backend §16 class that US-015 will reuse, so it doesn't count as speculative.

## Project Structure

### Documentation (this feature)

```text
specs/001-us-059-api-foundation/
├── spec.md
├── plan.md                  # this file
├── research.md              # Phase 0: R-01 … R-10
├── data-model.md            # Phase 1: problem, request ID, envelope, policy settings
├── quickstart.md            # Phase 1: validation scenarios
├── contracts/
│   └── api-foundation.md    # Phase 1: what US-059 guarantees to every endpoint
├── checklists/
│   └── requirements.md
└── tasks.md                 # Phase 2 (/speckit-tasks, not created here)
```

### Source Code (repository root)

```text
backend/
├── bootstrap/
│   ├── app.php                         # MODIFY: global AssignRequestId; api group += EnsureJsonBody, AddServerTime;
│   │                                   #         access.session/catalog/public/guest groups; ProblemRenderer for api/*
│   └── providers.php                   # MODIFY: + StorefrontServiceProvider
├── app/
│   ├── Support/
│   │   ├── RequestClock.php            # per-request "now" (request attribute, ADR-009)
│   │   ├── Problems/
│   │   │   ├── Catalogue.php           # code → status (20 codes)
│   │   │   ├── ApiProblem.php          # exception carrying code, detail, errors, headers
│   │   │   └── ProblemRenderer.php     # Throwable → problem+json, logging
│   │   └── Http/
│   │       ├── ApiFormRequest.php      # unknown query params → missing → unknown_parameter
│   │       └── Middleware/
│   │           ├── AssignRequestId.php
│   │           ├── AddServerTime.php
│   │           └── EnsureJsonBody.php
│   └── Modules/Storefront/
│       ├── StorefrontServiceProvider.php   # production startup check
│       ├── routes.php                      # settings route in access.public
│       ├── Exceptions/SettingsIncomplete.php  # extends ApiProblem (settings_incomplete)
│       ├── Services/StorefrontSettings.php
│       └── Http/Controllers/StorefrontSettingsController.php
├── config/storefront.php               # NEW: env reads, no defaults (except page size, switches)
├── lang/fr/problems.php                # NEW: titles, details, field messages (PROPOSED)
├── routes/api.php                      # MODIFY: require app/Modules/*/routes.php
├── .env.example                        # MODIFY: Storefront policy + Feature switches blocks (T-059-I1)
└── tests/
    ├── Feature/Api/
    │   ├── ProblemRendererTest.php     # 20 codes, no internals, Allow/Retry-After, 400/415
    │   ├── RequestIdTest.php
    │   ├── ServerTimeTest.php
    │   ├── UnknownParameterTest.php
    │   ├── StorefrontSettingsTest.php  # shape, no-store, 11 unset + 12 invalid → 503, production startup
    │   └── ApiRouteConventionsTest.php # every /api/v1 action type-hints ApiFormRequest
    └── Unit/StorefrontSettingsValidationTest.php
docs/architecture/api/error-format.md   # DONE in clarify: erratum E-01
```

**Structure Decision**: a web application with the backend change only. Cross-cutting code goes in `backend/app/Support/` and the settings in the `Storefront` module, exactly as in backend-architecture §16. `frontend/` is untouched: the clock slice and `useServerNow` are US-046.

## Risks and follow-ups

- **Startup failure rendering (R-01):** a `boot()` exception has to reach the render callback with the translator ready. Quickstart scenario 9 checks it. If it fails, fall back to a minimal JSON problem built without translation, and record that.
- **`last_hours_badge_threshold_hours` (Q-36):** treated as required in production. If the PO answers Q-36 with "no badge", this value blocks a release until it's set. Raise it at the Sprint 1 decision workshop.
- **Existing `backend/.env`:** it lacks the new variables. Without them, local settings answer 503 until the developer copies them over (quickstart step 0). This is the intended fail-closed behaviour.
- **gen.py:** the hard-coded "Q-80–Q-86" label in its check text is stale now that Q-87 exists. The check still passes. Update the wording when `backlog.txt` is touched at Done.

## Complexity Tracking

No constitution violations to justify.
