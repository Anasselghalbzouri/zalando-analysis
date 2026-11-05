---
description: "Task list for US-059 API foundation"
---

# Tasks: US-059 API foundation

**Input**: Design documents from `specs/001-us-059-api-foundation/` (plan.md, spec.md, research.md, data-model.md, contracts/api-foundation.md, quickstart.md)

**Tests**: **required**. Constitution Principle V says every acceptance criterion gets an automated test written first, and T-059-BT1 asks for renderer tests for every catalogue code. Write each test task before its implementation task and see it fail.

**Organization**: tasks are grouped by the spec's user stories. US1 = errors and request ID (P1), US2 = fail-closed settings (P2), US3 = server time (P3).

**Conventions for every task:**
- Paths are relative to the repository root. PHP lives in `backend/`.
- Commands run in the containers: `docker compose exec app php artisan test --filter=<Name>`, and `docker compose exec app ./vendor/bin/pint <path>`.
- No new Composer or npm package (plan, "No new dependencies").
- Phase 06 task mapping: T-059-B1 → T001–T002, T006–T012, T018–T020, T026–T031, T036–T037; T-059-BT1 → T003–T005, T013–T017, T022–T025, T034–T035; T-059-I1 → T032.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: can run in parallel (different files, no dependency on an unfinished task)
- **[Story]**: US1, US2 or US3 from spec.md

---

## Phase 1: Setup

**Purpose**: module skeleton and route wiring. No behaviour yet.

- [ ] T001 Create `backend/app/Modules/Storefront/StorefrontServiceProvider.php` (namespace `App\Modules\Storefront`, extends `Illuminate\Support\ServiceProvider`, empty `register()` and `boot()`). Register it in `backend/bootstrap/providers.php`.
- [ ] T002 In `backend/routes/api.php`, keep the header comment and add `foreach (glob(app_path('Modules/*/routes.php')) ?: [] as $file) { require $file; }`, sorted with `sort()` for a stable order. Create `backend/app/Modules/Storefront/routes.php` with only `<?php` and a comment that the settings route comes in T033.

---

## Phase 2: Foundational (blocking prerequisites)

**Purpose**: the problem shape, the catalogue, the request ID helper, the base Form Request and the route groups. Every story needs them.

**⚠️ CRITICAL**: no user story work starts until this phase is complete.

### Tests first

- [ ] T003 [P] Write `backend/tests/Unit/Problems/CatalogueTest.php`:
  - A data provider of exactly these 20 `code => status` pairs must match `Catalogue::status($code)`: `bad_request` 400, `unauthenticated` 401, `forbidden` 403, `invalid_signature` 403, `not_found` 404, `method_not_allowed` 405, `out_of_stock` 409, `reservation_limit_reached` 409, `campaign_not_live` 409, `campaign_ended` 410, `payload_too_large` 413, `unsupported_media_type` 415, `csrf_token_mismatch` 419, `validation_failed` 422, `invalid_credentials` 422, `invalid_token` 422, `rate_limited` 429, `server_error` 500, `settings_incomplete` 503, `service_unavailable` 503.
  - `count(Catalogue::CODES) === 20`.
  - `Catalogue::status('nope')` throws `InvalidArgumentException`.
- [ ] T004 [P] Write `backend/tests/Unit/Http/RequestIdTest.php`. `RequestId::for(Request)`:
  - returns the inbound `X-Request-Id` when it matches `^[A-Za-z0-9._-]{1,128}$`, e.g. `qa-123`, or 128 × `a`
  - generates a UUID (`Str::isUuid`) for a missing value, an empty value, 129 characters, `bad id<script>` or a value containing `\n`
  - returns the same value on a second call for the same request (stored in the request attribute `request_id`)
- [ ] T005 [P] Write `backend/tests/Feature/Api/UnknownParameterTest.php`. In `setUp()`, register test-only routes with `Route::prefix('api/v1/_test')->middleware(['api', 'access.public'])`:
  - `GET params-none`: closure with an `App\Support\Http\ApiFormRequest $request` parameter, returns `['data' => []]`
  - `GET params-page`: same, typed with the test Form Request `Tests\Support\PageRequest extends ApiFormRequest`, with `rules()` = `['page' => 'array', 'page.number' => 'integer|min:1', 'page.size' => 'integer|min:1']`. Create it in `backend/tests/Support/PageRequest.php`.
  - Assert:
    - `params-none?foo=1`, `?foo=` and `?foo` → 422, `Content-Type` `application/problem+json`, `code` `validation_failed`, `errors.foo.0.code` `unknown_parameter`, and a non-empty `errors.foo.0.message`
    - `params-page?page[number]=2` → 200
    - `params-page?page[bogus]=1` → 422 with key `errors.page.bogus`. Use `assertJsonPath('errors', fn ($e) => isset($e['page.bogus']))`, because the key itself contains a dot.
    - `params-page?page[number]=x` → 422 with `errors.page.number.0.code` `invalid`

### Implementation

- [ ] T006 [P] Create `backend/app/Support/Problems/Catalogue.php` (`final class Catalogue`):
  - `public const CODES = [...]`: the 20 pairs from T003, in error-format §2 order
  - `public static function status(string $code): int`: throws `InvalidArgumentException` for an unknown code
  - a doc comment citing error-format §2 and erratum E-01
- [ ] T007 [P] Create `backend/app/Support/Http/RequestId.php` (`final class RequestId`):
  - `public const HEADER = 'X-Request-Id'`
  - `public static function for(Request $request): string`, per research R-05: reuse the header only if `preg_match('/^[A-Za-z0-9._-]{1,128}$/D', $value)`, else `(string) Str::uuid()`; cache the value in `$request->attributes` under `request_id`
- [ ] T008 Create `backend/app/Support/Problems/ApiProblem.php` (depends on T006). `class ApiProblem extends Symfony\Component\HttpKernel\Exception\HttpException`:
  - `__construct(private readonly string $problemCode, private readonly ?string $detail = null, private readonly array $errors = [], array $headers = [], ?Throwable $previous = null)`
  - calls `parent::__construct(Catalogue::status($problemCode), $detail ?? '', $previous, $headers)`
  - getters `problemCode(): string`, `detail(): ?string`, `errors(): array`
  - **Naming:** don't name anything `$code` or `getCode()`, because the parent already uses those for an int
- [ ] T009 [P] Create `backend/lang/fr/problems.php`:
  - returns `['titles' => [code => fr], 'details' => [code => fr], 'fields' => ['unknown_parameter' => 'Ce paramètre n’est pas accepté.', 'invalid' => 'La valeur est invalide.', 'required' => 'Ce champ est obligatoire.']]` for all 20 codes
  - top comment: `// PROPOSED wording (error-format §3): a PO decision unless observed. Change text freely; never change keys.`
  - use `Données invalides` / `La requête contient des erreurs.` for `validation_failed` and `Article indisponible` for `out_of_stock` (error-format §4); write short neutral French for the rest
- [ ] T010 Create `backend/app/Support/Problems/ProblemRenderer.php` (depends on T006–T009). `final class ProblemRenderer`, with `public function __invoke(Throwable $e, Request $request): ?Response`:
  - **Scope:** returns `null` unless `$request->is('api/*')`; returns `null` for `Illuminate\Http\Exceptions\HttpResponseException`.
  - **Mapping:** maps `$e` to `[code, detail, errors, headers]` in this order (research R-02; `prepareException()` has already run):
    1. `ApiProblem` → `problemCode()`, `detail()`, `errors()`, `getHeaders()`
    2. `ValidationException` → `validation_failed`, with errors from `$e->validator`: for each field of `errors()->messages()`, zip the rule names of `failed()[$field]` with the messages; rule→code `Required`→`required`, `Unique`→`taken`, `Accepted`→`must_be_accepted`, `Missing`→`unknown_parameter`, else `invalid`
    3. `AuthenticationException` → `unauthenticated`
    4. `InvalidSignatureException` → `invalid_signature`
    5. `NotFoundHttpException` → `not_found`
    6. `MethodNotAllowedHttpException` → `method_not_allowed` (keep its `Allow` header)
    7. `PostTooLargeException` → `payload_too_large`
    8. `UnsupportedMediaTypeHttpException` → `unsupported_media_type`
    9. `AccessDeniedHttpException` → `forbidden`
    10. `ThrottleRequestsException` → `rate_limited` (keep `Retry-After`)
    11. any other `HttpExceptionInterface` → by status: 400 `bad_request`, 401 `unauthenticated`, 403 `forbidden`, 404 `not_found`, 405 `method_not_allowed`, 413 `payload_too_large`, 415 `unsupported_media_type`, 419 `csrf_token_mismatch`, 429 `rate_limited`, 503 `service_unavailable`, else `server_error`
    12. anything else → `server_error`
  - **Headers:** keep `getHeaders()` from HTTP exceptions.
  - **Body:** `type` = `rtrim(config('app.url'), '/').'/problems/'.str_replace('_', '-', $code)`; `title` = `__('problems.titles.'.$code)`; `status` = `Catalogue::status($code)`; `detail` = the exception's detail if it's an `ApiProblem` with a non-null detail, else `__('problems.details.'.$code)`; `instance` = `'/'.ltrim($request->path(), '/')`; `code`; `request_id` = `RequestId::for($request)`; `errors` (object) **only** when the status is 422. Never use exception messages from other classes in the body.
  - **Response:** `response()->json($body, $status, $headers + ['X-Request-Id' => $id], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)` with `Content-Type: application/problem+json`.
  - **Logging:** `Log::warning` (status < 500) or `Log::error` (≥ 500) with message `api.error` and context `['request_id' => $id, 'status' => …, 'code' => …, 'path' => $request->path()]`. Write `request_id` explicitly (research R-05). No query string, no body.
- [ ] T011 Register the renderer and the route groups in `backend/bootstrap/app.php` (depends on T010):
  - In `withExceptions`, add `$exceptions->render(new \App\Support\Problems\ProblemRenderer)` and keep the existing `shouldRenderJsonWhen`.
  - In `withMiddleware`, add four empty groups `$middleware->group('access.session', [])`, `access.catalog`, `access.public`, `access.guest`, with one comment line each: `S, filled by US-001/US-002`, `C, filled by US-005 (catalog.access)`, `P, no guard`, `G, filled by US-001/US-002`.
  - Run `docker compose exec app php artisan route:list` to confirm that empty groups are accepted (research R-08 "verify").
- [ ] T012 Create `backend/app/Support/Http/ApiFormRequest.php` (depends on T011). `class ApiFormRequest extends FormRequest`, with `authorize()` true and `rules()` `[]`. Override `withValidator(Validator $validator)`:
  - flatten `Arr::dot($this->query())`
  - a key is covered if, for some key `$r` of `$this->rules()`, `Str::is($r, $key)` or `str_starts_with($key, $r.'.')`
  - for every uncovered key, `$validator->addRules([$key => ['missing']])` and `$validator->setCustomMessages([$key.'.missing' => __('problems.fields.unknown_parameter')])`
  - a doc comment: `missing` is presence-based and implicit, so `?foo=` and `?foo` fail too (research R-04)
  - **Check:** T003, T004 and T005 pass

**Checkpoint**: shape, catalogue and parameter rejection are in place. Stories can start.

---

## Phase 3: User Story 1 - Every API error has one predictable shape and can be traced (Priority: P1) 🎯 MVP

**Goal**: every `/api/v1` error is a problem body with a correct code and status, no internals, a request ID in the header, the body and the log. Malformed JSON and wrong media types are answered too.

**Independent Test**: `docker compose exec app php artisan test --filter='ProblemRendererTest|RequestIdHeaderTest|JsonBodyTest'`, plus quickstart scenarios 3, 4 and 5.

### Tests for User Story 1 (write first, see them fail) ⚠️

- [ ] T013 [P] [US1] Write `backend/tests/Feature/Api/ProblemRendererTest.php`. In `setUp()`, register `GET api/v1/_test/raise/{case}` (middleware `api`, `access.public`) that throws by case:
  - `bad_request` → `new BadRequestHttpException`
  - `unauthenticated` → `new AuthenticationException`
  - `forbidden` → `new AuthorizationException`
  - `invalid_signature` → `new InvalidSignatureException`
  - `not_found` → `(new ModelNotFoundException)->setModel(User::class)`
  - `out_of_stock`, `reservation_limit_reached`, `campaign_not_live`, `campaign_ended`, `invalid_credentials`, `invalid_token`, `settings_incomplete` → `new ApiProblem($case)`
  - `payload_too_large` → `new PostTooLargeException`
  - `unsupported_media_type` → `new UnsupportedMediaTypeHttpException`
  - `csrf_token_mismatch` → `new TokenMismatchException`
  - `validation_failed` → `ValidationException::withMessages(['email' => 'x'])`
  - `rate_limited` → `new ThrottleRequestsException(headers: ['Retry-After' => 30])`
  - `service_unavailable` → `new HttpException(503)`
  - `server_error` → `new RuntimeException('SELECT * FROM secret')`

  Test the 20th code (`method_not_allowed`) separately with `DELETE` on `api/v1/_test/raise/x`. A data provider of 19 cases, plus that one, asserts:
  - the catalogue status
  - `Content-Type` starts with `application/problem+json`
  - the exact key set `type,title,status,detail,instance,code,request_id`, plus `errors` only for `validation_failed`
  - `type` ends with `/problems/` + code with `-`
  - `instance` = `/api/v1/_test/raise/<case>`
  - the body `request_id` equals the `X-Request-Id` header

  Also assert: `Allow` contains `GET` on the 405; `Retry-After: 30` on the 429; `errors.email.0.code` = `invalid` on the 422. Finally, `GET api/v1/does-not-exist` → 404 `not_found` with `request_id`.
- [ ] T014 [P] [US1] Write `backend/tests/Feature/Api/NoInternalsTest.php`. With `config(['app.debug' => true])`, the `server_error` case (route as in T013, registered in this test's `setUp()`) gives a body that has no keys `exception`, `file`, `line`, `trace`, and whose JSON string doesn't contain `SELECT`, `RuntimeException` or `.php`. With `Log::spy()`, assert `Log::shouldHaveReceived('error')` with `'api.error'` and a context whose `request_id` equals the response header.
- [ ] T015 [P] [US1] Write `backend/tests/Feature/Api/RequestIdHeaderTest.php`. Register `GET api/v1/_test/ok` (`api`, `access.public`, an `ApiFormRequest` parameter, returns `['data' => []]`) in `setUp()`. Assert:
  - with `X-Request-Id: qa-123`, `/api/v1/_test/ok` → 200 with header `qa-123`
  - without a header → a UUID header
  - with `bad id<script>` → a UUID header, not the sent value
  - with `X-Request-Id: qa-404` and `Log::spy()`, `/api/v1/nope` → 404, body `request_id` `qa-404`, and `Log::shouldHaveReceived('warning')` with `'api.error'` and context `request_id` `qa-404`, `status` 404, `code` `not_found`, `path` `api/v1/nope`
  - `Context::get('request_id')` is `qa-123` inside the route closure (assert it by returning it in `data`)
- [ ] T016 [P] [US1] Write `backend/tests/Feature/Api/JsonBodyTest.php`. Register `POST api/v1/_test/echo` (`api`, `access.public`, `ApiFormRequest`, returns `['data' => []]`). Assert:
  - body `{` with `Content-Type: application/json` → 400 `bad_request`
  - body `x` with `Content-Type: text/plain` → 415 `unsupported_media_type`
  - an empty body with no content type → 200
  - valid JSON `{}` → 200

  Use `$this->call('POST', $uri, [], [], [], ['CONTENT_TYPE' => …], $raw)`.
- [ ] T017 [P] [US1] Write `backend/tests/Feature/Api/ApiRouteConventionsTest.php`. For every route in `Route::getRoutes()` whose `uri()` starts with `api/v1`, reflect the action (`$route->getActionName()` controller method, or `$route->getAction('uses')` closure). Assert that at least one parameter's type `is_a(…, ApiFormRequest::class, true)`. The failure message names the URI (research R-04, SC-006).

### Implementation for User Story 1

- [ ] T018 [US1] Create `backend/app/Support/Http/Middleware/AssignRequestId.php`. `handle()`: `$id = RequestId::for($request)`, then `Context::add('request_id', $id)`, then `$response = $next($request)`, then `$response->headers->set(RequestId::HEADER, $id)`. Prepend it globally in `backend/bootstrap/app.php` with `$middleware->prepend(AssignRequestId::class)` (research R-05).
- [ ] T019 [US1] Create `backend/app/Support/Http/Middleware/EnsureJsonBody.php` (research R-07). If `$request->getContent() !== ''`:
  - throw `new ApiProblem('unsupported_media_type')` when `! $request->isJson()`
  - throw `new ApiProblem('bad_request')` when `! json_validate($request->getContent())`

  Append it to the `api` group in `backend/bootstrap/app.php` with `$middleware->appendToGroup('api', EnsureJsonBody::class)`.
- [ ] T020 [US1] Run T013–T017 and fix `ProblemRenderer` (T010) until they all pass. In particular, check that `TokenMismatchException` arrives as `HttpException(419)` and `AuthorizationException` as `AccessDeniedHttpException` (research R-02, checked behaviour).
- [ ] T021 [US1] Run `docker compose exec app ./vendor/bin/pint app/Support bootstrap tests` and `docker compose exec app php artisan test`. All green.

**Checkpoint**: US1 is complete and testable on its own (story AC-1, AC-2, AC-5; AC-NFR-OBS-01-1; FR-001–FR-012).

---

## Phase 4: User Story 2 - The SPA reads policy settings that are never silently defaulted (Priority: P2)

**Goal**: `GET /api/v1/storefront/settings` in the api-contracts §3 shape. It fails closed when a value is unset or invalid, and in production startup fails.

**Independent Test**: `docker compose exec app php artisan test --filter='StorefrontSettings'`, plus quickstart scenarios 1, 8 and 9.

### Tests for User Story 2 (write first, see them fail) ⚠️

- [ ] T022 [P] [US2] Create the test helper `backend/tests/Support/StorefrontConfig.php` (trait `StorefrontConfig`). Its `completeStorefrontConfig(): array` returns a valid `storefront` config:
  - `reservation_minutes` 20, `reservation_timer_mode` `per_item`, `max_quantity_per_article` 1, `low_stock_threshold` 3, `return_period_days` 30, `packaging_fee_cents` 390, `display_timezone` `Europe/Paris`, `upcoming_relative_threshold_minutes` 120, `upcoming_horizon_hours` 24, `last_hours_badge_threshold_hours` 24, `listing_page_size` 60, `catalog_access` `members`
  - `features` => `hide_add_button_after_add` false, `last_hours_badge` false

  `useStorefrontConfig(array $overrides = [])` sets `config(['storefront' => array_replace_recursive(...)])`. Comment: test values only, not PO decisions.
- [ ] T023 [P] [US2] Write `backend/tests/Unit/Storefront/StorefrontSettingsValidationTest.php` (extends `Tests\TestCase`, uses T022). `missing()`:
  - is `[]` for the complete config
  - for each of the 11 keys other than `listing_page_size` set to `null`, returns exactly `[that key]`
  - for each invalid value, returns that key: `reservation_minutes` `0`/`-1`/`'abc'`/`'1.5'`; `max_quantity_per_article` `0`; `listing_page_size` `0`; `low_stock_threshold` `-1`; `return_period_days` `'x'`; `packaging_fee_cents` `-1`; `upcoming_relative_threshold_minutes` `-1`; `upcoming_horizon_hours` `'x'`; `last_hours_badge_threshold_hours` `-1`; `reservation_timer_mode` `'both'`; `catalog_access` `'everyone'`; `display_timezone` `'Paris'` and `'GMT+1'`
  - accepts numeric strings such as `'20'` (env values are strings)
  - **config file defaults:** `require base_path('config/storefront.php')` with `STOREFRONT_LISTING_PAGE_SIZE`, `FEATURE_HIDE_ADD_BUTTON_AFTER_ADD` and `FEATURE_LAST_HOURS_BADGE` removed from the environment (`putenv('NAME')`, `unset($_ENV['NAME'], $_SERVER['NAME'])`) returns `listing_page_size` 60 and both switches `false`. Every other key is `null` when its variable is removed
- [ ] T024 [P] [US2] Write `backend/tests/Feature/Api/StorefrontSettingsTest.php` (uses T022):
  - **Complete config:** `GET /api/v1/storefront/settings` → 200, `Cache-Control` contains `no-store`, and `data` equals exactly (`assertExactJson` on `data` via `json('data')`):
    `{reservation_minutes:20, reservation_timer_mode:'per_item', max_quantity_per_article:1, low_stock_threshold:3, return_period_days:30, packaging_fee:{amount_cents:390, currency:'EUR'}, display_timezone:'Europe/Paris', upcoming_relative_threshold_minutes:120, listing_page_size:60, catalog_access:'members', features:{hide_add_button_after_add:false, last_hours_badge:false, last_hours_badge_threshold_hours:24}}`
    All are integers, not strings, and there is no `upcoming_horizon_hours` key (FR-020).
  - **Fail closed:** a data provider of the 11 nullable keys, plus one invalid value per key for all 12, gives 503 `settings_incomplete` problem bodies. With `Log::spy()`, `Log::shouldHaveReceived('critical')` with `'storefront.settings_incomplete'` and a context `missing` that contains the key.
  - **Unknown parameter:** `?foo=1` → 422 `unknown_parameter`.
  - **No session needed:** the route is in the `access.public` group (`Route::getRoutes()->getByName('storefront.settings')->gatherMiddleware()` contains `access.public`).
- [ ] T025 [P] [US2] Write `backend/tests/Feature/Api/StorefrontStartupCheckTest.php` (uses T022):
  - With `$this->app['env'] = 'production'` and `reservation_minutes` null, `(new StorefrontServiceProvider($this->app))->boot()` throws `SettingsIncomplete`, and `Log::shouldHaveReceived('critical')` names `reservation_minutes`.
  - With the complete config in production, it doesn't throw.
  - With an incomplete config in `testing`, it doesn't throw.
  - Restore `env` to `testing` in `tearDown()`.

### Implementation for User Story 2

- [ ] T026 [P] [US2] Create `backend/config/storefront.php` (research R-09, data-model "Policy settings"). It returns:
  - each of the 11 PO values as `env('STOREFRONT_<UPPER_KEY>')` with **no default**
  - `listing_page_size` as `env('STOREFRONT_LISTING_PAGE_SIZE', 60)`, with the comment `CONFIRMED 60 (BR-06-07)`
  - `features` => `hide_add_button_after_add` `(bool) env('FEATURE_HIDE_ADD_BUTTON_AFTER_ADD', false)` and `last_hours_badge` `(bool) env('FEATURE_LAST_HOURS_BADGE', false)`, with the comment `HYPOTHESIS behaviour, off until PO validation (Principle VII)`
  - a comment with the Q-ID next to each key (data-model table)
- [ ] T027 [P] [US2] Create `backend/app/Modules/Storefront/Exceptions/SettingsIncomplete.php`. `final class SettingsIncomplete extends ApiProblem`, with `__construct(public readonly array $missing)` calling `parent::__construct('settings_incomplete')`. The missing names never go into the body.
- [ ] T028 [US2] Create `backend/app/Modules/Storefront/Services/StorefrontSettings.php` (depends on T026, T027). `final class StorefrontSettings`:
  - `public function missing(): array` validates `config('storefront')` verbatim per data-model.md:
    - `reservation_minutes` int ≥ 1, `reservation_timer_mode` `per_item` | `shared`, `max_quantity_per_article` int ≥ 1
    - `low_stock_threshold`, `return_period_days`, `packaging_fee_cents`, `upcoming_relative_threshold_minutes`, `upcoming_horizon_hours` and `last_hours_badge_threshold_hours` int ≥ 0
    - `display_timezone` in `DateTimeZone::listIdentifiers()`, `listing_page_size` int ≥ 1, `catalog_access` `members` | `public`
    - integers through `filter_var($v, FILTER_VALIDATE_INT, ['options' => ['min_range' => N]])` (not `false`, and not null)
  - `public function assertComplete(): void`: if `missing()` isn't empty, `Log::critical('storefront.settings_incomplete', ['missing' => $missing])`, then throw `new SettingsIncomplete($missing)`
  - `public function toPublicArray(): array` calls `assertComplete()`, then returns the contracts §4 shape with integers cast, `packaging_fee` = `['amount_cents' => (int) …, 'currency' => 'EUR']`, and no `upcoming_horizon_hours`
- [ ] T029 [US2] Create `backend/app/Modules/Storefront/Http/Controllers/StorefrontSettingsController.php`. `__invoke(ApiFormRequest $request, StorefrontSettings $settings)` returns `response()->json(['data' => $settings->toPublicArray()])->header('Cache-Control', 'no-store')`. No logic beyond that (thin controller, Principle III).
- [ ] T030 [US2] Fill `backend/app/Modules/Storefront/routes.php`: `Route::middleware('access.public')->get('storefront/settings', StorefrontSettingsController::class)->name('storefront.settings');`. The file is required from `routes/api.php`, which `withRouting(api:)` already wraps in the `api` group and the `api/v1` prefix, so don't repeat either. Confirm with `docker compose exec app php artisan route:list --path=api/v1` that the URI is `api/v1/storefront/settings` and that the middleware is `api, access.public`.
- [ ] T031 [US2] Implement the production startup check in `backend/app/Modules/Storefront/StorefrontServiceProvider.php`: in `boot()`, `if ($this->app->isProduction()) { $this->app->make(StorefrontSettings::class)->assertComplete(); }`, with a comment citing backend-architecture §12 and research R-01.
- [ ] T032 [US2] Update `backend/.env.example` (T-059-I1, docker-architecture §5). After the logging block, add:
  - **`# Storefront policy`:** the 12 `STOREFRONT_*` variables with the values from T022, each preceded by `# DEV PLACEHOLDER, not a PO decision (Q-xx)` (Q-IDs from data-model.md; `listing_page_size` is commented `CONFIRMED 60 (BR-06-07)`)
  - **`# Feature switches`:** `FEATURE_HIDE_ADD_BUTTON_AFTER_ADD=false` and `FEATURE_LAST_HOURS_BADGE=false`, commented `off until PO validation`

  No secrets.
- [ ] T033 [US2] Run T023–T025 (`backend/tests/Unit/Storefront/`, `backend/tests/Feature/Api/StorefrontS*Test.php`) and the full suite. All green. Then run quickstart step 0 and scenarios 1 and 8 against the running stack.

**Checkpoint**: US2 is complete (story AC-4; FR-015–FR-022). The endpoint works and fails closed.

---

## Phase 5: User Story 3 - Every success response states the server's time (Priority: P3)

**Goal**: `meta.server_time` (UTC, `Z`) on every 2xx JSON response, equal to the request's single "now".

**Independent Test**: `docker compose exec app php artisan test --filter='ServerTime|RequestClock'`.

### Tests for User Story 3 (write first, see them fail) ⚠️

- [ ] T034 [P] [US3] Write `backend/tests/Unit/RequestClockTest.php`:
  - `$this->travelTo('2026-11-05 12:00:00')`, then `RequestClock::now($req)` equals that instant in UTC
  - after `$this->travel(5)->seconds()`, a second call with the **same** request returns the same instant
  - a **new** `Request` returns the new time (research R-06)
- [ ] T035 [P] [US3] Write `backend/tests/Feature/Api/ServerTimeTest.php` (uses T022 for the settings endpoint). With `travelTo('2026-11-05T12:00:00Z')`:
  - `GET /api/v1/storefront/settings` has `meta.server_time` === `2026-11-05T12:00:00Z`, and `data` is unchanged
  - a test route `GET api/v1/_test/meta` returning `['data' => [], 'meta' => ['campaign_count' => 2]]` keeps `campaign_count` and adds `server_time`
  - a test route returning `response()->noContent()` has no body
  - a 422 problem response has no `meta` key
  - two requests in one test, with `travel(1)->minutes()` between them, return different `server_time` values

### Implementation for User Story 3

- [ ] T036 [P] [US3] Create `backend/app/Support/RequestClock.php`. `final class RequestClock`, with `public static function now(?Request $request = null): CarbonImmutable`: `$request ??= request()`; return `$request->attributes->get('request_now')` if set; else store and return `CarbonImmutable::now('UTC')`. Doc comment: ADR-009 "one now per request", reused by US-015 T-015-B2 and US-053.
- [ ] T037 [US3] Create `backend/app/Support/Http/Middleware/AddServerTime.php` (depends on T036). After `$next`, if the response `instanceof JsonResponse`, its status is 2xx, and `getData(true)` is an array with string keys, set `$data['meta']['server_time'] = RequestClock::now($request)->toIso8601ZuluString()` and call `setData($data)`. Leave other responses untouched. Append it to the `api` group in `backend/bootstrap/app.php` with `appendToGroup('api', AddServerTime::class)`.
- [ ] T038 [US3] Run T034–T035 (`backend/tests/Unit/RequestClockTest.php`, `backend/tests/Feature/Api/ServerTimeTest.php`) and the full suite. All green.

**Checkpoint**: all three stories work (story AC-3; FR-013, FR-014).

---

## Phase 6: Polish & cross-cutting

- [ ] T039 Run `docker compose exec app ./vendor/bin/pint` on all changed PHP in `backend/` and `docker compose exec app php artisan test`. Report the real output, with any failures verbatim (Principle V).
- [ ] T040 Run every scenario 1–9 in `specs/001-us-059-api-foundation/quickstart.md` against the stack. Scenario 9 (production startup → `/up` plain 503 and API 503 problem body) settles research R-01's "verify". If it fails, apply the plan's fallback and record it in research.md. **Restore `APP_ENV=local`** afterwards.
- [ ] T041 [P] Update the docs:
  - `docs/phase-07/04-configuration-guide.md`: the "Not configured yet" line becomes "Configured in US-059: `config/storefront.php`, `STOREFRONT_*`, `FEATURE_HIDE_ADD_BUTTON_AFTER_ADD`, `FEATURE_LAST_HOURS_BADGE`; the backend-only switches come with their stories"
  - `docs/phase-07/06-phase-07-summary.md`: known issues K-2 and K-7 become "resolved by US-059"
- [ ] T042 At Definition of Done, after PO acceptance:
  - mark US-059 done in `tools/phase06/backlog.txt`
  - update the check label `Q-80–Q-86` in `tools/phase06/gen.py` to `Q-80–Q-87`
  - run `python -I tools/phase06/gen.py .`; all checks must pass

---

## Dependencies & Execution Order

### Phase dependencies

- **Setup (T001–T002)**: none.
- **Foundational (T003–T012)**: after Setup. Blocks every story.
- **US1 (T013–T021)**: after Foundational.
- **US2 (T022–T033)**: after Foundational. It uses `ApiFormRequest` (T012) and the renderer (T010) only, so it doesn't need US1's middleware. Its 405/request-ID behaviour comes from US1 when both are done.
- **US3 (T034–T038)**: after Foundational. T035 uses the settings endpoint, so it needs **US2** (T029–T030) as well. If US3 runs first, drop the first T035 assertion until US2 lands.
- **Polish (T039–T042)**: after all stories.

### Within each story

The tests come first and must fail. Then: config/exceptions → service → controller/route → wiring in `bootstrap/app.php` → green run.

### Shared file

`backend/bootstrap/app.php` is edited by T011, T018, T019 and T037. These four are **not** parallel with each other.

## Parallel examples

```text
Foundational tests:   T003, T004, T005 together
Foundational impl:    T006, T007, T009 together; then T008 → T010 → T011 → T012
US1 tests:            T013, T014, T015, T016, T017 together
US2:                  T022, T023, T024, T025 together; then T026, T027 together → T028 → T029 → T030 → T031; T032 any time
US3:                  T034, T035 together; T036 → T037
```

## Implementation strategy

1. **MVP = Setup + Foundational + US1**: every endpoint after this gets the error contract, the request ID and parameter rejection. Stop and validate with quickstart 3–5.
2. **+ US2**: the first real endpoint and the fail-closed policy values (unblocks US-005 and US-046's settings reads).
3. **+ US3**: `meta.server_time`, which unblocks US-046 (clock slice, `useServerNow`).
4. **Polish**: lint, full quickstart, docs. Then step 8 of the Phase 08 loop at Done.

**Not in this feature** (spec, "Out of scope"): Sanctum and the S/G guards, `catalog.access`, the backend-only switches, the front-end clock, and the US-060 headers.
