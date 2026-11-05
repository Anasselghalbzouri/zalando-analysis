# REST API contracts — Phase 05

**Status:** PROPOSED contracts for the authorized functional scope.
- **Not designed:** checkout and payment (M21), notify-me (FR-UPC-04), wishlist (C-01), addresses (C-02) and the back-office (Q-50) are deliberately **not** designed (§11).
- **Shapes:** JSON shapes below show **types, not values**.
- **Errors:** see [error-format.md](error-format.md).

## 1. Conventions
| Topic | Convention |
|---|---|
| Base path | `/api/v1`, with the version in the path. A breaking change means `/api/v2`. Additive fields are non-breaking |
| Origin | Same origin as the SPA (Nginx). No CORS (ADR-003) |
| Format | `application/json; charset=utf-8`; snake_case fields; UTF-8 |
| Auth | **S** = session required (Sanctum SPA, `auth:sanctum`). **C** = catalogue access: requires a session when `catalog_access = members`, public when it is `public` (Q-71). **P** = public. **G** = guest only (no session) |
| CSRF | Every unsafe method (POST, PUT, PATCH, DELETE) needs the `X-XSRF-TOKEN` header matching the `XSRF-TOKEN` cookie. A missing or wrong token gives 419 (NFR-SEC-02) |
| Identity | Member resources use `me`/`account` paths. **No user ID ever appears in a URL** (NFR-SEC-01) |
| Envelope | Success: `{ "data": …, "meta": { "server_time": "date-time", … }, "links"?: {…} }`. `meta.server_time` is **always** present (countdowns, FR-CMP-02) |
| Time | ISO-8601 UTC with a `Z` suffix (`date-time`). Dates without time use `date` (`YYYY-MM-DD`) |
| Money | `Money = { "amount_cents": int, "currency": "EUR" }`, VAT included (BR-12-02) |
| Images | `Image = { "url": string, "alt": string }`, with alt text always present (NFR-A11Y-01) |
| Endpoint fields | Every endpoint lists Purpose, Auth, Parameters, Body, Response, Validation, Errors and Pagination; the method and route are in its heading |
| Pagination | Page-based: `page[number]` (≥ 1, default 1) and `page[size]` (1…`listing_page_size`, default `listing_page_size`, i.e. 60 per BR-06-07). The response has `meta.page = {number, size, total, last_page}` and `links = {first, prev, next, last}`. The UI pattern (scroll or control) is UNKNOWN (Q-11) |
| Unknown parameters | Rejected with 422 `validation_failed` |
| Caching | `Cache-Control: no-store` on S endpoints and on everything with availability (cart, article detail). Short private caching is allowed on catalogue reads in `public` mode only (NFR-SEC-03) |
| Rate limits | 429 `rate_limited` with `Retry-After`. Limits are placeholders pending Q-42 |
| Request ID | `X-Request-Id` is accepted and echoed (NFR-OBS-01) |

### 1.1 Shared shapes
```json
// CampaignTile
{ "id": "int", "name": "string", "image": "Image",
  "starts_at": "date-time", "ends_at": "date-time", "state": "live",
  "headline": { "type": "percent | special", "value": "int | null" } }

// UpcomingTile — no ID and no link (FR-UPC-01, BR-03-11)
{ "name": "string", "image": "Image", "starts_at": "date-time" }

// ArticleTile
{ "sku": "string", "campaign_id": "int",
  "brand": { "code": "string", "name": "string" },
  "name": "string", "colour": "string | null",
  "images": ["Image"],                       // first two images (FR-CAT-02)
  "price": "Money", "rrp": "Money", "discount_percent": "int",
  "availability": "available | reserved | sold_out",   // "Réservé" state (FR-STK-01, rule Q-45)
  "campaign_ends_at": "date-time" }          // for the PROPOSED "Dernières heures" badge (FR-STK-03)

// Problem (errors) — see error-format.md
```

**`availability` is computed (PROPOSED, Q-45):**
- `available`: at least one size has `quantity_available > 0`.
- `reserved`: nothing available, but `quantity_reserved > 0` somewhere.
- `sold_out`: otherwise.

## 2. Identity (`/auth`, `/me`)
```json
// User
{ "first_name": "string", "email": "string", "email_verified": "bool" }
```

#### GET `/sanctum/csrf-cookie`
- **Purpose:** issue the `XSRF-TOKEN` cookie before login or registration (framework route; ADR-003).
- **Auth:** P.
- **Parameters:** none.
- **Body:** none.
- **Response:** 204, plus `Set-Cookie` for `XSRF-TOKEN` and the session cookie.
- **Validation:** none.
- **Errors:** 429.
- **Pagination:** none.

#### POST `/api/v1/auth/register`
- **Purpose:** create a member account and start a session (FR-ACC-08).
- **Auth:** G, with CSRF.
- **Parameters:** none.
- **Body:** `{ "first_name": "string", "email": "string", "password": "string", "password_confirmation": "string", "adult_confirmed": "bool", "terms_accepted": "bool" }`
- **Response:** 201 `{data: User}`, and the session starts.
- **Validation:**
  - `first_name`: 1–100 characters.
  - `email`: RFC email, ≤ 255 characters, unique (BR-15-02).
  - `password`: ≥ 12 characters, confirmed, with a breached-password check (PROPOSED).
  - `adult_confirmed` and `terms_accepted`: accepted (BR-15-02). The accepted terms version is recorded.
  - Further fields and social login are UNKNOWN (Q-40, Q-41).
- **Errors:** 422 `validation_failed` (`taken`, `must_be_accepted`, …), 419, 429.
- **Pagination:** none.

#### POST `/api/v1/auth/login`
- **Purpose:** start a session.
- **Auth:** G, with CSRF.
- **Parameters:** none.
- **Body:** `{ "email": "string", "password": "string", "remember": "bool?" }`
- **Response:** 200 `{data: User}`. The session ID is regenerated.
- **Validation:** `email` and `password` required. Throttled per e-mail + IP.
- **Errors:** 422 `invalid_credentials` (generic: doesn't reveal whether the e-mail exists), 419, 429.
- **Pagination:** none.

#### POST `/api/v1/auth/logout`
- **Purpose:** end the session.
- **Auth:** S, with CSRF.
- **Parameters:** none.
- **Body:** none.
- **Response:** 204. The session is invalidated and the CSRF token rotated.
- **Validation:** none.
- **Errors:** 401, 419.
- **Pagination:** none.

#### POST `/api/v1/auth/forgot-password`
- **Purpose:** send a password-reset link (PROPOSED security baseline).
- **Auth:** G, with CSRF.
- **Parameters:** none.
- **Body:** `{ "email": "string" }`
- **Response:** 202, the same response whether or not the e-mail exists.
- **Validation:** `email` required, RFC format.
- **Errors:** 422, 419, 429.
- **Pagination:** none.

#### POST `/api/v1/auth/reset-password`
- **Purpose:** set a new password with a reset token.
- **Auth:** G, with CSRF.
- **Parameters:** none.
- **Body:** `{ "token": "string", "email": "string", "password": "string", "password_confirmation": "string" }`
- **Response:** 204. The user's other sessions are invalidated.
- **Validation:** token, e-mail and password rules as for register.
- **Errors:** 422 `invalid_token` / `validation_failed`, 419, 429.
- **Pagination:** none.

#### POST `/api/v1/auth/email/verification-notification`
- **Purpose:** resend the verification e-mail.
- **Auth:** S, with CSRF.
- **Parameters:** none.
- **Body:** none.
- **Response:** 202.
- **Validation:** none (already verified → 202, no e-mail sent).
- **Errors:** 401, 419, 429.
- **Pagination:** none.

#### GET `/api/v1/auth/email/verify/{id}/{hash}`
- **Purpose:** verify the e-mail address from the signed link.
- **Auth:** S.
- **Parameters:** path `id`, `hash`; query `expires`, `signature`. The ID here is the signed link's subject and must equal the session user. It isn't a lookup key.
- **Body:** none.
- **Response:** 204.
- **Validation:** valid, unexpired signature; the subject matches the session.
- **Errors:** 403 `invalid_signature`, 401.
- **Pagination:** none.

#### GET `/api/v1/me`
- **Purpose:** the current member (session check for the SPA).
- **Auth:** S.
- **Parameters:** none.
- **Body:** none.
- **Response:** 200 `{data: User}`, `no-store`.
- **Validation:** none.
- **Errors:** 401.
- **Pagination:** none.

## 3. Storefront
```json
// Settings — every value is a PO decision; the endpoint fails closed (503 settings_incomplete) in production if any is unset
{ "reservation_minutes": "int", "reservation_timer_mode": "per_item | shared",
  "max_quantity_per_article": "int", "low_stock_threshold": "int",
  "return_period_days": "int", "packaging_fee": "Money",
  "display_timezone": "string (IANA)", "upcoming_relative_threshold_minutes": "int",
  "listing_page_size": "int", "catalog_access": "members | public",
  "features": { "hide_add_button_after_add": "bool", "last_hours_badge": "bool",
                "last_hours_badge_threshold_hours": "int | null" } }

// Navigation
{ "items": [ { "landing_id": "int", "title": "string",
               "mega_menu": [ { "category_id": "int", "name": "string",
                                "children": [ { "category_id": "int", "name": "string" } ] } ] | null } ] }
```

#### GET `/api/v1/storefront/settings`
- **Purpose:** the policy values the SPA needs (system-overview §7).
- **Auth:** P.
- **Parameters:** none.
- **Body:** none.
- **Response:** 200 `{data: Settings}`, `no-store`.
- **Validation:** none.
- **Errors:** 503 `settings_incomplete` (production, a policy value unset).
- **Pagination:** none.

#### GET `/api/v1/storefront/navigation`
- **Purpose:** main nav items and mega-menus (FR-NAV-03, FR-NAV-04).
- **Auth:** C.
- **Parameters:** none.
- **Body:** none.
- **Response:** 200 `{data: Navigation}`. `mega_menu: null` means the vertical has no mega-menu.
- **Validation:** none.
- **Errors:** 401 (members mode).
- **Pagination:** none (bounded tree).

## 4. Sales: landings and campaigns

#### GET `/api/v1/landings/{landing}`
- **Purpose:** a landing page: hero, live campaign grid, and which sections to show (FR-EVT-01–05, FR-CMP-01–04).
- **Auth:** C.
- **Parameters:** path `landing` = a numeric ID or the literal `default` (FR-EVT-01).
- **Body:** none.
- **Response:**
```json
{ "data": { "id": "int", "title": "string", "type": "aggregate | vertical | curated", "is_default": "bool",
            "hero": { "image": "Image", "title": "string | null", "campaign_id": "int | null" } | null,
            "sections": { "upcoming": "bool", "personal_rails": "bool", "popular_rail": "bool", "footer": "bool" },
            "campaigns": ["CampaignTile"] },
  "meta": { "server_time": "date-time", "campaign_count": "int" } }
```
- **Rules:**
  - Only live, published campaigns (FR-EVT-02). Order: `landing_campaigns.position`, then `starts_at DESC` (FR-EVT-03, PROPOSED).
  - `personal_rails` is true only for the default landing (FR-REC-01).
  - The `curated` type gives `upcoming = false` and `footer = false` (FR-EVT-05, PROPOSED).
- **Validation:** `landing` matches `^\d+$` or `default`.
- **Errors:** 404 `not_found`; 401 (members mode).
- **Pagination:** none. The grid is a bounded set of live campaigns (126 observed on the reference).

#### GET `/api/v1/campaigns/upcoming`
- **Purpose:** the global "Prochainement" list (FR-UPC-01, FR-UPC-03).
- **Auth:** C.
- **Parameters:** none.
- **Body:** none.
- **Response:** 200 `{data: [UpcomingTile], meta: {server_time, horizon_hours}}`, ordered by `starts_at` and limited to `upcoming_horizon_hours` (Q-74).
- **Validation:** none.
- **Errors:** 401 (members mode).
- **Pagination:** none (bounded by the horizon).
- **Routing:** this literal route is registered **before** `/campaigns/{campaign}`, and `{campaign}` is constrained to digits, so the two never clash.

#### GET `/api/v1/campaigns/{campaign}`
- **Purpose:** the campaign header for the campaign listing page (name, end countdown, headline).
- **Auth:** C.
- **Parameters:** path `campaign` (digits).
- **Body:** none.
- **Response:** 200 `{data: CampaignTile}`.
- **Validation:** `campaign` is numeric.
- **Errors:**
  - 404 `not_found`: unknown, draft, or not yet started. Refusing upcoming campaigns server-side is **PROPOSED**: the reference shows them without links (BR-03-11), but server-side enforcement there is a HYPOTHESIS.
  - 410 `campaign_ended` (PROPOSED, Q-17).
  - 401 (members mode).
- **Pagination:** none.

#### GET `/api/v1/campaigns/{campaign}/articles/{sku}`
- **Purpose:** the product detail page (FR-PDP-01, FR-PDP-05–08, FR-CMP-06).
- **Auth:** C. `Cache-Control: no-store` (availability).
- **Parameters:** path `campaign` (digits), `sku` (string, ≤ 32 characters).
- **Body:** none.
- **Response:**
```json
{ "data": { "sku": "string", "campaign": { "id": "int", "name": "string", "ends_at": "date-time" },
            "brand": { "code": "string", "name": "string" },
            "name": "string", "colour": "string | null", "model_code": "string",
            "images": ["Image"], "price": "Money", "rrp": "Money", "discount_percent": "int",
            "details": "string | null", "size_and_fit": "string | null",
            "composition": "string | null", "manufacturing_info": "string | null",
            "attributes": [ { "code": "string", "name": "string", "values": ["string"] } ],
            "size_system": { "code": "string", "name": "string" },
            "sizes": [ { "variant_id": "int", "label": "string",
                         "availability": "available | reserved | sold_out",
                         "low_stock_quantity": "int | null" } ],
            "delivery_window": { "from": "date", "to": "date" } | null,
            "returns": { "free": "bool", "period_days": "int" },
            "in_cart": "bool" },
  "meta": { "server_time": "date-time" } }
```
- **Rules:**
  - `low_stock_quantity` is set only when `0 < available ≤ low_stock_threshold` (FR-PDP-05, Q-26).
  - `delivery_window` is computed by `DeliveryEstimator` from the product lead time. The computation is UNKNOWN (Q-49), and the field is null when no lead time is set.
  - `returns.period_days` comes from the single setting (FR-PDP-07, C-03).
  - `in_cart` supports `hide_add_button_after_add` (FR-PDP-04, PROPOSED).
- **Validation:** `campaign` is numeric; the SKU belongs to that campaign.
- **Errors:** 404 (unknown SKU or not in this campaign; campaign not started, PROPOSED), 410 `campaign_ended`, 401 (members mode).
- **Pagination:** none.

## 5. Catalogue

#### GET `/api/v1/brands/{code}`
- **Purpose:** brand header and breadcrumb **name** (FR-BRD-01, FR-BRD-02). Products come from `GET /articles?brand=`.
- **Auth:** C.
- **Parameters:** path `code` (≤ 16 characters).
- **Body:** none.
- **Response:** 200 `{data: {code: "string", name: "string"}}`.
- **Validation:** `code` matches `^[A-Za-z0-9-]{1,16}$`.
- **Errors:** 404, 401 (members mode).
- **Pagination:** none.

#### GET `/api/v1/categories/{id}`
- **Purpose:** category header and breadcrumb (FR-CAT-01). Products come from `GET /articles?category=`.
- **Auth:** C.
- **Parameters:** path `id` (digits).
- **Body:** none.
- **Response:** 200 `{data: {id: "int", name: "string", breadcrumb: [{id: "int", name: "string"}]}}`.
- **Validation:** `id` is numeric.
- **Errors:** 404, 401 (members mode).
- **Pagination:** none.

## 6. Listing, search and recommendations

#### GET `/api/v1/articles`
- **Purpose:** the single listing endpoint for every context (FR-CAT-01–09, FR-SRC-02, FR-BRD-01).
- **Auth:** C (`collection=top-picks` needs S).
- **Parameters** (query):

| Parameter | Type | Rule |
|---|---|---|
| `campaign` \| `category` \| `brand` \| `q` \| `collection` | int \| int \| string (code) \| string (1–100) \| `top-picks` | **Exactly one** is required |
| `sort` | enum `newest`, `preferred`, `price_asc`, `price_desc`, `discount` | Default `preferred` (BR-06-01). `preferred` = merchandising position, then newest, as a placeholder (Q-43) |
| `filter[size][]` | int[] (size_value IDs) | Values must exist |
| `filter[brand][]` | string[] (brand codes) | |
| `filter[category][]` | int[] | Includes descendants |
| `filter[attr][{code}][]` | string[] (attribute value slugs) | `code` ∈ the filterable attributes |
| `filter[price_min]`, `filter[price_max]` | int (whole euros) | ≥ 0, and min ≤ max |
| `page[number]`, `page[size]` | int | §1 |

- **Body:** none.
- **Response:**
```json
{ "data": ["ArticleTile"],
  "meta": { "server_time": "date-time",
            "context": { "type": "campaign | category | brand | search | top_picks", "id": "int | string | null", "title": "string" },
            "page": { "number": "int", "size": "int", "total": "int", "last_page": "int" },
            "active_filter_count": "int" },
  "links": { "first": "string", "prev": "string | null", "next": "string | null", "last": "string" } }
```
- **Rules:**
  - Live campaign articles only. With `q`, a FULLTEXT match on `products.search_text` (ADR-008).
  - `collection=top-picks` uses the `ForYou` provider (empty in v1, Q-29).
  - Clearing one filter ("Retirer") or all filters ("Réinitialiser", FR-CAT-08) is a new request without those parameters. No server state is involved.
- **Validation:**
  - exactly one context
  - known keys only
  - enum `sort`
  - integer IDs and existing values
  - `price_min ≤ price_max` (`min_greater_than_max`)
  - `page[size] ≤ listing_page_size`
- **Errors:**
  - 422 `validation_failed`
  - 404: unknown campaign, category or brand
  - 410: ended campaign
  - 401 (members mode, or top-picks without a session)
- **Pagination:** page-based (§1). Default and maximum page size = `listing_page_size` (60, BR-06-07). `meta.page.total` gives the count. Whether the UI shows a control or infinite scroll is UNKNOWN (Q-11).

#### GET `/api/v1/articles/facets`
- **Purpose:** filter dialog content and the result count for "Afficher N résultats", **without loading products** (FR-CAT-04–08).
- **Auth:** as `GET /articles`.
- **Parameters:** the same context and `filter[...]` parameters as `GET /articles`; no `sort` or `page`.
- **Body:** none.
- **Response:**
```json
{ "data": { "total": "int",
            "sizes": [ { "size_system": { "code": "string", "name": "string" },
                         "values": [ { "id": "int", "label": "string", "count": "int" } ] } ],
            "brands": [ { "code": "string", "name": "string", "count": "int" } ],
            "categories": [ { "id": "int", "name": "string", "count": "int" } ],
            "attributes": [ { "code": "string", "name": "string",
                              "values": [ { "slug": "string", "value": "string", "count": "int" } ] } ],
            "price": { "min_euros": "int", "max_euros": "int" } },
  "meta": { "server_time": "date-time" } }
```
- **Rules:**
  - Each facet's counts ignore that facet's own filter (indexing-strategy §3.3).
  - Facets with no values are omitted. That's how context-dependent filters such as "Longueur" appear (BR-06-08).
  - Price bounds are the min/max in the context (FR-CAT-07).
- **Validation:** as `GET /articles`, but `sort` and `page` are rejected.
- **Errors:** as `GET /articles`.
- **Pagination:** none. Facet value lists are bounded by the context.

#### GET `/api/v1/search/suggestions`
- **Purpose:** brand suggestions as the member types (FR-SRC-01).
- **Auth:** C.
- **Parameters:** query `q`.
- **Body:** none.
- **Response:** 200 `{data: [{type: "brand", code: "string", name: "string"}]}`, at most 10 items, brands with live articles only (PROPOSED).
- **Validation:** `q` string, 1–100 characters (the minimum length is UNKNOWN, Q-44).
- **Errors:** 422, 429, 401 (members mode).
- **Pagination:** none (top 10).

#### GET `/api/v1/recommendations/{rail}`
- **Purpose:** recommendation rails (FR-REC-01, FR-REC-02).
- **Auth:** `for-you` and `continue-shopping` need S; `popular` is C.
- **Parameters:** path `rail` ∈ `for-you`, `continue-shopping`, `popular`; query `landing` (int, required for `popular`).
- **Body:** none.
- **Response:** 200 `{data: [ArticleTile], meta: {server_time, strategy: "string"}}`, at most 12 items. **An empty `data` is valid** (logic UNKNOWN, Q-29), and the UI hides the rail.
- **Validation:** known rail; `landing` exists when required.
- **Errors:** 422, 404 (landing), 401.
- **Pagination:** none (at most 12).

## 7. Cart (reservations)
All cart endpoints: auth **S**, CSRF on writes, `Cache-Control: no-store`.

```json
// Cart
{ "items": [ { "id": "int", "sku": "string", "campaign_id": "int",
               "brand": { "code": "string", "name": "string" }, "name": "string",
               "size_label": "string", "image": "Image", "quantity": "int",
               "price": "Money", "rrp": "Money", "discount_percent": "int",
               "reserved_at": "date-time", "expires_at": "date-time" } ],
  "item_count": "int", "savings": "Money", "total": "Money",
  "excludes_packaging_and_handling": true,
  "expires_at": "date-time | null" }   // earliest item expiry (header countdown, FR-NAV-05)
```

#### GET `/api/v1/cart`
- **Purpose:** active reservations, savings vs RRP and the total (FR-CRT-01, FR-CRT-03, FR-CRT-05, FR-CRT-06).
- **Auth:** S.
- **Parameters:** none.
- **Body:** none.
- **Response:** 200 `{data: Cart, meta: {server_time}}`. An empty cart has `items: []`. Expired reservations are never included.
- **Validation:** none.
- **Errors:** 401.
- **Pagination:** none (the cart is bounded by the quantity limits).

#### POST `/api/v1/cart/items`
- **Purpose:** reserve a unit (FR-PDP-03, FR-CRT-02).
- **Auth:** S, with CSRF.
- **Parameters:** none.
- **Body:** `{ "sku": "string", "variant_id": "int", "quantity": "int?" }`. If omitted, `quantity` is 1, the default **request** quantity. The per-member **limit** `max_quantity_per_article` has no default; it's a PO setting (Q-46).
- **Response:** 201 `{data: Cart, meta: {server_time}}`.
- **Validation:**
  - `sku` exists.
  - `variant_id` is **required**: 422 field code `size_required`, message "Choisissez une taille" (FR-PDP-02).
  - The variant belongs to the SKU's product.
  - `quantity` ≥ 1, and the existing plus requested quantity ≤ `max_quantity_per_article`.
- **Errors:**
  - 409 `out_of_stock` (NFR-CON-01)
  - 409 `reservation_limit_reached` (Q-46)
  - 409 `campaign_not_live` (PROPOSED guard, FR-CMP-08)
  - 404, 401, 419, 429
- **Pagination:** none.
- **Idempotency:** adding the same SKU and size again increases `quantity` up to the limit. An **expired** row for the same SKU and size is first released inside the same transaction (ADR-010), so it neither counts against the limit nor leaks reserved stock.
- **Timer:** `expires_at` per item follows `reservation_timer_mode` (Q-32).

#### DELETE `/api/v1/cart/items/{id}`
- **Purpose:** remove an item and release its unit (FR-CRT-04). The UI asks for confirmation first.
- **Auth:** S, with CSRF.
- **Parameters:** path `id` (digits) of the member's own reservation.
- **Body:** none.
- **Response:** 200 `{data: Cart, meta: {server_time}}`.
- **Validation:** `id` is numeric; ownership is checked by policy.
- **Errors:** 404 (unknown, expired or another member's reservation, so ownership isn't disclosed), 401, 419.
- **Pagination:** none.

The "Commander" button (FR-CRT-07) navigates to the checkout entry point; no endpoint is designed here (§10).

## 8. Account
All account endpoints: auth **S**, CSRF on writes, `Cache-Control: no-store`.

```json
// SizeProfile
{ "gender": "women | men", "garment_group": "string", "size_system": "string", "size_value_ids": ["int"] }
```

#### GET `/api/v1/account/overview`
- **Purpose:** greeting and recent updates (FR-ACC-02).
- **Auth:** S.
- **Parameters:** none.
- **Body:** none.
- **Response:** 200 `{data: {first_name: "string", updates: []}}`. The content of `updates` is UNKNOWN; it's an empty list in v1.
- **Validation:** none.
- **Errors:** 401.
- **Pagination:** none.

#### GET `/api/v1/account/profile`
- **Purpose:** profile (FR-ACC-01).
- **Auth:** S.
- **Parameters:** none.
- **Body:** none.
- **Response:** 200 `{data: {first_name: "string", email: "string"}}`.
- **Validation:** none.
- **Errors:** 401.
- **Pagination:** none.

#### PATCH `/api/v1/account/profile`
- **Purpose:** update the first name (FR-ACC-01).
- **Auth:** S, with CSRF.
- **Parameters:** none.
- **Body:** `{ "first_name": "string" }`
- **Response:** 200 `{data: Profile}`.
- **Validation:** `first_name` 1–100 characters. Changing the e-mail or other fields is UNKNOWN (Q-41).
- **Errors:** 422, 401, 419.
- **Pagination:** none.

#### GET `/api/v1/account/newsletter`
- **Purpose:** newsletter preferences (FR-ACC-05).
- **Auth:** S.
- **Parameters:** none.
- **Body:** none.
- **Response:** 200 `{data: {daily_sales: "bool", weekly_sales: "bool", personalisation_opt_in: "bool", unsubscribed_at: "date-time | null"}}`.
- **Validation:** none.
- **Errors:** 401.
- **Pagination:** none.

#### PUT `/api/v1/account/newsletter`
- **Purpose:** save the preferences (FR-ACC-05).
- **Auth:** S, with CSRF.
- **Parameters:** none.
- **Body:** `{ "daily_sales": "bool", "weekly_sales": "bool", "personalisation_opt_in": "bool" }`
- **Response:** 200 `{data: Newsletter}`. Saving any `true` clears `unsubscribed_at` (PROPOSED).
- **Validation:** all three are required booleans.
- **Errors:** 422, 401, 419.
- **Pagination:** none.

#### GET `/api/v1/account/newsletter/unsubscribe-reasons`
- **Purpose:** the reason options (FR-ACC-06).
- **Auth:** S.
- **Parameters:** none.
- **Body:** none.
- **Response:** 200 `{data: [{id: "int", label: "string"}]}`. Values UNKNOWN (PO content).
- **Validation:** none.
- **Errors:** 401.
- **Pagination:** none (short list).

#### POST `/api/v1/account/newsletter/unsubscribe`
- **Purpose:** unsubscribe with a reason (FR-ACC-06, BR-15-04).
- **Auth:** S, with CSRF.
- **Parameters:** none.
- **Body:** `{ "reason_id": "int" }`
- **Response:** 200 `{data: Newsletter}`.
- **Validation:** `reason_id` required (`reason_required`), exists, active.
- **Errors:** 422, 401, 419.
- **Pagination:** none.

#### GET `/api/v1/account/saved-sizes`
- **Purpose:** saved sizes, plus the options to edit them (FR-ACC-07).
- **Auth:** S.
- **Parameters:** none.
- **Body:** none.
- **Response:** 200 `{data: {profiles: [SizeProfile], options: {garment_groups: [{code, name}], size_systems: [{code, name, values: [{id, label}]}]}}}`.
- **Validation:** none.
- **Errors:** 401.
- **Pagination:** none.

#### PUT `/api/v1/account/saved-sizes`
- **Purpose:** replace the saved sizes (FR-ACC-07).
- **Auth:** S, with CSRF.
- **Parameters:** none.
- **Body:** `{ "profiles": [ SizeProfile ] }`
- **Response:** 200 `{data: {profiles: [SizeProfile]}}`.
- **Validation:**
  - `gender` ∈ {women, men}; the garment group and size system codes exist.
  - At most one profile per (gender, garment group).
  - Every `size_value_ids` entry belongs to the profile's system (`size_not_in_system`).
  - An empty `size_value_ids` deletes the profile.
- **Errors:** 422, 401, 419.
- **Pagination:** none.

**Not designed:**
- **Orders and returns** (FR-ACC-03, FR-ACC-04): the data is owned by checkout (C-04). The SPA renders their empty states and the return entry pages without an API call.
- **Addresses** (C-02).
- **Account deletion** (Q-73).

## 9. Content

#### GET `/api/v1/help`
- **Purpose:** help categories and article titles (FR-CNT-02, FR-DLV-01).
- **Auth:** P.
- **Parameters:** none.
- **Body:** none.
- **Response:** 200 `{data: [{slug: "string", title: "string", articles: [{path: "string", title: "string"}]}]}`, published pages only.
- **Validation:** none.
- **Errors:** none specific.
- **Pagination:** none (bounded tree).

#### GET `/api/v1/pages/{path}`
- **Purpose:** a help or legal page, e.g. `help/<category>/<slug>` or `terms` (FR-CNT-02, FR-CNT-03, FR-DLV-01, FR-PRC-02).
- **Auth:** P.
- **Parameters:** path `path` (may contain `/`; ≤ 255 characters).
- **Body:** none.
- **Response:** 200 `{data: {path: "string", type: "help | legal", title: "string", body_html: "string", version: "string | null", updated_at: "date-time"}}`. `body_html` is sanitised on write.
- **Validation:** `path` matches `^[a-z0-9/-]+$`; published pages only.
- **Errors:** 404.
- **Pagination:** none.

## 10. Checkout boundary (OUT OF SCOPE: contract outline only)
The checkout specification (Q-04) will need two internal module contracts, **not HTTP endpoints**:
- `ActiveReservationsForCheckout(user)`: reservations with `expires_at > now`, with prices.
- `MarkSold(reservation_ids)`: atomically turns reserved quantities into `quantity_sold` and deletes the reservations. It **refuses expired rows**, which must be released instead (ADR-010).

Fees (FR-PRC-02 is display only), payment, vouchers and orders are not designed.

## 11. Candidate route groups from the brief that are not designed
| Candidate | Decision | Reason |
|---|---|---|
| `/api/v1/wishlist` | Not designed | No evidence (C-01, Q-76) |
| `/api/v1/products` | Replaced by `/api/v1/articles` plus `/campaigns/{id}/articles/{sku}` | Products are only ever sold as campaign articles (ADR-004). A product-only endpoint would expose prices outside a campaign |
| `/api/v1/account/addresses` | Not designed | C-02 |
| `/api/v1/campaigns/{id}/notify` | Not designed | FR-UPC-04 HYPOTHESIS, Q-47 |
| Checkout and payment | Not designed | M21 OUT OF SCOPE (FR-OOS-01). Zalando Plus (FR-OOS-02) isn't designed either |

## 12. Field-to-schema traceability
| Response field | Source |
|---|---|
| CampaignTile.id, name, image, starts_at, ends_at | `campaigns.id, name, image_path/image_alt, starts_at, ends_at` |
| CampaignTile.headline | `campaigns.headline_mode`, `headline_percent`, `MAX(campaign_articles.discount_percent)` |
| CampaignTile.state | derived (live predicate) |
| UpcomingTile.* | `campaigns.name, image_path/image_alt, starts_at` |
| Landing.* | `landings.id, title, type, default_flag, hero_*`; `landing_campaigns.position` |
| Navigation.* | `landings.nav_position, menu_category_id`; `categories` tree |
| ArticleTile.sku, price, rrp, discount_percent | `campaign_articles.sku, sale_price_cents, rrp_cents, discount_percent` |
| ArticleTile.brand, name, colour, images | `brands.code, name`; `products.name`; `attribute_values` (code `couleur`); `product_images` |
| ArticleTile.availability, sizes[].availability, low_stock_quantity | `inventory_items.quantity_available, quantity_reserved` + setting `low_stock_threshold` |
| Article detail texts, model_code, delivery lead time | `products.details, size_and_fit, composition, manufacturing_info, model_code, delivery_days_min/max` |
| sizes[].variant_id, label; size_system | `product_variants.id` → `size_values.label`; `size_systems.code, name` |
| Cart items | `cart_reservations.id, quantity, reserved_at, expires_at` → `inventory_items` → `campaign_articles`, `product_variants`, `size_values` |
| Facets | `size_values`, `brands`, `categories`, `attribute_values`, `campaign_articles.sale_price_cents` |
| User, Profile | `users.first_name, email, email_verified_at` |
| Newsletter | `newsletter_preferences.*`, `unsubscribe_reasons.id, label` |
| SizeProfile, options | `saved_size_profiles`, `saved_size_values`, `garment_groups`, `size_systems`, `size_values` |
| Help, pages | `help_categories`, `content_pages` |
| Settings | `config/storefront.php` (no table) |
| Recommendations | `article_reservation_stats` (popular) or empty |
