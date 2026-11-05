# Feature Specification: US-059 API foundation

**Feature Branch**: `feature/001-us-059-api-foundation`

**Created**: 2026-11-05

**Status**: Draft

**Input**: User description: "US-059 API foundation. Implement exactly the story in docs/phase-06/04-user-stories.md#us-059 (NFR-OBS-01, NFR-TECH-01): /api/v1 route groups by auth code, the problem+json renderer covering the error-format.md catalogue, request ID and server time middleware, config/storefront.php and GET /api/v1/storefront/settings that fails closed in production. Policy values are PO decisions: no silent defaults (system-overview §7)."

## Traceability

| Item | Reference |
|---|---|
| Story | [US-059 API foundation](../../docs/phase-06/04-user-stories.md#us-059--api-foundation) (Must, Ready, S0, epic E14, depends on US-057) |
| Requirements | NFR-OBS-01 (CONFIRMED, Could, pulled into the MVP), NFR-TECH-01 (CONFIRMED, Must) |
| Acceptance criteria | Story AC-1…AC-5; AC-NFR-OBS-01-1; AC-NFR-TECH-01-1 |
| Tasks | T-059-B1, T-059-BT1, T-059-I1 ([05-technical-tasks.md](../../docs/phase-06/05-technical-tasks.md#us-059)) |
| Contracts | [api-contracts.md](../../docs/architecture/api/api-contracts.md) §1 conventions and §3 `GET /api/v1/storefront/settings`; [error-format.md](../../docs/architecture/api/error-format.md) §1–§3 |
| Design | [system-overview.md](../../docs/architecture/system-overview.md) §7; [backend-architecture.md](../../docs/architecture/backend-architecture.md) §9, §11, §12, §14, §15; [docker-architecture.md](../../docs/architecture/docker-architecture.md) §5; ADR-009 (one "now" per request) |
| Risk | R-15 (policy settings unset: production fails closed) |

The actors are **API client developers**, who build the SPA and every later endpoint against this base, the **SPA** itself, and the **operator**, who deploys and reads the logs.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Every API error has one predictable shape and can be traced (Priority: P1)

A developer calling any `/api/v1` endpoint gets every error in the same machine-readable problem format. It has a stable `code` to switch on and never leaks internals. Each response carries a request ID that the operator can find in the server log.

**Why this priority**: every later endpoint and the SPA's error handling depend on this contract (error-format §2, "SPA behaviour" column). Without it, each story would invent its own error shape.

**Independent Test**: call the settings endpoint with an unknown parameter, call an unknown `/api/v1` path, and force one error of each catalogue code through test doubles. Check each body, header and log entry.

**Acceptance Scenarios**:

1. **Given** any request under `/api/v1` that fails, **When** the response is returned, **Then** its media type is `application/problem+json` and the body has `type`, `title`, `status`, `detail`, `instance`, `code` and `request_id`. It has `errors` only for 422 (story AC-1, error-format §1).
2. **Given** an unhandled failure with diagnostic output enabled in the environment, **When** the 500 response is returned, **Then** the body has `code` `server_error` and no stack trace, SQL, class name, file path or upstream message. Those details are in the server log entry with the same request ID (story AC-1, error-format §1 rule 2).
3. **Given** a request that sends a well-formed `X-Request-Id`, **When** it fails, **Then** the response header `X-Request-Id` and the body `request_id` hold that same value. A server log entry with that value exists (story AC-2, AC-NFR-OBS-01-1).
4. **Given** a request without `X-Request-Id`, **When** it is answered, **Then** the server generates a request ID and returns it in `X-Request-Id`, success or error.
5. **Given** any endpoint, **When** it is called with a query parameter it does not declare, **Then** the answer is 422 `validation_failed`. `errors` has an entry for that parameter's name with field code `unknown_parameter` (story AC-5, api-contracts §1, error-format §3).
6. **Given** each code in the error-format §2 catalogue, **When** an error carrying that code occurs, **Then** the response has the HTTP status listed for it, and 429 responses carry `Retry-After` (T-059-BT1, error-format §1 rule 5).

---

### User Story 2 - The SPA reads policy settings that are never silently defaulted (Priority: P2)

The SPA reads the storefront policy values from `GET /api/v1/storefront/settings`. Every value the PO hasn't decided comes from configuration. In production, a missing value makes the system fail closed instead of running with a value nobody chose.

**Why this priority**: 11 policy values are open PO decisions (system-overview §7, R-15). A silent default would turn an open decision into a shipped rule, which Principle VII forbids.

**Independent Test**: call the settings endpoint with all values configured, then with one value removed, in a production-like and a development environment.

**Acceptance Scenarios**:

1. **Given** all policy values are configured, **When** a client calls `GET /api/v1/storefront/settings` without a session, **Then** it gets 200 with `data` in exactly the api-contracts §3 Settings shape, `meta.server_time` and `Cache-Control: no-store`.
2. **Given** production and one required policy value unset or invalid, **When** the system starts or the settings endpoint is called, **Then** startup fails, or the endpoint answers 503 `settings_incomplete` as a problem body. No default is used, and the server log names the missing setting (story AC-4).
3. **Given** a development or test environment, **When** the example environment file is used as is, **Then** every policy value has a placeholder that is clearly labelled as a development placeholder and not a PO decision (T-059-I1, backend-architecture §12).
4. **Given** the packaging fee configured in cents, **When** settings are read, **Then** `packaging_fee` is a Money value `{amount_cents, currency: "EUR"}` (api-contracts §1 Money, §3).

---

### User Story 3 - Every success response states the server's time (Priority: P3)

Every successful `/api/v1` JSON response carries `meta.server_time`, so countdowns can be based on server time instead of the client clock (FR-CMP-02, FR-CRT-02; consumed by US-046).

**Why this priority**: it's a small, cross-cutting guarantee. US-046 and every countdown depend on it, but no page uses it yet.

**Independent Test**: freeze the server clock at a known instant, call the settings endpoint, and compare `meta.server_time`.

**Acceptance Scenarios**:

1. **Given** any successful `/api/v1` response with a JSON body, **When** it is returned, **Then** it has the envelope `{data, meta}` and `meta.server_time` is an ISO-8601 UTC instant with a `Z` suffix (story AC-3, api-contracts §1).
2. **Given** the server clock is frozen at a known instant for a request, **When** the response is returned, **Then** `meta.server_time` equals that instant. It is the request's single "now" (ADR-009).

---

### Edge Cases

- **Malformed or hostile inbound `X-Request-Id`** (too long, control characters, line breaks): the value is not used. A new request ID is generated, so log entries can't be forged or broken.
- **Unknown path under `/api/v1`**: 404 `not_found` problem body, with `request_id`.
- **Malformed JSON body**: 400 `bad_request`.
- **Maintenance mode**: 503 `service_unavailable`, not `settings_incomplete`.
- **Wrong HTTP method on a known route, oversized body, wrong media type**: these aren't in the catalogue (FR-012).
- **Invalid policy value** (non-numeric number, `catalog_access` not `members`/`public`, `reservation_timer_mode` not `per_item`/`shared`, timezone not an IANA name): treated like an unset value.
- **Query keys with brackets** (`page[number]`): reported under the request's own bracket path in `errors`, e.g. `page.number` (error-format §1 rule 4).
- **Responses with no body** (204): there is no envelope, so no `meta.server_time`. The request ID header is still present.
- **Routes outside `/api/v1`** (the SPA shell, the health route): not changed by this feature.

## Requirements *(mandatory)*

### Functional Requirements

**API base and route groups**

- **FR-001**: The system MUST serve the API under the base path `/api/v1` only (api-contracts §1).
- **FR-002**: The system MUST provide one route group per auth code: **S** (session), **C** (catalogue access), **P** (public) and **G** (guest only) (api-contracts §1, T-059-B1). `GET /api/v1/storefront/settings` belongs to **P**. Guards for S and G come with the authentication stories (US-001, US-002). The C rule (`catalog_access`) and the test that every route is in exactly one group come with US-005 (T-005-B1, T-005-BT1).

**Error format**

- **FR-003**: Every error response under `/api/v1` MUST be `application/problem+json` with `type`, `title`, `status`, `detail`, `instance`, `code` and `request_id`. `errors` MUST be present only for 422 (error-format §1).
- **FR-004**: The system MUST map every code in the error-format §2 catalogue to its listed HTTP status, through one mapping point (backend-architecture §11). Later features MUST be able to raise any catalogue code without changing that point.
- **FR-005**: Error bodies MUST NOT contain stack traces, SQL, class names, file paths or upstream messages, whatever the diagnostic setting of the environment (error-format §1 rule 2; Phase 07 known issue K-2).
- **FR-006**: `title` and `detail` MUST be French text taken from the French language resources. Their wording is PROPOSED until the PO approves it (error-format §3, last paragraph). `code` is the contract.
- **FR-007**: `type` MUST be a URI of the form `<application URL>/problems/<code slug>`, built from the configured application URL. The product domain stays open (Q-75).
- **FR-008**: 422 responses MUST list field errors under the request's own field names, bracket paths included, as `{code, message}` items (error-format §1 rule 4, §3). 429 responses MUST carry `Retry-After` (error-format §1 rule 5).

**Validation of query parameters**

- **FR-009**: Every endpoint MUST declare the query parameters it accepts. Any other query parameter MUST be rejected with 422 `validation_failed`, field code `unknown_parameter` (story AC-5, backend-architecture §14). An endpoint with no parameters, such as settings, rejects every query parameter.

**Request ID and logging**

- **FR-010**: Every `/api/v1` response, success or error, MUST carry an `X-Request-Id` header. The system MUST reuse the inbound `X-Request-Id` when it is well formed (1–128 characters from letters, digits, `.`, `_` and `-`). Otherwise it MUST generate a new unique identifier (backend-architecture §15).
- **FR-011**: The request ID MUST be attached to every server log entry written while the request is handled. Each error response MUST produce one log entry with the request ID, status, `code` and path. A 500 entry MUST also hold the exception details. Log entries MUST NOT contain passwords, session IDs, CSRF tokens, query strings or request bodies (story AC-2, backend-architecture §15, NFR-PRV-01).
- **FR-012**: HTTP errors under `/api/v1` that have no catalogue entry (405 wrong method, 413 body too large, 415 wrong media type) MUST be answered with [NEEDS CLARIFICATION: Q-87, which `code` and status these errors use; the error-format §2 catalogue has none].

**Server time**

- **FR-013**: Every successful `/api/v1` response with a JSON body MUST use the envelope `{data, meta}`, with `meta.server_time` as an ISO-8601 UTC instant ending in `Z` (story AC-3, api-contracts §1).
- **FR-014**: `meta.server_time` MUST be the request's single "now", the same instant every time-based rule in that request uses. It MUST be controllable in tests (ADR-009, backend-architecture §15 "Time").

**Policy settings**

- **FR-015**: All 12 policy values in system-overview §7 MUST come from one configuration source that reads the environment (backend-architecture §12, docker-architecture §5 "Storefront policy").
- **FR-016**: No undecided policy value may have a default in code. The single exception is `listing_page_size`, which is CONFIRMED at 60 (BR-06-07) and may default to 60.
- **FR-017**: A policy value that is set but invalid MUST be treated as unset. Integers MUST be whole numbers ≥ 0, and `reservation_minutes`, `max_quantity_per_article` and `listing_page_size` ≥ 1. `catalog_access` ∈ {`members`, `public`}, `reservation_timer_mode` ∈ {`per_item`, `shared`}, and `display_timezone` MUST be an IANA timezone name.
- **FR-018**: In production, when any required policy value is unset or invalid, the system MUST fail closed. Either startup fails, or `GET /api/v1/storefront/settings` answers 503 `settings_incomplete`. The log names the missing or invalid settings by name (story AC-4, R-15).
- **FR-019**: `GET /api/v1/storefront/settings` MUST be public (P). It MUST answer 200 with `data` in exactly the api-contracts §3 Settings shape: `reservation_minutes`, `reservation_timer_mode`, `max_quantity_per_article`, `low_stock_threshold`, `return_period_days`, `packaging_fee` (Money, from the configured cents), `display_timezone`, `upcoming_relative_threshold_minutes`, `listing_page_size`, `catalog_access`, and `features` {`hide_add_button_after_add`, `last_hours_badge`, `last_hours_badge_threshold_hours`}. The response MUST carry `Cache-Control: no-store` (api-contracts §3, backend-architecture §15).
- **FR-020**: `upcoming_horizon_hours` MUST be configured and checked like every other policy value, but MUST NOT appear in the public settings payload (api-contracts §3 shape; the upcoming endpoint returns its own `meta.horizon_hours`).
- **FR-021**: The feature switches `hide_add_button_after_add` and `last_hours_badge` MUST be configuration booleans that are off unless set (Principle VII: HYPOTHESIS behaviours stay off until the PO validates them).
- **FR-022**: The example environment file MUST list every policy variable and both switches by category, each with a placeholder labelled as a development placeholder. It MUST NOT contain secrets (T-059-I1, docker-architecture §5).

### Key Entities

- **Problem**: one error answer. It has a stable `code`, its HTTP `status`, French `title` and `detail`, the request path (`instance`), a documentation `type` URI and the `request_id`. It has field `errors` only for 422.
- **Request ID**: the correlation identifier of one request. It is either taken from a well-formed inbound header or generated, and it is the same in the response header, the error body and the log entries.
- **Policy settings**: the 12 policy values and the feature switches the SPA reads. Each value is either decided by the PO or unset, never defaulted (except `listing_page_size`).
- **Success envelope**: `data`, plus `meta` with at least `server_time`.

## Open decisions

### Policy values: not blocking US-059

US-059 makes each value configurable and fails closed without it. It does **not** decide any value, and none of these questions blocks this story. They stay open in the register.

| Setting | Open question | Status |
|---|---|---|
| `reservation_minutes` | Q-25, Q-32 | Open |
| `reservation_timer_mode` | Q-32 | Open |
| `max_quantity_per_article` | Q-46 | Open |
| `low_stock_threshold` | Q-26 | Open |
| `return_period_days` | Q-31 | Open |
| `packaging_fee_cents` | Q-30 | Open |
| `display_timezone` | Q-10 | Open |
| `upcoming_relative_threshold_minutes` | Q-35 | Open |
| `upcoming_horizon_hours` | Q-74 | Open |
| `last_hours_badge_threshold_hours` | Q-36 | Open (see the tension below) |
| `catalog_access` | Q-28, Q-71 | Open |
| `listing_page_size` | — | CONFIRMED 60 (BR-06-07) |

### Recorded tensions (for the plan, not resolved here)

- **`last_hours_badge_threshold_hours`**: the api-contracts §3 shape types it `int | null`, but R-15 and the Phase 06 summary (G-05) count it among the required values. This spec doesn't pick a side. The plan takes it up under Q-36, and until then it counts as required in production.
- **Story AC-4 says "boot fails *or*" 503**: if startup fails, no 503 body is ever served. The plan picks one behaviour and records why. FR-018 accepts either.

## Out of scope

- The `catalog.access` rule for group C and the route-list test (US-005); session and guest guards and the authentication package (US-001, US-002).
- The back-end-only switches `enforce_live_window_on_add` and `release_expired_reservations`: they are added by the stories that read them.
- The front-end clock slice, `useServerNow` and money/date formatting (US-046), and a client-sent `X-Request-Id`.
- Web-server security headers and caching headers beyond the settings response (US-060).
- Domain error classes of modules that don't exist yet (Cart, Sales, Inventory). This feature only guarantees that any carried catalogue code renders correctly.
- Rate-limit values (Q-42) and structured log formatting.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of the codes in the error-format §2 catalogue produce a body with all 7 required fields and the listed status, verified by one automated test per code.
- **SC-002**: For any failed API call, an operator finds the matching server log entry from the request ID shown in the response, in one search, with no other information.
- **SC-003**: 0 error bodies contain internals (trace, SQL, class name, file path), including with diagnostic output enabled.
- **SC-004**: In a production-like environment with any single policy value removed (each of the 11 tried), the system never answers the settings with a value; 11 out of 11 cases fail closed.
- **SC-005**: 100% of successful JSON responses carry a UTC `meta.server_time`, and with a frozen clock it equals the frozen instant exactly.
- **SC-006**: A developer adding a new endpoint gets the error shape, request ID, server time and unknown-parameter rejection without writing any code for them.

## Assumptions

- **Inbound request IDs are untrusted input.** The 1–128 character safe set in FR-010 is a security bound at the trust boundary, not a business rule. It accepts common tracing formats.
- **Logging every error response** (not only 5xx) is the reading of AC-NFR-OBS-01-1 ("a failed API call can be correlated"). 4xx entries carry no personal data.
- **Development placeholders** are values chosen only so local work can run. They are labelled as such and are never PO decisions (backend-architecture §12).
- **`packaging_fee`** is configured in cents and exposed as Money. Both forms come from the documents (system-overview §7, api-contracts §3), so this is no conflict.
- **US-057** (Docker environment) is in place. Checks run in the containers on MySQL 8 (Principle V), although this feature adds no tables.
