# Phase 03 — API observations (HAR)

**Source:** `evidence/har/zalando`. The source, `#n` notation, labels and global limitations are as in [network-analysis.md](network-analysis.md).

**Schema notation:** `str`, `int`, `bool`, `float`, `null` are JSON types observed in this single sample. `…` means deeper levels were not expanded. All values are omitted. Schemas come from `evidence/har-sanitized/zalando.summary.json`.

## Summary of first-party APIs observed
| ID | Method + path | Req content-type | Resp content-type | Status | Purpose (observed / inferred) |
|---|---|---|---|---|---|
| API-01 | GET `/api/tracking/clients/me/events/ga4` | — | `application/json; charset=utf-8` | 200 | Member attributes for analytics |
| API-02 | GET `/api/flash-messenger/clients/me/messages` | — | `application/json; charset=utf-8` | 200 | User notification messages |
| API-03 | POST `/api/graphql/` | `application/json` | `application/json` | 200 | Persisted GraphQL query (data layer) |
| API-04 | GET `/api/phoenix/stockcart/cart` | — | (none, empty) | 204 | Cart state |
| API-05 | POST `/api/cmag` | `application/json` | (none, empty) | 200 | Client-side performance marks |
| API-06 | POST `/api/otlp/metrics` | `application/json` | `application/json` | 200 | OpenTelemetry metrics export |
| API-07 | POST `/<obfuscated-sensor-path>` | `application/json` | `application/json` (empty) | 202 | Bot-protection sensor |

No API used a query string, and no versioned path (`/v1/…`) appears for first-party APIs.

---

## API-01 Tracking attributes — `GET /api/tracking/clients/me/events/ga4` (#34)
- **Observation:** returns a flat JSON object describing the **current member**. The identity comes from the session; no ID is in the URL (`clients/me`). Response schema:
```json
{ "customer_hash": "str", "existing_customer": "bool", "fashion_gender": "str", "hashed_email": "str",
  "language_country": "str", "local_notification_status": "null", "loyalty_status": "str", "loyalty_tier": "str",
  "marketing_notification_status": "null", "method": "str", "myfilter_usage": "bool", "newsletter_status": "int",
  "newsletter_tracking": "bool", "plus_status": "int", "registration_day": "int", "registration_month": "int",
  "registration_year": "int", "sales_channel_id": "str", "transaction_notification_status": "null", "x_zalando_lounge_id": "str" }
```
- **Confidence:** HIGH · **Classification:** CONFIRMED (shape). HYPOTHESIS: the data feeds GA4 via GTM, based on the path segment `ga4`.
- **Limitations:**
  - **Contains personal data** (hashed email, customer hash, member ID). Values are never reproduced.
  - Field enumerations (e.g. the values of `loyalty_tier`) are UNKNOWN.
  - The response header `x-lead-event` is present; its value is withheld.

## API-02 Flash messages — `GET /api/flash-messenger/clients/me/messages` (#35)
- **Observation:** response `{"results": []}`, an empty list for this member.
- **Confidence:** HIGH · **Classification:** CONFIRMED (envelope). Item schema: UNKNOWN (empty).
- **Limitations:** the message item structure couldn't be observed.

## API-03 GraphQL (persisted query) — `POST /api/graphql/` (#36)
- **Observation:**
  - **Batched request** (an array) of one item containing **only an `id`**: `[{"id": "str"}]`. There's no query text and no variables, so it's a persisted or registered query.
  - **Response:** an array of one GraphQL result: `[{"data": {"dataLayer": { … 24 keys … }}}]`. The keys are: clientID, clientType, component, country, domain, gender, generatedUrl, hashedEmail, isPlusCustomer, languageId, lead, leadLp, lpName, lpRedirect, myfilter, nwlConsent, nwlStatus, reco, registrationDate, social, userrole, visitorExistingCustomer, visitorID, zalandoType.
  - **Request headers** (names only): `x-xsrf-token`, `x-zalando-experiments`, `x-zalando-feature`, `x-zalando-intent-context`, `x-zalando-request-uri`, `x-device-type`, `ot-tracer-traceid`, `ot-tracer-spanid`, `ot-tracer-sampled`.
  - The page HTML embeds a `graphqlCache` object keyed by query ID (document #0), i.e. server-side prefetched GraphQL results.
- **Confidence:** HIGH (shape, headers) / MEDIUM ("persisted query" interpretation) · **Classification:** CONFIRMED (batch array and ID-only body; `graphqlCache` present) / HYPOTHESIS (persisted-query mechanism; OpenTracing-style distributed tracing from the `ot-tracer-*` header names)
- **Limitations:**
  - Only one operation was observed, and it's analytics-oriented.
  - Business queries (campaigns, products) are UNKNOWN. They may use GraphQL or other APIs.
  - The `dataLayer` contains personal data; values withheld.

## API-04 Cart — `GET /api/phoenix/stockcart/cart` (#42)
- **Observation:**
  - **Response:** `204 No Content` with no body, and `cache-control: no-cache, no-store, max-age=0, must-revalidate`, `expires: 0`, `x-frame-options: DENY`, `x-content-type-options: nosniff`.
  - **Request headers** (names only): `client_type`, `x-enable-unreserved-cart`, `x-requested-with`.
  - The HAR was recorded with an empty cart: the Phase 02 item had been removed.
- **Confidence:** HIGH (status, headers) / MEDIUM (204 = empty cart) · **Classification:** CONFIRMED (status) / HYPOTHESIS:
  - 204 means "no cart"
  - "stockcart" refers to the stock-reservation cart seen in Phase 02 (M14 BR-14-01)
  - `x-enable-unreserved-cart` signals that a non-reserved cart variant exists
- **Limitations:**
  - The non-empty response schema is UNKNOWN.
  - The add, remove and update endpoints are UNKNOWN; no mutation was recorded.
  - "phoenix" is a service name; its meaning is UNKNOWN.

## API-05 Client performance marks — `POST /api/cmag` (#44)
- **Observation:**
  - **Request body:** a flat object of numeric timing marks, including:
    - `Zone`, `Zone:XHR`, `Zone:timers`, `Zone:ZoneAwarePromise` … (Zone.js patch timings)
    - `all-done`, `all-done-re`
    - `page-css-decodedBodySize`, `page-html-encodedBodySize` …
  - **Response:** 200 with an empty body.
  - **Request headers:** `x-page-type` and `x-xsrf-token` (value withheld).
- **Confidence:** HIGH · **Classification:** CONFIRMED (shape) / HYPOTHESIS (real-user monitoring endpoint)
- **Limitations:** the semantics of the individual marks are UNKNOWN.

## API-06 OpenTelemetry metrics — `POST /api/otlp/metrics` (#50)
- **Observation:**
  - **Request:** OTLP/JSON shape `{"resourceMetrics":[{"resource":{"attributes":…,"droppedAttributesCount":…},"scopeMetrics":[…]}]}`.
  - **Response:** `{"partialSuccess":{}}`.
  - **Response headers:** `access-control-allow-origin: https://www.zalando-prive.fr`, `access-control-allow-credentials: true`, `server: envoy`.
  - Sent with priority "VeryLow" as a `ping` (beacon).
- **Confidence:** HIGH · **Classification:** CONFIRMED (OTLP metrics over HTTP/JSON)
- **Limitations:** the metric names weren't expanded; the content is telemetry and not needed for the design.

## API-07 Bot-protection sensor (#46, #49, #51; scripts #22, #28)
- **Observation:**
  - **Requests:** three POSTs `{"body":"str"}` to a long, obfuscated first-party path, answered with `202` and an empty JSON body.
  - **Scripts:** two large first-party scripts (610 kB and 528 kB decoded) load from sibling obfuscated paths.
- **Confidence:** HIGH (traffic) / MEDIUM (purpose) · **Classification:** CONFIRMED (presence) / HYPOTHESIS (Akamai-style bot manager sensor; consistent with Phase 01 M20 and the `x-akamai-transformed` header on #0)
- **Limitations:**
  - The path and the payload are deliberately not reproduced.
  - Its behaviour wasn't analysed, and won't be: it's out of scope and not needed for the design.

## API-08 Third-party APIs
- **Braze `POST sdk.fra-01.braze.eu/api/v3/data/` (#45, #52):**
  - **Request schema** (names only): `api_key, attributes[{custom{web_last_locale}, first_name, gender, user_id}], device{browser, browser_version, locale, os_version, time_zone, user_agent}, device_id, events[{data, name, session_id, time, user_id}], respond_with{config{config_time}, triggers, user_id}, sdk_metadata, sdk_version, time, user_id`.
  - Header `x-braze-api-key` present (value withheld).
  - Both calls were **blocked** (status 0).
- **GTM** (#38, #47, #48 on googletagmanager.com; #39 a first-party `/gtm.js`): script loads only.
- **Confidence:** HIGH · **Classification:** CONFIRMED (attempted calls and shapes) / HYPOTHESIS (Braze used for CRM messaging, push or in-app)
- **Limitations:** the Braze payloads include the member's first name and IDs. Values were never output. The blocked calls mean the responses are UNKNOWN.

---

## Cross-cutting API patterns
| Pattern | Evidence | Class | Confidence |
|---|---|---|---|
| Session-scoped "me" resources (`/clients/me/…`) instead of user IDs in URLs | #34, #35 | CONFIRMED | HIGH |
| All APIs same-origin under `/api/<service>/…` behind one gateway | #34–36, #42, #44, #50 | CONFIRMED | HIGH |
| JSON request and response bodies; no XML or form encoding | all API entries | CONFIRMED | HIGH |
| GraphQL plus REST-style endpoints coexist | #36 vs #34, #35, #42 | CONFIRMED | HIGH |
| Persisted GraphQL queries plus server-embedded GraphQL cache | #36, #0 | HYPOTHESIS | MEDIUM |
| No pagination, filter or sort parameters observed | all | UNKNOWN (for the platform) | — |
| No API versioning in paths | all API paths | CONFIRMED (in sample) | MEDIUM |
| Error envelopes | no 4xx/5xx | UNKNOWN | — |

## Implications for the independent design (no reverse-engineering of internals)
These are notes for Phase 04. They aren't findings about the reference platform.
- The observed `/clients/me` style maps naturally to authenticated REST resources such as `/api/me/...` in Laravel.
- The cart, catalogue and campaign APIs must be **designed from the Phase 01–02 functional rules**, because their transport wasn't observed.
