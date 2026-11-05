# Quickstart: validate US-059

Run from the repository root with the stack up (`docker compose up -d --wait`). Every command runs in the containers (constitution, "Tools run inside the containers").

## 0. Prerequisites

- **Policy variables:** `backend/.env` was created from the old example, so it has none of the new ones. Copy the "Storefront policy" and "Feature switches" blocks from `backend/.env.example` into `backend/.env`, then run `docker compose exec app php artisan config:clear`.
- **Shell:** the `curl` calls use Git Bash. The base URL is `http://localhost:8080`.

## 1. Automated tests

```bash
docker compose exec app php artisan test --filter=Api
```

**Expected:** all pass. These cover:
- 20 catalogue codes (T-059-BT1)
- unknown parameters
- request ID reuse and generation
- `meta.server_time` with a frozen clock
- the settings shape and `no-store`
- 503 for each of the 11 undecided values left unset, and for an invalid value of each of the 12, `listing_page_size` included (SC-004)
- the production startup check
- the route-list check that every API action uses an `ApiFormRequest`

## 2. Manual scenarios

| # | Scenario | Command | Expected |
|---|---|---|---|
| 1 | Settings, success (US 2, AC-1) | `curl -si localhost:8080/api/v1/storefront/settings` | 200. `Cache-Control` contains `no-store`. `X-Request-Id` is set. Body has the shape of contracts §4 with `meta.server_time` ending in `Z` |
| 2 | Unknown parameter (story AC-5) | `curl -si 'localhost:8080/api/v1/storefront/settings?foo=1'`, and again with `?foo=` and `?foo` | 422 `application/problem+json`, `code: validation_failed`, `errors.foo[0].code: unknown_parameter` |
| 3 | Request ID echoed (story AC-2) | `curl -si -H 'X-Request-Id: qa-123' 'localhost:8080/api/v1/nope'` | 404 `not_found`. The header and the body `request_id` are `qa-123`. `docker compose exec app grep qa-123 storage/logs/laravel.log` finds the `api.error` entry |
| 4 | Hostile request ID | `curl -si -H 'X-Request-Id: bad id<script>' localhost:8080/api/v1/storefront/settings` | A generated UUID in `X-Request-Id`, not the sent value |
| 5 | Wrong method (E-01) | `curl -si -X DELETE localhost:8080/api/v1/storefront/settings` | 405 `method_not_allowed` with an `Allow` header |
| 6 | Malformed JSON / wrong media type | No write endpoint exists yet. The automated suite posts to a test-only route | 400 `bad_request` for `{` with a JSON content type, and 415 `unsupported_media_type` for `text/plain`. (On a real route the method is matched first, so a POST to the settings route answers 405.) |
| 7 | No internals with debug on (story AC-1) | APP_DEBUG is `true` locally; trigger a 500 through the test route in the test suite | Body `code: server_error`, no `trace`/`file`/`line` keys |
| 8 | Missing value, local (story AC-4) | Comment out `STOREFRONT_RESERVATION_MINUTES` in `backend/.env`, then run `config:clear` and scenario 1 | 503 `settings_incomplete`. Other `/api/v1` routes still answer. Restore the line afterwards |
| 9 | Missing value, production (story AC-4, research R-01) | Same, plus `APP_ENV=production` in `backend/.env` (config is not cached after step 0, and artisan itself won't start now), then `curl -si localhost:8080/up` | `/up` answers a plain 503 page (no debug trace). `curl -si localhost:8080/api/v1/storefront/settings` answers the 503 `settings_incomplete` problem body. `docker compose exec app php artisan about` fails. The log names `reservation_minutes`. **Restore `APP_ENV=local`** and the variable |

Scenario 9 is the check for the "verify" point in research R-01. If the 503 problem body doesn't appear, fix that before the story is Done.

## 3. Done checks (Definition of Done, step 8)

```bash
python -I tools/phase06/gen.py .
```

**Expected:** every check passes, after `tools/phase06/backlog.txt` marks US-059 done.
