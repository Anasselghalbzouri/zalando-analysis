# Research: US-059 API foundation

Phase 0 of the plan. Each decision resolves one open point from the Technical Context or the spec's "Recorded tensions". Framework details were checked against the installed Laravel 13.40 / PHP 8.4. Points marked **verify** must be confirmed against the framework source while implementing.

## R-01 Fail closed: startup fails *and* the API answers 503

- **Decision:** both.
  - **Startup check:** in production, the Storefront module's service provider checks all 12 policy values during boot. If any is unset or invalid, it logs the names (critical level) and throws `SettingsIncomplete`.
  - **What a request gets:** the HTTP kernel catches exceptions thrown during bootstrap and passes them to the exception handler.
    - Every `/api/v1` request gets a `503 settings_incomplete` problem body.
    - `/up` and web routes get the framework's plain 503 error page, because `ApiProblem` is an `HttpException` (R-02), so no debug page appears even with `APP_DEBUG=true`.
    - The artisan, queue and scheduler processes also fail to start.
  - **Settings endpoint, every environment:** the endpoint and every later reader of a policy value call `StorefrontSettings`, which throws the same exception when a value is missing. So local and test environments fail closed at the point of use, without blocking the whole app.
- **Rationale:** the startup check conforms to backend-architecture §12 ("a boot-time check fails in production"), which takes precedence over the Phase 06 AC wording. Rendering through the problem renderer keeps story AC-1 ("any error" is problem+json). It also makes the 503 in AC-4 observable, which settles the "boot fails *or* 503" tension in the spec. The failing `/up` makes the container unhealthy, so a bad deploy is visible at once.
- **Alternatives considered:**
  - 503 from the endpoint only: smaller, but deviates from backend §12 without an erratum.
  - A startup failure that crashes the process: gives no problem body, which breaks AC-1.
- **Verify:** that a provider `boot()` exception reaches the registered render callback, with the translator and `app.url` available. Covered by a quickstart scenario.

## R-02 One renderer and one catalogue

- **Decision:**
  - **`App\Support\Problems\Catalogue`:** a constant map `code → status` of the 20 codes: error-format §2 plus erratum E-01.
  - **`ApiProblem` exception:** extends Symfony's `HttpException`, with the status taken from the catalogue. It carries `code`, optional `detail`, `errors` and headers. An unknown code is impossible to construct, and outside `api/*` the framework renders it as a plain HTTP error page.
  - **`ProblemRenderer`:** registered with `withExceptions()->render()` for `api/*` requests. It maps:

| Source | `code` |
|---|---|
| `ApiProblem` (incl. `SettingsIncomplete`), matched **first**, before the generic HTTP-status rules | its own |
| Validation failure | `validation_failed` (R-04) |
| Authentication failure | `unauthenticated` |
| Signature failure (403) | `invalid_signature` |
| Authorization / access denied | `forbidden` |
| Not found (model or route) | `not_found` |
| Method not allowed | `method_not_allowed`, keeping the `Allow` header |
| Post too large | `payload_too_large` |
| Unsupported media type | `unsupported_media_type` |
| CSRF token mismatch / HTTP 419 | `csrf_token_mismatch` |
| Throttled | `rate_limited`, keeping `Retry-After` |
| HTTP 503 (maintenance) | `service_unavailable` |
| HTTP 400 | `bad_request` |
| Anything else (including an HTTP status with no catalogue entry) | `server_error` 500 |

- **Rationale:** later stories raise domain errors with `throw new ApiProblem('out_of_stock')` or a subclass, without touching the renderer (FR-004). Test doubles cover the domain codes, so no Cart or Sales classes are created early (spec, Out of scope).
- **Alternatives considered:** one exception class per code now: speculative (Principle IX).
- **Checked (Laravel 13.40, `Foundation/Exceptions/Handler::render`):** `prepareException()` runs **before** the render callbacks.
  - So the renderer sees the converted forms:
    - authorization → `AccessDeniedHttpException` / `HttpException(status)`
    - model and record not found → `NotFoundHttpException`
    - CSRF → `HttpException(419)`
    - request errors → `BadRequestHttpException`
  - Authentication and validation failures arrive unconverted.
  - `HttpResponseException` is matched only *after* the callbacks, so the renderer returns `null` for it and lets the framework send its response.

## R-03 Problem fields

- **Decision:**
  - `type` = `rtrim(config('app.url'), '/').'/problems/'.str_replace('_', '-', $code)`.
  - `instance` = the request path with a leading `/`, without the query string.
  - `title` and `detail` come from `lang/fr/problems.php`, keyed by code, marked PROPOSED in a file comment. An `ApiProblem` may override `detail`.
  - `request_id` = the request's ID (R-05).
- **Rationale:** follows error-format §1. The domain stays configuration (Q-75). Leaving out the query string keeps personal data out of bodies and logs.
- **Alternatives considered:** `about:blank` types: allowed by RFC 9457, but error-format asks for a stable doc link.

## R-04 Unknown query parameters and field codes

- **Decision:**
  - **`App\Support\Http\ApiFormRequest`:** a base Form Request. Before validation, it flattens the query string to dot keys (`page[number]` becomes `page.number`). Every key that no rule key covers (wildcards included) gets a `missing` rule. It's presence-based and implicit (checked in 13.40), so `?foo=` and a bare `?foo` fail too. `prohibited` would let empty values through.
  - **Endpoints without parameters:** they type-hint `ApiFormRequest` itself, whose `rules()` is empty, so every query parameter is rejected.
  - **Field codes:** a failed validation becomes `errors[field] = [{code, message}]`, with codes from a small rule map: `Required`→`required`, `Unique`→`taken`, `Accepted`→`must_be_accepted`, `Missing`→`unknown_parameter`, a rule object exposing `errorCode()`→that code, anything else→`invalid`.
  - **Message:** the `unknown_parameter` message comes from `lang/fr/problems.php` (PROPOSED copy).
- **Rationale:** the allowed parameters are declared once, in the rules that validate them. This keeps validation at the boundary in Form Requests (Principle III) and matches the field codes of error-format §3.
- **Enforcement:** a route-list test checks that every `/api/v1` route action type-hints an `ApiFormRequest`, which keeps SC-006 true for later endpoints.
- **Alternatives considered:**
  - A middleware with a per-route allow-list: declares parameters twice.
  - Ignoring unknown parameters: contradicts api-contracts §1.

## R-05 Request ID

- **Decision:**
  - **`AssignRequestId` middleware**, prepended to the global stack:
    - it reuses the inbound `X-Request-Id` when it matches `^[A-Za-z0-9._-]{1,128}$`, and otherwise generates `Str::uuid()`
    - it stores the ID on the request and adds it to the log context (`Context::add('request_id', …)`), so every log entry carries it
    - it sets the response header
  - **The renderer** also reads or creates the ID and sets the header, for errors raised before the middleware runs (R-01).
  - **Error logging:** the renderer writes one entry per error response (`api.error`, with `request_id`, status, code and path). It writes the ID into the entry explicitly, not through `Context`, which is empty when boot fails before the middleware runs. Entries are at warning level for 4xx and error level for 5xx. The framework's own report of an unhandled 5xx carries the exception and trace with the same context.
- **Rationale:** global placement covers the 404s of unknown routes, which never reach the route-group middleware. The safe character set protects the logs at a trust boundary.
- **Alternatives considered:**
  - Group middleware: misses unmatched routes.
  - Trusting any inbound value: allows log injection.

## R-06 Server time and the request clock

- **Decision:**
  - **`App\Support\RequestClock`:** a static `now(?Request $request = null): CarbonImmutable` method. It stores `CarbonImmutable::now('UTC')` in the request attribute `request_now` the first time it is called, and returns that value on every later call in the same request.
  - **Why not a scoped singleton:** checked in 13.40, the HTTP kernel never calls `forgetScopedInstances()` (only Octane and queue workers do). So a scoped clock would keep one instant across several requests in the same test or process.
  - **`AddServerTime` middleware** on the `api` group: for a 2xx `JsonResponse` whose payload is an object, it sets `meta.server_time` to `RequestClock::now()->toIso8601ZuluString()` (second precision, `Z`). Other responses are untouched.
- **Rationale:** ADR-009 asks for one "now" per request. US-015 (T-015-B2) and US-053 reuse the same clock instead of adding a second one. `travelTo()` in tests freezes it.
- **Alternatives considered:**
  - `now()` at response time: not the same instant that the request's rules use.
  - Millisecond precision: other timestamps in the contract are second-precision.

## R-07 Malformed JSON and media type

- **Decision:** an `EnsureJsonBody` middleware on the `api` group, applied only when the body is not empty:
  - 415 `unsupported_media_type` when the content type isn't JSON
  - 400 `bad_request` when it is JSON but `json_validate()` fails
- **Rationale:** the framework silently treats invalid JSON as an empty body, so the 400 in the catalogue would never happen. The 415 follows api-contracts §1 (Format: `application/json`) and erratum E-01. 413 needs nothing new, because the framework's global post-size check already raises it.

## R-08 Route groups by auth code

- **Decision:**
  - **Groups:** four named middleware groups are declared in `bootstrap/app.php`: `access.session` (S), `access.catalog` (C), `access.public` (P) and `access.guest` (G). They are empty for now.
  - **Who fills them:** the authentication stories put `auth:sanctum` / guest guards in S and G. US-005 puts `catalog.access` in C and adds the "exactly one group" route-list test.
  - **Module routes:** `routes/api.php` includes `app/Modules/*/routes.php`. The Storefront module puts the settings route in `access.public`.
- **Rationale:** later stories only fill a group; no route needs to move. No authentication package is installed here (spec, Out of scope).
- **Alternatives considered:** prefixes or route-name conventions: harder to check in a route-list test than a middleware-group name.
- **Verify:** that an empty middleware group is accepted by the router.

## R-09 Policy configuration

- **Decision:**
  - **`config/storefront.php`:** each value is read with `env('STOREFRONT_…')` and has **no default**, except `listing_page_size` (60, CONFIRMED) and the two switches (`false`). Values are stored raw.
  - **`StorefrontSettings`:**
    - validates every value (FR-017)
    - exposes `missing(): list<string>` (setting names) and `toPublicArray()`, which throws `SettingsIncomplete` when `missing()` isn't empty
    - takes integers with `FILTER_VALIDATE_INT` and the minimums from FR-017, and checks the timezone against `DateTimeZone::listIdentifiers()`
  - **`last_hours_badge_threshold_hours`:** counts as required (the spec's fail-closed reading, Q-36 open). It is exposed as an integer.
  - **The backend-only switches** are not added (spec, Out of scope).
- **Rationale:** no silent defaults (Principle VII), and one validation point.
- **Example environment file:** `backend/.env.example` gets a "Storefront policy" and a "Feature switches" block. Every line is commented `DEV PLACEHOLDER, not a PO decision (Q-xx)`, and the values are taken from the system-overview §7 reference observations. Tests set config explicitly and don't depend on `.env`.

## R-10 Tests

- **Decision:**
  - **Framework and location:** PHPUnit 12 feature tests in `backend/tests/Feature/Api/`, plus unit tests in `backend/tests/Unit/`, run with `docker compose exec app php artisan test`.
  - **Catalogue codes:** one data-provider test over all 20 codes, using test-only routes that throw the matching exception.
  - **Production check:** the environment is switched to `production` inside the test.
  - **Database:** in-memory SQLite stays acceptable, because this feature touches no database constraint (Principle V).
- **Rationale:** T-059-BT1 ("renderer tests for every catalogue code"), and every acceptance scenario maps to a test.

## No new dependencies

Everything uses the framework already installed: the exception configuration, middleware groups, Form Requests, `Context`, `Str::uuid()`, Carbon and `json_validate()`.
