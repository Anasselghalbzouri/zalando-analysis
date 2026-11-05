# Phase 03 — Network analysis (HAR)

## Source
| | |
|---|---|
| **Source HAR** | `evidence/har/zalando` (Chrome DevTools "WebInspector 537.36", HAR 1.2, 5 147 451 bytes) |
| **SHA-256** | `03456b1bea1c19d866c59f2d11185f7b90a6c06e871545ba4239e33c7e68de6a` (unchanged; read-only) |
| **Sanitized derivative** | `evidence/har-sanitized/zalando.summary.json`. Per request: method, host, masked path, query-parameter **names**, status, timings, and header **names**. Values are kept only for an allowlist of non-sensitive headers. Bodies are reduced to key/type schemas. |
| **Scope** | **One page load** of `https://www.zalando-prive.fr/myaccount/returns` by a logged-in member, 2026-11-04 17:53:08 → 17:53:33 UTC (≈ 25.5 s), 53 requests. |
| **Evidence notation** | `#n` = entry index *n* (0-based) in `evidence/har/zalando`, which is the same index as in the sanitized summary. |

**Labels:**
- **Confidence:** HIGH = read directly from the HAR. MEDIUM = small inference or a sample of 1. LOW = weak signal.
- **Classification:** CONFIRMED / HYPOTHESIS / UNKNOWN.

**What this single HAR can't show** (applies to every finding below):
- catalogue, campaign, product, search and cart-mutation traffic
- login and logout
- mobile behaviour
- repeated loads (a warm-cache bias is present)

**Handling:** cookie, auth, XSRF and Braze key values, and every body value, were never printed or saved.

---

## N-01 Request count and classification
- **Observation:** 53 requests in total.
  - **By type:** script 25 · image 12 · fetch 5 · xhr 5 · font 3 · stylesheet 1 · document 1 · ping 1.
  - **By method:** GET 45 · POST 8.
- **Evidence:** all entries, `_resourceType` and `request.method`.
- **Confidence:** HIGH · **Classification:** CONFIRMED
- **Limitations:** this is one account page. Other page types will differ in mix and volume.

## N-02 First-party and third-party domains
| Host | Requests | Role (observed) | Party |
|---|---|---|---|
| `www.zalando-prive.fr` | 13 (#0, #22, #28, #34–36, #39, #42, #44, #46, #49–51) | HTML, first-party APIs, first-party GTM proxy, bot-sensor scripts and beacons | First party |
| `mosaic01.ztat.net` | 21 (#1–7, #21, #23–27, #30–33, #37, #40, #41, #43) | Fonts, CSS, JS bundles ("rendering-engine", "lux"), consent UI | Zalando-operated asset CDN. HYPOTHESIS: based on the `ztat` naming and the `ZalandoSans` fonts |
| `images.ctfassets.net` | 12 (#9–20) | SVG logos (payment, social, carriers, app badges) | Third party (Contentful CDN) |
| `www.googletagmanager.com` | 3 (#38, #47, #48) | `gtm.js`, `gtag/js` | Third party |
| `sdk.fra-01.braze.eu` | 2 (#45, #52) | Braze SDK data calls (**blocked**) | Third party |
| `js-de.sentry-cdn.com` | 1 (#8) | Sentry loader (**blocked**) | Third party |
| `app.usercentrics.eu` | 1 (#29) | Usercentrics CMP loader (**blocked**) | Third party |
- **Confidence:** HIGH (hosts and counts) / MEDIUM (role of `ztat.net`) · **Classification:** CONFIRMED / HYPOTHESIS for ownership of `ztat.net`.
- **Limitations:** only the hosts contacted on this page. The catalogue image CDN (product photos) isn't present.

## N-03 Endpoint grouping (method + path)
| Group | Method | Path (sanitized) | Entries |
|---|---|---|---|
| Page document | GET | `/myaccount/returns` | #0 |
| Tracking data | GET | `/api/tracking/clients/me/events/ga4` | #34 |
| Flash messages | GET | `/api/flash-messenger/clients/me/messages` | #35 |
| GraphQL | POST | `/api/graphql/` | #36 |
| Cart | GET | `/api/phoenix/stockcart/cart` | #42 |
| Client performance metrics | POST | `/api/cmag` | #44 |
| OpenTelemetry metrics | POST | `/api/otlp/metrics` | #50 |
| Bot-protection sensor | POST ×3 / GET (script) ×2 | `/<obfuscated-sensor-path>` (deliberately not reproduced) | #46, #49, #51 / #22, #28 |
| First-party GTM proxy | GET | `/gtm.js` | #39 |
| Braze | POST | `sdk.fra-01.braze.eu/api/v3/data/` | #45, #52 |
- **Confidence:** HIGH · **Classification:** CONFIRMED
- **Limitations:** no request carried a query string (`queryString` is empty for every API call). See N-06.

## N-04 HTTP status codes
| Status | Count | Entries | Meaning here |
|---|---|---|---|
| 200 | 43 | most | OK, including 33 served from the memory cache |
| 202 | 3 | #46, #49, #51 | Sensor beacons accepted, empty body |
| 204 | 1 | #42 | Cart endpoint: **no content**, consistent with an empty cart (see API-05) |
| 304 | 2 | #21 (`gtm-loader-2.0.0.js`), #39 (`/gtm.js`) | Conditional revalidation |
| 0 | 4 | #8, #29, #45, #52 | `net::ERR_BLOCKED_BY_CLIENT`: blocked in the browser (HYPOTHESIS: Brave Shields) |
- **Confidence:** HIGH · **Classification:** CONFIRMED (codes) / HYPOTHESIS (blocker identity)
- **Limitations:** no 3xx redirect, 4xx or 5xx was observed. Error handling remains UNKNOWN.

## N-05 Timing
- **Observation:**
  - **Document (#0):** 2 974 ms total, of which 2 949 ms is `wait`, i.e. server time / TTFB. The transfer was 32 483 bytes (112 804 bytes decoded).
  - **First-party API fetches** (#34–36, #42, #44): started together at ≈ +3.7 s and took 2.4–2.9 s each, almost all of it `wait`.
  - **Sensor beacons:** at +5.3 s, +6.7 s and +15.3 s (502–1 646 ms).
  - **OTLP ping:** at +8.5 s.
  - **Last request:** at +25.5 s (#52, blocked).
  - **Connections:** DNS, connect and SSL are `-1` for every entry, so all connections were reused or pre-established.
  - **Caching:** 33 of 53 responses came from the memory cache and 1 from disk. Only 15 had a transfer size above 0.
- **Evidence:** `timings`, `time`, `_fromCache`, `_transferSize` per entry. Aggregates are in the sanitized summary.
- **Confidence:** HIGH (values) / LOW (as performance indicators) · **Classification:** CONFIRMED
- **Limitations:** this is a single sample on a warm cache, and DevTools may have been open with throttling off. The ≈ 2.4–3 s server waits are notable, but **one sample can't establish typical latency**.

## N-06 Pagination, filtering and sorting
- **Observation:** none. No query parameters and no paging fields appear in any request or response schema.
- **Evidence:** `queryString` is empty for all 53 entries. The schemas of #34–36 and #42 contain no page, cursor, sort or filter fields.
- **Confidence:** HIGH (absence in this HAR) · **Classification:** UNKNOWN (for the platform)
- **Limitations:** the captured page has no listing. Phase 02 UI evidence shows sort, filter and a 60-item first page (M06), but **how those are transported (query string, GraphQL variables, path) is unobserved**. That needs a HAR of a campaign listing with a filter applied.

## N-07 Protocols and transport
- **Observation:**
  - **Protocols:** HTTP/2 for 25 entries, HTTP/3 (`h3`) for 21, HTTP/1.1 for 3, and none for the 4 blocked requests.
  - **Compression:** `content-encoding` is `br` or `gzip`.
  - **HSTS:** `strict-transport-security: max-age=31536000; includeSubDomains` on first-party responses.
- **Evidence:** `httpVersion`; response headers of #0, #34, #42.
- **Confidence:** HIGH · **Classification:** CONFIRMED
- **Limitations:** the HAR doesn't record which assets negotiated `alt-svc` upgrades.

## N-08 Edge and infrastructure headers (no backend inference)
- **Observation:**
  - **Gateway header:** first-party responses carry `server: Skipper` (#0, #34–36, #42, #44), plus an `x-zalando-lounge-skipper` header (value withheld).
  - **OTLP endpoint (#50):** `server: envoy` and `x-envoy-upstream-service-time`.
  - **Document (#0):** carries `x-akamai-transformed`.
  - **Responses:** carry `x-flow-id` and `x-zalando-child-request-id`. Values withheld; these look like trace or request IDs.
  - **Per-service headers:** `x-zalando-lounge-lounge-web-tracking` (#34), `x-zalando-lounge-pegasus-flash-messenger` (#35), `x-zalando-lounge-api` (#36), `x-zalando-lounge-re` (#0). They name the upstream that served each response.
- **Confidence:** HIGH (header presence) · **Classification:**
  - **CONFIRMED:** an HTTP gateway identifying itself as "Skipper" fronts first-party routes; Envoy fronts `/api/otlp`; Akamai is involved in document delivery.
  - **HYPOTHESIS (MEDIUM):** routing to several distinct backend services (web tracking, flash messenger, a "lounge api" for GraphQL, a rendering engine "re"). The "lounge" naming suggests the platform derives from Zalando Lounge.
- **Limitations:** **the backend language, framework and database are UNKNOWN.** Nothing in the HAR identifies them, and none are inferred here.

## N-09 Asset inventory summary
Detail is in [frontend-analysis.md](frontend-analysis.md).
- **Observation:**
  - **JS:** 25 requests, ≈ 3.60 MB decoded, 11.6 kB transferred.
  - **CSS:** 1 file, 153 kB.
  - **Fonts:** 3 WOFF2, 87 kB.
  - **Images:** 12 SVG, 53 kB.
  - **Caching:** versioned assets on `mosaic01.ztat.net` use `cache-control: public,max-age=31536000,immutable`.
- **Confidence:** HIGH · **Classification:** CONFIRMED
- **Limitations:** the cache was warm, so transfer sizes understate a first visit.

---

## Missing information (needs further HARs)
| Need | Capture to record |
|---|---|
| Campaign and catalogue APIs, pagination, filters, sort | `/event` scrolled; `/campaigns/{id}` scrolled, with one filter and one sort applied |
| Product and cart mutation APIs | a PDP, then add to cart, open the cart, remove the item (as authorized in Phase 02) |
| Search API | a header search and suggestion selection |
| Error behaviour | none planned. Forcing errors is out of scope |
