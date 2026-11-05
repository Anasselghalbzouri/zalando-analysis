# Phase 03 — Front-end analysis (HAR)

**Source:** `evidence/har/zalando`. The `#n` notation, labels and limitations are as in [network-analysis.md](network-analysis.md).

**Method:** the document HTML (#0, base64 in the HAR) was decoded **in memory only**, and searched for framework markers. Only marker names and counts, global variable *names*, and module names were extracted. No user content was printed, and no source code was executed.

## F-01 Server-side rendering with client hydration
- **Observation:**
  - **HTML:** the document (#0) is delivered fully rendered: 112 kB, `lang="fr-FR"`, 24 `<script>` tags (18 inline), one `application/json` script with id `re-renderer-reporter-server`.
  - **Global variables** (names only): `__serverData`, `__hydrationData`, `__hydrationDataConsume`, `__recontext`, `__additionalRendererContext`, `__RE_CLIENT_ROOT_LOAD_SPAN`.
  - **Inline code** measures `primaryHydration`.
- **Evidence:** #0 (decoded HTML).
- **Confidence:** HIGH · **Classification:** CONFIRMED (SSR + hydration pattern)
- **Limitations:** one page type (account returns) only. Event and campaign pages may use a different composition.

## F-02 UI library: React (+ styled-components)
- **Observation:** the HTML defines a shared-module list for the client loader that includes `react`, `react-dom`, `react-dom/client`, `react-is`, `react-transition-group` and `styled-components`. It also includes internal modules: `@pegasus/tracker`, `@pegasus/rest-client`, `safe-fetch`, `@zalando-internal/z-shop-event-bus`, `@zalando-internal/zalando-consent-api`.
- **Evidence:** #0. Bundles #26 (`runtime-concurrent-<hash>.js`, 479 kB) and #27 (`re-common-chunk`, 292 kB).
- **Confidence:** HIGH (module names) / MEDIUM (that React renders the UI) · **Classification:** CONFIRMED (React and styled-components are declared shared modules) / HYPOTHESIS (React 18+ concurrent rendering, from `react-dom/client` and the "runtime-concurrent" bundle name)
- **Limitations:** library versions are UNKNOWN; bundle contents weren't decompiled.

## F-03 Module loading: AMD/RequireJS plus a "rendering engine" with per-fragment bundles
- **Observation:**
  - **Loader:** `require-2.1.22.min.js` (#5); inline `require(...)` and `define('z-shop-js-env', …)` calls.
  - **Base bundle:** `bundle-1-9-2.js` (#6).
  - **Per-fragment bundles** under `/pgs/rendering-engine/<fragment>/index-<hash>.js`:
    - `view-return_home` (#30), `header` (#31), `footer` (#32), `customer-navigation` (#33)
    - `header-logged-in` (#37), `layout_header-search` (#40), `layout_header-messages` (#41)
    - `braze-web-sdk` (#43), `@lounge/campaign-card-context` (#24), `@lounge/product-card-context` (#25)
  - **Inline Tailor runtime:** a `window.TailorPipe` function.
  - "rendering-engine" appears 107 times and "mosaic" 120 times in the HTML.
- **Evidence:** #0, #5, #6, #24–27, #30–33, #37, #40, #41, #43.
- **Confidence:** HIGH (file names, loader calls) / MEDIUM (architecture reading) · **Classification:** CONFIRMED (RequireJS loader; fragment-named bundles; `TailorPipe` present) / HYPOTHESIS (a micro-frontend / fragment composition where a server-side "rendering engine" assembles independently built fragments)
- **Limitations:** fragment boundaries were inferred from file names. Server-side composition itself isn't visible in a HAR.

## F-04 Shared UI components are scoped by "card context"
- **Observation:** the bundles `@lounge/campaign-card-context` and `@lounge/product-card-context` load even on the account page.
- **Evidence:** #24, #25.
- **Confidence:** MEDIUM · **Classification:** HYPOTHESIS (shared campaign-card and product-card components; consistent with the M03 tiles and M06 tiles from Phase 02)
- **Limitations:** contents not inspected.

## F-05 Styling, fonts, design system
- **Observation:**
  - **CSS:** one global stylesheet, `styles-1ec5b044.css` (#4, 153 kB, br).
  - **Fonts:** three WOFF2 fonts `ZalandoSans-Regular`, `-Bold`, `-RegularWide` (#1–3).
  - **Design system:** a `lux` bundle (#23, path `/pgs/rendering-engine/base-assets/lux/`); the fonts also sit under `/pgs/lux/fonts/`.
  - **Runtime styling:** styled-components (F-02).
- **Confidence:** HIGH (files) / MEDIUM ("lux" = design system) · **Classification:** CONFIRMED / HYPOTHESIS (Lux is an internal design-system package)
- **Limitations:** none beyond the sample.

## F-06 Images and CMS assets
- **Observation:**
  - **Footer logos:** all 12 images are SVGs from Contentful's image CDN (`images.ctfassets.net/<space>/<assetId>/<hash>/<name>.svg`), covering payment, social, carriers and app badges (#9–20).
  - **Caching:** `cache-control: max-age=31536000`.
  - **CMS documents:** the CGV PDF seen in Phase 02 is also on `assets.ctfassets.net`.
- **Confidence:** HIGH · **Classification:** CONFIRMED (Contentful hosts footer and legal assets) / HYPOTHESIS (Contentful is the CMS for editorial content)
- **Limitations:** no product or campaign images on this page, so the product-image CDN is UNKNOWN.

## F-07 Third-party scripts
| Script | Entry | Status |
|---|---|---|
| Google Tag Manager `gtm.js`, `gtag/js` | #38, #47, #48 | loaded |
| First-party GTM proxy `/gtm.js` (531 kB, `private, max-age=660`) | #39 | 304 |
| GTM loader `gtm-loader-2.0.0.js` | #21 | 304 |
| Zalando consent UI `consent.js` (`/pnl/consent-ui/latest/lounge/`) | #7 | loaded |
| Usercentrics `browser-ui/latest/loader.js` | #29 | blocked |
| Sentry loader | #8 | blocked |
| Braze Web SDK (bundled as a fragment) | #43 | loaded; its API calls blocked |
| Bot-protection sensor scripts (first-party obfuscated paths) | #22, #28 | loaded |
- **Confidence:** HIGH · **Classification:** CONFIRMED
- **Limitations:** the blocked scripts' runtime behaviour is UNKNOWN. A browser without content blocking would show more traffic.

## F-08 Service worker is actively unregistered
- **Observation:** an inline script calls `navigator.serviceWorker.getRegistrations()` and **unregisters every registration**. 13 entries still carry service-worker timing fields (`_workerStart` etc.), but none was `_fetchedViaServiceWorker`.
- **Evidence:** #0 (inline script); timings of #0, #21, #30, #34–36, #39, #42, #44, #46, #49–51.
- **Confidence:** HIGH (code and fields) / MEDIUM (interpretation) · **Classification:** CONFIRMED (unregister code present) / HYPOTHESIS (a legacy service worker is being retired; it was still active when this load started)
- **Limitations:** this **corrects Phase 01 M20**, which noted "a service worker is registered" as if it were an active feature. The platform appears to be removing it. Offline and push support via the service worker: UNKNOWN, likely not intended.

## F-09 Caching and versioning strategy
- **Observation:**
  - **Versioned assets:** content-hashed or versioned file names (`index-<hash>.js`, `styles-<hash>.css`, `bundle-1-9-2.js`) with `public,max-age=31536000,immutable`.
  - **Unversioned assets:** `consent.js` uses `max-age=300, must-revalidate`; `gtm-loader-2.0.0.js` uses `public, max-age=1200, must-revalidate`.
  - **HTML:** `no-cache, no-store, must-revalidate`.
- **Evidence:** response headers of #0, #4, #7, #21, #26.
- **Confidence:** HIGH · **Classification:** CONFIRMED
- **Limitations:** none.

## F-10 Instrumentation in the front end
- **Observation:**
  - **Sentry globals:** `__SentryConsent`, `__SentryEventQueue`, `__SentryGlobalTags`.
  - **Root-load span:** `__RE_CLIENT_ROOT_LOAD_SPAN`.
  - **Trace headers** on GraphQL calls (`ot-tracer-*`).
  - **Metrics:** OTLP metrics (API-06) and performance marks (API-05).
  - **Test IDs:** 8 `data-testid` attributes in the HTML.
- **Confidence:** HIGH · **Classification:** CONFIRMED (presence)
- **Limitations:** the dashboards and back-ends are outside the HAR.

## Not determinable from this HAR
- **Build tooling** (webpack, Vite…): no marker found ("webpack" count 0). UNKNOWN.
- **Routing:** client-side routing on event pages was seen in Phase 01, but the router library is UNKNOWN.
- **Responsive and mobile front end:** UNKNOWN (desktop UA only).
- **Back-end rendering technology** of the "rendering engine": UNKNOWN. Not inferred.
