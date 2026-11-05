# Data model: US-059 API foundation

**No database tables** (the story says "Tables: none"). The entities below are response and configuration shapes only.

## Problem (error body)

The source is [error-format.md](../../docs/architecture/api/error-format.md) §1, with erratum E-01.

| Field | Type | Rule |
|---|---|---|
| `type` | URI string | `<app.url>/problems/<code with _ → ->` (research R-03) |
| `title` | string (French) | From `lang/fr/problems.php`, by code. PROPOSED wording |
| `status` | int | Always the catalogue status of `code` |
| `detail` | string (French) | Generic per code, or set by the raising code. Never internals |
| `instance` | string | Request path, leading `/`, no query string |
| `code` | string | One of the 20 catalogue codes ([contracts/api-foundation.md](contracts/api-foundation.md) §3) |
| `request_id` | string | Equal to the `X-Request-Id` response header |
| `errors` | object | **Only on 422.** `{ "<field dot path>": [ { "code", "message" } ] }` |

**Validation rules:**
- `status` and `code` can't disagree: the catalogue is the only source of `status`.
- An `ApiProblem` with an unknown code can't be built.

## Request ID

| Property | Rule |
|---|---|
| Inbound | `X-Request-Id`, accepted when it matches `^[A-Za-z0-9._-]{1,128}$`. Otherwise it's ignored |
| Generated | A UUID string, when the inbound value is missing or rejected |
| Lifetime | One request |
| Appears in | The response header `X-Request-Id` (every `/api/v1` response), the problem `request_id`, and every log entry of the request |

## Success envelope

| Field | Type | Rule |
|---|---|---|
| `data` | any | Set by the endpoint |
| `meta.server_time` | `date-time` | ISO-8601 UTC, second precision, `Z` suffix. Equals the request clock's single "now" |
| `meta.*` | — | Endpoints may add keys (e.g. `campaign_count`). `server_time` is never removed |
| `links` | object | Optional, set by paginated endpoints |

It applies to 2xx JSON responses with an object payload. 204 and non-JSON responses have no envelope.

## Policy settings

The source is [system-overview.md](../../docs/architecture/system-overview.md) §7 and the [api-contracts.md](../../docs/architecture/api/api-contracts.md) §3 Settings shape. Every row is required. "Unset or invalid" means fail closed (FR-017, FR-018).

| Setting (config key) | Environment variable | Type and validation | Public payload key | Default | Q-ID |
|---|---|---|---|---|---|
| `reservation_minutes` | `STOREFRONT_RESERVATION_MINUTES` | int ≥ 1 | `reservation_minutes` | none | Q-25, Q-32 |
| `reservation_timer_mode` | `STOREFRONT_RESERVATION_TIMER_MODE` | `per_item` \| `shared` | `reservation_timer_mode` | none | Q-32 |
| `max_quantity_per_article` | `STOREFRONT_MAX_QUANTITY_PER_ARTICLE` | int ≥ 1 | `max_quantity_per_article` | none | Q-46 |
| `low_stock_threshold` | `STOREFRONT_LOW_STOCK_THRESHOLD` | int ≥ 0 | `low_stock_threshold` | none | Q-26 |
| `return_period_days` | `STOREFRONT_RETURN_PERIOD_DAYS` | int ≥ 0 | `return_period_days` | none | Q-31 |
| `packaging_fee_cents` | `STOREFRONT_PACKAGING_FEE_CENTS` | int ≥ 0 | `packaging_fee` = `{amount_cents, currency: "EUR"}` | none | Q-30 |
| `display_timezone` | `STOREFRONT_DISPLAY_TIMEZONE` | IANA identifier | `display_timezone` | none | Q-10 |
| `upcoming_relative_threshold_minutes` | `STOREFRONT_UPCOMING_RELATIVE_THRESHOLD_MINUTES` | int ≥ 0 | `upcoming_relative_threshold_minutes` | none | Q-35 |
| `upcoming_horizon_hours` | `STOREFRONT_UPCOMING_HORIZON_HOURS` | int ≥ 0 | **not exposed** (FR-020) | none | Q-74 |
| `last_hours_badge_threshold_hours` | `STOREFRONT_LAST_HOURS_BADGE_THRESHOLD_HOURS` | int ≥ 0 | `features.last_hours_badge_threshold_hours` | none | Q-36 |
| `listing_page_size` | `STOREFRONT_LISTING_PAGE_SIZE` | int ≥ 1 | `listing_page_size` | **60** (CONFIRMED, BR-06-07) | — |
| `catalog_access` | `STOREFRONT_CATALOG_ACCESS` | `members` \| `public` | `catalog_access` | none | Q-28, Q-71 |

### Feature switches

| Switch | Environment variable | Type | Public payload key | Default |
|---|---|---|---|---|
| `hide_add_button_after_add` (FR-PDP-04) | `FEATURE_HIDE_ADD_BUTTON_AFTER_ADD` | bool | `features.hide_add_button_after_add` | `false` (off until PO validation, Principle VII) |
| `last_hours_badge` (FR-STK-03) | `FEATURE_LAST_HOURS_BADGE` | bool | `features.last_hours_badge` | `false` |

**State:** settings are read-only at run time. A value changes only through the environment, followed by a configuration reload or a restart.
