# Phase 03 — Authentication analysis (conceptual, HAR)

**Source:** `evidence/har/zalando`. The `#n` notation, labels and limitations are as in [network-analysis.md](network-analysis.md).

**Secrets policy applied:** no cookie, token, XSRF, API-key, session or personal value was displayed or saved. This document describes **mechanisms only**.

## What the HAR contains, and what it doesn't
| Item | Present? | Notes |
|---|---|---|
| `Cookie` / `Set-Cookie` headers | **No**: 0 of 53 entries; `cookies[]` empty everywhere | The DevTools export omitted cookies (sanitized export). Cookie use can't be confirmed from the HAR. |
| `Authorization` header | **No** | — |
| `X-XSRF-Token` request header | **Yes**: 2 entries (#36 GraphQL, #44 `/api/cmag`) | Value withheld |
| Braze `x-braze-api-key` / `api_key` | Yes: #45, #52 (blocked calls) | A public SDK key by design; value withheld anyway |
| Login / logout / token refresh traffic | **No** | The session was already authenticated |

## A-01 The session is authenticated and resolved server-side
- **Observation:**
  - **Rendering:** the account page (#0) rendered for a logged-in member: the `header-logged-in` fragment loads (#37) and the page is `/myaccount/returns`.
  - **APIs:** `/clients/me/` APIs (#34, #35) returned member-specific data with **no user identifier in the URL and no `Authorization` header**.
- **Confidence:** HIGH · **Classification:** CONFIRMED (the server identifies the member without a bearer token in the captured headers) / HYPOTHESIS (identity carried by an HTTP-only session cookie omitted from the export, since nothing else in the request could carry it)
- **Limitations:** with cookies stripped, the exact credential (session cookie vs another mechanism) is UNKNOWN.

## A-02 CSRF protection via a double-submit-style header
- **Observation:**
  - **Header present:** `X-XSRF-Token` on two **state-changing or POST** first-party calls (#36 GraphQL, #44 `/api/cmag`).
  - **Header absent:** GET calls (#34, #35, #42); the other first-party POSTs (#46, #49, #51 sensor; #50 OTLP beacon).
- **Confidence:** HIGH (presence) / MEDIUM (pattern) · **Classification:** CONFIRMED (an XSRF token header is sent on selected POSTs) / HYPOTHESIS (cookie-to-header "double submit" CSRF scheme; the `XSRF-TOKEN` → `X-XSRF-TOKEN` naming is a common convention)
- **Limitations:**
  - The token's source (cookie, meta tag, API) is UNKNOWN.
  - Whether the server rejects requests without it wasn't tested and won't be: that would mean replaying requests.

## A-03 Same-origin API design reduces the cross-origin surface
- **Observation:**
  - **Same origin:** all first-party APIs are same-origin (`sec-fetch-site: same-origin`). No CORS preflight (OPTIONS) appears.
  - **CORS headers:** only `/api/otlp/metrics` (#50) returns them, `access-control-allow-origin: https://www.zalando-prive.fr` with `allow-credentials: true`.
  - **Framing:** the document sends `x-frame-options: SAMEORIGIN` (#0), the cart API sends `DENY` (#42), and a CSP header is present (value withheld).
- **Confidence:** HIGH · **Classification:** CONFIRMED
- **Limitations:** the CSP contents weren't analysed in detail.

## A-04 Sensitive responses are not cacheable
- **Observation:**
  - **HTML:** `no-cache, no-store, must-revalidate` (#0).
  - **Cart:** `no-cache, no-store, max-age=0, must-revalidate` + `expires: 0` (#42).
  - **Sensor:** `max-age=0, no-cache, no-store` (#46).
- **Confidence:** HIGH · **Classification:** CONFIRMED
- **Limitations:** the cache headers of API-01, API-02 and API-03 aren't set explicitly in the sample.

## A-05 Identity is shared with the Zalando group (cross-reference)
- **Observation:**
  - **Data shape:** the analytics data layer includes `hashedEmail`, `clientID`, `visitorID` and `userrole` (#36), and the tracking API includes `customer_hash` and `x_zalando_lounge_id` (#34). Values withheld.
  - **Phase 02 help page:** "si vous êtes déjà client·e chez Zalando, vous pouvez vous connecter en utilisant les mêmes identifiants", with Facebook, Gmail and Apple login offered (M15 BR-15-01).
  - **Account nav:** links out to `zalando.fr/plus` for rewards.
- **Confidence:** MEDIUM · **Classification:** CONFIRMED (shared credentials per the help text; hashed identifiers in analytics) / HYPOTHESIS (a central Zalando identity provider with SSO across zalando.fr / Privé)
- **Limitations:** no login flow, redirect chain or IdP host was captured. The protocol (OAuth 2.0 / OIDC…) is UNKNOWN.

## A-06 Bot protection is part of the security model
- **Observation:** an obfuscated first-party sensor (scripts #22, #28; beacons #46, #49, #51) runs on authenticated pages.
- **Confidence:** MEDIUM · **Classification:** CONFIRMED (presence) / HYPOTHESIS (Akamai Bot Manager–type protection; consistent with Phase 01, where an automated fresh browser failed to load the site)
- **Limitations:** not analysed, by design.

## A-07 Personal data exposure surfaces (for privacy design)
- **Observation:**
  - **Analytics feeds:** member attributes go client-side to analytics: hashed email, gender, loyalty tier, newsletter status, registration date (#34, #36).
  - **Braze:** the member's first name and IDs are sent to Braze (#45, #52; blocked in this session).
- **Confidence:** HIGH · **Classification:** CONFIRMED (field names)
- **Limitations:** consent gating (Usercentrics was blocked) is UNKNOWN. With a consent tool active, the flows may differ.

---

## Unknowns that need a different capture (no secrets required)
| Unknown | How to observe safely |
|---|---|
| Login flow, IdP host, redirect chain | A HAR of logout → login, recorded **only by the user**, then shared as a sanitized export. Never share the raw file. |
| Session lifetime and renewal | Not observable passively. Ask the PO for the new app's policy. |
| CSRF enforcement | Not testable without replaying requests, which is out of scope. |

## Design notes for the independent Laravel/React app
These aren't findings about the reference platform.
- **Proposed model:**
  - **Session:** cookie-based (HTTP-only, Secure, SameSite).
  - **CSRF:** XSRF-TOKEN cookie → `X-XSRF-TOKEN` header. This is the default in Laravel Sanctum's SPA mode.
  - **APIs:** same-origin, with `/me` resources.
- **Mapping:** the observed patterns (A-01–A-04) map to this approach directly. The reference's actual implementation remains UNKNOWN.
