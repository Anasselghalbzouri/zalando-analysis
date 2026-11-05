# Design validation — Phase 05

This document checks the Phase 05 architecture against the Phase 04 requirements and against itself.
- **Mechanical checks:** §1 and §2 were produced on 2026-11-04 by read-only scripts over the documentation. The scripts were session tooling and aren't part of the repository, so the checks must be **repeated or re-scripted** at implementation time and after any edit.
- **Review:** §3–§9 record the review findings.
- **Labels:** CONFIRMED / PROPOSED / UNKNOWN, as in [system-overview.md §1](system-overview.md).

## 1. Automated consistency checks (run on 2026-11-04)
| # | Check | Result |
|---|---|---|
| 1 | Every FR/NFR ID in [SRS.md](../requirements/SRS.md) is referenced **by its exact ID** in at least one design document. ADR "Related requirements" lines and range or wildcard mentions (e.g. `FR-CMP-01–10`) don't count. Each requirement's design status (designed, partial, seam, not designed) is stated in §2 | **Pass**: 95 requirements, 0 without an exact reference (§2) |
| 2 | Every requirement ID cited in the architecture exists in the SRS; every `AC-…` reference points to an existing requirement | **Pass** |
| 3 | Every Q-ID cited exists in [open-questions.md](../requirements/open-questions.md), with Phase 05 questions Q-70–Q-78 added as section G | **Pass** |
| 4 | Every BR-ID cited exists in [business-rules.md](../requirements/business-rules.md) | **Pass** |
| 5 | Every `table.column` reference in the architecture docs exists in [schema.md](database/schema.md); every FK target table exists; every source in the API field-to-schema table ([api-contracts.md §12](api/api-contracts.md)) is a table, a column or a named setting | **Pass**: 25 domain tables, plus the framework tables |
| 5a | Every column named in the index catalogue ([indexing-strategy.md §2](database/indexing-strategy.md)) exists in its table | **Pass** |
| 5b | Every entity in [erd.md §2](database/erd.md) states Purpose, Attributes, Relationships, Constraints, Lifecycle and Source | **Pass** |
| 5c | Every endpoint in [api-contracts.md](api/api-contracts.md) states method and route (heading), Purpose, Auth, Parameters, Body, Response, Validation, Errors and Pagination | **Pass**: 35 endpoints |
| 6 | Every setting returned by `GET /storefront/settings` is listed in the policy-values table ([system-overview.md §7](system-overview.md)) | **Pass** |
| 7 | All relative Markdown links resolve | **Pass** |
| 8 | No application code: code fences are only `mermaid`, `json` (shapes) or `text` (directory trees); the `.mmd` files are raw Mermaid without fences | **Pass** |
| 9 | Each of the 8 Phase 04 HYPOTHESIS requirements is named in the overview's PROPOSED-pending-PO list | **Pass** |
| 10 | No personal data or secrets: no e-mail addresses, no long hex tokens | **Pass** |
| 11 | The original HAR is unchanged: SHA-256 `03456b1bea1c19d866c59f2d11185f7b90a6c06e871545ba4239e33c7e68de6a`, the same value as recorded in Phase 03 | **Pass** |

**Limit of the checks:** they prove that references and names are consistent. They **don't** prove that a design is correct. That's the job of the review below and of the implementation-phase tests (backend-architecture §17).

## 2. Requirements coverage
"Covered in" lists the documents that address the requirement. Short names:
- `overview`, `erd`, `schema`, `indexes`
- `backend`, `frontend`, `api`, `errors`
- `auth`, `docker`
- `ADR-0nn`

| Requirement | Phase 04 class | Priority | Covered in | Design status |
|---|---|---|---|---|
| FR-NAV-01 | CONFIRMED | Must | frontend | Designed |
| FR-NAV-02 | CONFIRMED | Should | frontend | Designed |
| FR-NAV-03 | CONFIRMED | Must | api, erd, frontend, overview, schema | Designed |
| FR-NAV-04 | CONFIRMED | Should | api, erd, frontend, indexes, overview, schema | Designed |
| FR-NAV-05 | CONFIRMED | Must | api, erd, frontend | Designed |
| FR-EVT-01 | CONFIRMED | Must | api, frontend, indexes, schema | Designed |
| FR-EVT-02 | CONFIRMED | Must | api, erd, indexes, overview | Designed |
| FR-EVT-03 | HYPOTHESIS | Could | api, erd, overview, schema | Designed as PROPOSED, pending PO validation (feature switch or seam) |
| FR-EVT-04 | CONFIRMED | Should | erd, schema | Designed |
| FR-EVT-05 | HYPOTHESIS | Could | api, erd, frontend, overview | Designed as PROPOSED, pending PO validation (feature switch or seam) |
| FR-CMP-01 | CONFIRMED | Must | frontend | Designed |
| FR-CMP-02 | CONFIRMED | Must | ADR-009, api, backend, frontend, overview | Designed |
| FR-CMP-03 | CONFIRMED | Must | indexes | Designed |
| FR-CMP-04 | CONFIRMED | Should | erd | Designed |
| FR-CMP-05 | CONFIRMED | Must | backend, erd, indexes | Designed |
| FR-CMP-06 | CONFIRMED | Must | api, erd, frontend, indexes, overview, schema | Designed |
| FR-CMP-07 | CONFIRMED | Should | erd, indexes, overview, schema | Designed |
| FR-CMP-08 | HYPOTHESIS | Could | api, backend, erd, errors, overview | Designed as PROPOSED, pending PO validation (feature switch or seam) |
| FR-CMP-09 | CONFIRMED | Should | erd | Designed |
| FR-CMP-10 | CONFIRMED | Should | schema | Designed |
| FR-UPC-01 | CONFIRMED | Should | api, erd, frontend, indexes, overview | Designed |
| FR-UPC-02 | CONFIRMED | Should | frontend, overview | Designed; threshold is a setting (Q-35) |
| FR-UPC-03 | CONFIRMED | Should | api, backend | Designed |
| FR-UPC-04 | HYPOTHESIS | Could | api, overview | Not designed (HYPOTHESIS; behaviour OUT OF SCOPE, Q-47) |
| FR-CAT-01 | CONFIRMED | Must | api, frontend, indexes | Designed |
| FR-CAT-02 | CONFIRMED | Must | api, erd, overview, schema | Designed |
| FR-CAT-03 | CONFIRMED | Must | erd, indexes | Designed |
| FR-CAT-04 | CONFIRMED | Must | erd, frontend, indexes, overview | Designed |
| FR-CAT-05 | CONFIRMED | Should | indexes | Designed |
| FR-CAT-06 | CONFIRMED | Should | erd, indexes, overview, schema | Designed |
| FR-CAT-07 | CONFIRMED | Should | api, indexes | Designed |
| FR-CAT-08 | CONFIRMED | Should | api | Designed |
| FR-CAT-09 | CONFIRMED | Must | overview | Designed (60 per page); UI pattern UNKNOWN (Q-11) |
| FR-PDP-01 | CONFIRMED | Must | api, erd, overview | Designed |
| FR-PDP-02 | CONFIRMED | Must | ADR-005, api, backend, erd, errors, frontend, overview | Designed |
| FR-PDP-03 | CONFIRMED | Must | api, backend, erd, overview | Designed |
| FR-PDP-04 | HYPOTHESIS | Could | api, backend, overview | Designed as PROPOSED, pending PO validation (feature switch or seam) |
| FR-PDP-05 | CONFIRMED | Must | ADR-005, api, erd, indexes, overview | Designed |
| FR-PDP-06 | CONFIRMED | Should | erd | Designed |
| FR-PDP-07 | CONFIRMED | Must | api, overview | Designed; value is a setting (Q-31) |
| FR-PDP-08 | CONFIRMED | Should | erd, indexes, overview, schema | Designed |
| FR-SRC-01 | CONFIRMED | Must | ADR-008, api, backend, erd, indexes, overview, schema | Designed |
| FR-SRC-02 | CONFIRMED | Must | ADR-008, api, erd, frontend, indexes | Designed |
| FR-BRD-01 | CONFIRMED | Should | api, erd, frontend, indexes, overview, schema | Designed |
| FR-BRD-02 | CONFIRMED | Should | api, erd, frontend, overview, schema | Designed |
| FR-PRC-01 | CONFIRMED | Must | erd, overview | Designed |
| FR-PRC-02 | CONFIRMED | Should | api, backend, erd, overview | Designed |
| FR-STK-01 | CONFIRMED | Must | ADR-005, api, erd, indexes, overview | Designed |
| FR-STK-02 | CONFIRMED | Must | ADR-005, erd, overview, schema | Designed |
| FR-STK-03 | HYPOTHESIS | Could | api, backend, overview | Designed as PROPOSED, pending PO validation (feature switch or seam) |
| FR-CRT-01 | CONFIRMED | Must | api, frontend, indexes | Designed |
| FR-CRT-02 | CONFIRMED | Must | ADR-009, api, backend, frontend, indexes, overview | Designed |
| FR-CRT-03 | CONFIRMED | Must | api, backend, overview | Designed |
| FR-CRT-04 | CONFIRMED | Must | api, backend, erd, frontend, overview | Designed |
| FR-CRT-05 | CONFIRMED | Must | api, backend | Designed |
| FR-CRT-06 | CONFIRMED | Must | api, frontend | Designed |
| FR-CRT-07 | CONFIRMED | Must | api, overview | Designed up to the hand-off; checkout OUT OF SCOPE |
| FR-CRT-08 | HYPOTHESIS | Could | ADR-010, backend, docker, erd, indexes, overview | Designed as PROPOSED, pending PO validation (feature switch or seam) |
| FR-ACC-01 | CONFIRMED | Should | api, backend, frontend | Designed; personal-details fields beyond first name UNKNOWN (Q-41); Adresses not designed (C-02) |
| FR-ACC-02 | CONFIRMED | Should | api, erd, frontend, overview, schema | Designed |
| FR-ACC-03 | CONFIRMED | Should | api, frontend, overview | Partial: empty state only; order data owned by checkout (C-04) |
| FR-ACC-04 | CONFIRMED | Should | api, frontend, overview | Partial: entry pages only; flow UNKNOWN (Q-38), data owned by checkout (C-04) |
| FR-ACC-05 | CONFIRMED | Should | api, backend, erd, frontend, overview | Designed |
| FR-ACC-06 | CONFIRMED | Should | api, backend, erd, frontend, overview | Designed |
| FR-ACC-07 | CONFIRMED | Should | api, backend, erd, frontend, indexes, overview, schema | Designed |
| FR-ACC-08 | CONFIRMED | Must | api, auth, backend, erd, frontend, indexes, overview | Designed; login methods UNKNOWN (Q-40) |
| FR-REC-01 | CONFIRMED | Should | api, backend, frontend, overview | Partial: rails and endpoints designed; logic UNKNOWN, v1 returns empty (Q-29) |
| FR-REC-02 | CONFIRMED | Should | api, erd, indexes, overview, schema | Designed with PROPOSED strategy (reservations, no region, Q-78) |
| FR-DLV-01 | CONFIRMED | Should | api, erd, frontend, overview | Designed |
| FR-DLV-02 | CONFIRMED | Must | overview | Designed; value is a setting (Q-31) |
| FR-CNT-01 | CONFIRMED | Should | frontend | Designed |
| FR-CNT-02 | CONFIRMED | Should | api, erd, frontend, indexes, overview | Designed |
| FR-CNT-03 | CONFIRMED | Must | api, erd, frontend, overview, schema | Designed |
| FR-CNT-04 | CONFIRMED | Must | diagrams/system-context.mmd, frontend, overview | Partial: entry point designed; CMP tool UNKNOWN |
| FR-CNT-05 | CONFIRMED | Could | ADR-003, auth, frontend | Designed (footer links); no apps exist |
| FR-ANL-01 | CONFIRMED | Could | frontend | Designed |
| FR-OOS-01 | OUT OF SCOPE | Won't (this spec) | api | Not designed (OUT OF SCOPE) |
| FR-OOS-02 | OUT OF SCOPE | Won't (this spec) | api, frontend | Not designed (OUT OF SCOPE) |
| NFR-TECH-01 | CONFIRMED | Must | overview | Designed |
| NFR-I18N-01 | CONFIRMED | Must | frontend | Designed |
| NFR-I18N-02 | CONFIRMED | Must | backend, errors, frontend, overview | Designed |
| NFR-I18N-03 | CONFIRMED | Must | ADR-009, frontend, overview | Designed |
| NFR-TIME-01 | UNKNOWN | Must | backend, overview | Seam only (setting or placeholder); value UNKNOWN |
| NFR-A11Y-01 | CONFIRMED | Must | api, erd, frontend, overview, schema | Designed |
| NFR-SEC-01 | CONFIRMED | Must | ADR-003, api, auth, backend, erd, overview | Designed |
| NFR-SEC-02 | CONFIRMED | Must | ADR-003, api, auth, frontend, overview | Designed |
| NFR-SEC-03 | CONFIRMED | Must | ADR-003, api, auth, backend, indexes | Designed |
| NFR-SEC-04 | CONFIRMED | Must | auth, backend, docker, errors, overview | Designed |
| NFR-SEC-05 | HYPOTHESIS | Could | auth, backend, overview | Partial: rate limits designed; bot defence pending PO (Q-42) |
| NFR-PRV-01 | CONFIRMED | Must | auth, backend, diagrams/system-context.mmd, erd, frontend, overview, schema | Designed |
| NFR-CON-01 | CONFIRMED | Must | ADR-001, ADR-005, api, backend, erd, errors, frontend, indexes, overview, schema | Designed |
| NFR-CACHE-01 | CONFIRMED | Should | frontend | Designed |
| NFR-OBS-01 | CONFIRMED | Could | api, backend, errors, frontend | Designed |
| NFR-PERF-01 | UNKNOWN | Must | indexes | Seam only (setting or placeholder); value UNKNOWN |
| NFR-RESP-01 | UNKNOWN | Must | frontend | Seam only (setting or placeholder); value UNKNOWN |

**Summary of design status:** Designed: 76; Designed as PROPOSED, pending PO validation: 6; Designed up to the hand-off: 1; Designed with PROPOSED strategy: 1; Not designed: 3; Partial: 5; Seam only: 3.

## 3. Entity–relationship consistency
| Check | Finding | Status |
|---|---|---|
| Every ERD entity has a table | Yes. 25 domain tables in schema.md; the framework tables are listed separately | OK |
| Every relationship has a declared FK and an explicit delete rule | Yes. `RESTRICT` by default; `CASCADE` only for owned rows (images, variants, preferences, a deleted user's reservations) | OK |
| A variant and its article share one product | Enforced by composite FKs through `inventory_items.product_id` (ADR-005) | OK |
| A variant's size value belongs to the product's size system | **Service-level only** (Catalog service and Form Request). A DB-level rule would need a trigger | Accepted risk: test it |
| A saved size value belongs to the profile's system | Service-level only (`SaveSizeProfilesRequest`) | Accepted risk: test it |
| Single default landing | UQ on the nullable `default_flag` | OK |
| Campaign state | Derived (no column), so it can't contradict the clock | OK |
| `quantity_reserved` vs `cart_reservations` | Two representations of one fact (a justified counter). Changed only by `ReserveUnit`/`ReleaseUnit` in one transaction; a reconciliation command is PROPOSED | Monitor |
| `products.search_text` | Denormalised for FULLTEXT; rebuilt by a queued listener on product or brand changes. Search may lag edits by seconds | Accepted |
| No orphan tables | `article_reservation_stats` is the only strategy-specific table. Remove it if the PO picks another popularity strategy (Q-78) | OK |

## 4. API ↔ database consistency
- **Field traceability:** every response field traces to a column, a derived value (state, availability, headline) or a setting ([api-contracts.md §12](api/api-contracts.md), check 5).
- **Request fields** map to columns: `sku` → `campaign_articles.sku`; `variant_id` → `product_variants.id`; `reason_id` → `unsubscribe_reasons.id`; `size_value_ids` → `size_values.id`; filters → `size_values`, `brands.code`, `attribute_values.slug`, `categories.id`, `campaign_articles.sale_price_cents`.
- **One name per concept:** the API uses `variant_id` and `size_value_ids` throughout, the backend Form Requests use the same names, and the earlier `campaign_article_id` wording was aligned during this phase.
- **Upcoming tiles** expose no campaign ID, consistent with FR-UPC-01 / BR-03-11. The campaign endpoints return 404 for not-yet-started campaigns, so the ID can't be probed for content.
- **`products` vs `articles`:** there's no product-only endpoint, because a product without a campaign has no price (ADR-004). The brief's candidate route `/api/v1/products` is replaced, and this is recorded in api-contracts §11.

## 5. Authentication consistency
| Check | Finding |
|---|---|
| Same origin everywhere | Dev (Nginx → Vite and PHP-FPM) and production (Nginx serving the build) both use one origin. Sanctum's stateful domains are limited to it |
| CSRF on all unsafe methods | The API conventions (§1), the front-end base query (`X-XSRF-TOKEN`), the error format (419) and the auth design agree |
| No user IDs in URLs | All member endpoints are `me`/`account`/`cart`-scoped; policies return 404 for foreign resources |
| `no-store` | Cart, account, `me`, auth and article detail (availability) are marked in api-contracts §1 and in the auth design §7 |
| Roles | Only `member` is used in v1. `merchandiser`/`admin` gates exist but are unused (Q-50). No role is ever exposed to the SPA in `User` |
| Catalogue access | `catalog.access` middleware (backend §8), the route table (frontend §3) and the API auth column **C** all refer to the same `catalog_access` setting (Q-71) |
| Reference not copied | No reference cookie names, tokens, identity provider or session settings are used. Patterns are generic Laravel features |

## 6. Module dependencies
- **Direction:** Catalog ← Sales ← Inventory ← Cart, with Listing reading from Sales, Catalog and Inventory, and Recommendations reading from Listing. Recommendations reacts to Cart **events**, so Cart never depends on it.
- **No cycles:** the component diagram, the backend module table and the Phase 02 [dependencies.md](../requirements/dependencies.md) build order agree.
- **Account → Catalog** (size systems and values) is read-only.
- **Checkout** depends on Cart and Inventory through two module contracts (api-contracts §10). Neither depends back on checkout.
- **Enforcement:** architecture tests (backend §2, §17).

## 7. Security considerations
| Area | Design | Residual risk / open point |
|---|---|---|
| Overselling and race conditions | Conditional atomic update; unique keys; one transaction (ADR-005) | Verify with the concurrency test (indexing-strategy §6) |
| Reservation hoarding | Quantity limit, per-user and per-IP cart limiters | Bot defence at launch time is pending (NFR-SEC-05, Q-42) |
| XSS | React escaping, strict CSP, server-sanitised help and legal HTML, HTTP-only session | The CSP must be extended deliberately when the CMP and analytics are chosen |
| CSRF, clickjacking, fixation | SameSite=Lax + XSRF header, `frame-ancestors 'none'`, session regeneration | — |
| Enumeration | Generic login errors; forgot-password always 202; 404 on foreign or upcoming resources | — |
| Privacy | Opt-ins off by default; minimal profile; no birth date stored; masked logs; consent-gated analytics with a neutral link-source parameter | Account deletion and retention UNKNOWN (Q-73) |
| Secrets | Environment and secret store only; nothing secret in `VITE_*`; `.env` never committed | — |
| Error leakage | Problem details without internals; a request ID for support | — |
| Dependency surface | No extra search engine, no permissions package, no form or i18n libraries | — |

## 8. Performance considerations
- **No targets:** none exist (NFR-PERF-01 UNKNOWN, Q-42), so the design makes **no numeric performance claims**.
- **What it provides instead:**
  - indexes per access path (indexing-strategy §2–§3)
  - landing and facet caches invalidated by campaign boundary events, with a TTL capped at the next boundary
  - availability is never cached
  - route-level code splitting and immutable hashed assets
  - a single ticker for all countdowns
- **Known hot spots to measure first:**
  - add-to-cart on popular units at launch time (row-lock contention on one `inventory_items` row)
  - cross-campaign listings with several filters (in-memory sort)
  - facet queries on large campaigns (≈ 2,000 articles)
  - the database session and queue drivers under load
- **Upgrade paths:** all are configuration-level or behind interfaces:
  - Redis for sessions, cache, queue and locks (ADR-007)
  - keyset pagination (indexing §3.2)
  - a search engine behind the same interface (ADR-008)
  - read replicas for the listing endpoints

## 9. Contradictions and unresolved assumptions
The register is repeated from [system-overview.md §10](system-overview.md). Nothing is silently resolved.

| ID | Item | Design handling | Class | Question |
|---|---|---|---|---|
| C-01 | Wishlist candidate, no evidence | Not designed | UNKNOWN | Q-76 |
| C-02 | Address candidate, its consumer out of scope | Not designed | UNKNOWN | Q-41, Q-04 |
| C-03 | Three published return periods | One setting | UNKNOWN | Q-31 |
| C-04 | Orders and returns pages need checkout data | Empty states and a boundary only | UNKNOWN | Q-04, Q-38 |
| C-05 | Guest access vs "membership required" | `catalog_access` setting | UNKNOWN | Q-28, Q-71 |
| C-06 | Product granularity (model vs model + colour; same item in two campaigns) | Schema allows it; PROPOSED | PROPOSED | Q-70 |
| C-07 | Per-campaign stock allocation is a HYPOTHESIS | Stock per article × size | PROPOSED | Q-70 |
| C-08 | Expiry and the live-window guard are HYPOTHESIS but needed | Designed with feature switches, pending PO | PROPOSED | Q-37, Q-17 |
| C-09 | Display timezone unknown | UTC storage + setting | UNKNOWN | Q-10 |
| C-10 | Recommendation logic unknown; "région" has no data source | Provider interface; empty, or the PROPOSED popular strategy | UNKNOWN | Q-29, Q-78 |
| C-11 | Reference GraphQL vs mandated REST | REST only | CONFIRMED (stack) | — |
| C-12 | No load targets to justify Redis | Optional profile | PROPOSED | Q-42, Q-72 |
| C-13 | Listing beyond 60 unknown | Page-based API with a total | UNKNOWN | Q-11 |
| C-14 | "Préférés" meaning unknown | Merchandising position placeholder | UNKNOWN | Q-43 |
| C-15 | Discount rounding unknown | Floor (PROPOSED) | UNKNOWN | Q-77 |
| C-16 | Back-office unspecified | Services exist; seeders/imports in v1 | UNKNOWN | Q-50 |
| C-17 | Product name and branding undefined | Neutral naming | UNKNOWN | Q-75 |

**Design assumptions made in this phase (PROPOSED, reversible):**
- one category per product
- one article per (campaign, product)
- no price per size
- released reservations are deleted, with no history
- opt-ins default to off
- the birth date isn't stored (an 18+ confirmation only)
- unverified members may reserve (Q-41)
- brand suggestions only for brands with live articles
- facet counts ignore their own facet

## 10. Readiness for implementation
| Area | Ready? | Needs first |
|---|---|---|
| Catalogue, sales, landings, listing, PDP, search | **Ready to build** | Q-70 (granularity) before the migrations are frozen |
| Cart and inventory | **Ready to build** | PO decisions on Q-25/Q-32/Q-46 (timer and limits) and on the FR-CMP-08 / FR-CRT-08 switches |
| Account (newsletter, saved sizes, profile) | **Ready to build** | Reason list values; Q-41 for profile fields |
| Authentication | **Ready to build** | Q-40 (login methods), session lifetime (PO) |
| Recommendations | **Endpoints ready; logic blocked** | Q-29, Q-78 |
| Content and legal | **Ready to build** | Legal texts (Q-48) |
| Docker environment | **Ready to write compose files** | Q-72 for the Redis decision in shared environments |
| Checkout, back-office, notify-me, wishlist, addresses | **Not designed** | Separate specifications (Q-04, Q-50, Q-47, Q-76, C-02) |
| Responsive layouts, performance targets | **Structure only** | Q-15, Q-42 |
