# Contract: US-059 API foundation

This file restates **only** what US-059 guarantees. The sources stay authoritative: [api-contracts.md](../../../docs/architecture/api/api-contracts.md) §1 and §3, and [error-format.md](../../../docs/architecture/api/error-format.md) with erratum E-01.

## 1. Every `/api/v1` response

| Guarantee | Applies to |
|---|---|
| `X-Request-Id` header (inbound value reused if `^[A-Za-z0-9._-]{1,128}$`, otherwise generated) | All responses |
| `{data, meta: {server_time}}` envelope, `server_time` ISO-8601 UTC with `Z` | 2xx JSON responses |
| `application/problem+json` body (§2) | All 4xx/5xx |
| Unknown query parameter → 422 `validation_failed`, `errors.<param>[].code = unknown_parameter` | All endpoints |
| Non-empty body with a non-JSON content type → 415 `unsupported_media_type`; invalid JSON → 400 `bad_request` | All endpoints |

## 2. Problem body

```json
{ "type": "<app.url>/problems/validation-failed", "title": "string (fr)", "status": 422,
  "detail": "string (fr)", "instance": "/api/v1/storefront/settings",
  "code": "validation_failed", "request_id": "string",
  "errors": { "foo": [ { "code": "unknown_parameter", "message": "string (fr)" } ] } }
```

`errors` appears only on 422. There are never traces, SQL, class names or file paths.

## 3. Code catalogue (20 codes)

| HTTP | Codes | Extra header |
|---|---|---|
| 400 | `bad_request` | |
| 401 | `unauthenticated` | |
| 403 | `forbidden`, `invalid_signature` | |
| 404 | `not_found` | |
| 405 | `method_not_allowed` (E-01) | `Allow` |
| 409 | `out_of_stock`, `reservation_limit_reached`, `campaign_not_live` | |
| 410 | `campaign_ended` | |
| 413 | `payload_too_large` (E-01) | |
| 415 | `unsupported_media_type` (E-01) | |
| 419 | `csrf_token_mismatch` | |
| 422 | `validation_failed`, `invalid_credentials`, `invalid_token` | |
| 429 | `rate_limited` | `Retry-After` |
| 500 | `server_error` | |
| 503 | `settings_incomplete`, `service_unavailable` | |

**Raising a code from later features:** `throw new ApiProblem('<code>', detail: …, errors: …)`. The status always comes from this catalogue.

## 4. `GET /api/v1/storefront/settings`

- **Auth:** P (`access.public` group). **Parameters:** none, so any query parameter gives a 422.
- **200 response,** `Cache-Control` containing `no-store`:

```json
{ "data": {
    "reservation_minutes": 0, "reservation_timer_mode": "per_item | shared",
    "max_quantity_per_article": 0, "low_stock_threshold": 0,
    "return_period_days": 0, "packaging_fee": { "amount_cents": 0, "currency": "EUR" },
    "display_timezone": "IANA", "upcoming_relative_threshold_minutes": 0,
    "listing_page_size": 60, "catalog_access": "members | public",
    "features": { "hide_add_button_after_add": false, "last_hours_badge": false,
                  "last_hours_badge_threshold_hours": 0 } },
  "meta": { "server_time": "2026-11-05T12:00:00Z" } }
```

The numbers are type placeholders, not values.

- **503 `settings_incomplete`:** any of the 11 undecided policy values is unset, or any of the 12 is invalid (data-model.md), in any environment. `listing_page_size` falls back to 60 when unset. In production, startup fails (research R-01). Every `/api/v1` request then gets this 503 problem body, and `/up` and web routes get a plain 503 page.

## 5. Route groups (for later stories)

| Auth code | Middleware group | Filled by |
|---|---|---|
| S | `access.session` | US-001 / US-002 (session guard) |
| C | `access.catalog` | US-005 (`catalog.access`) |
| P | `access.public` | — (no guard) |
| G | `access.guest` | US-001 / US-002 (guest guard) |

Each module declares its routes in `app/Modules/<Module>/routes.php` inside exactly one group. Every route action type-hints an `ApiFormRequest` (or a subclass), which declares the query parameters it accepts.

## 6. Environment variables

These are the `STOREFRONT_*` and `FEATURE_*` variables in [data-model.md](../data-model.md). `backend/.env.example` lists them all with placeholders labelled `DEV PLACEHOLDER, not a PO decision (Q-xx)`.
