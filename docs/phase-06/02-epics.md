# Epics — Phase 06

The suggested epic list from the brief is kept, numbered as given, with two changes the documentation justifies:
- **E15 Recommendations (added):** the rails are CONFIRMED UI with UNKNOWN logic. They have their own Phase 05 module and their own open questions (Q-29, Q-78), so they don't fit the listing or storefront epics.
- **E16 Data Administration (added):** no back-office is designed (C-16), but data has to exist for anything to work. It holds the synthetic seed data (Ready) and production data entry (Blocked on Q-50).

**Order of work:** E14 (infrastructure) carries the E14 number from the brief but is **scheduled first**, because every other epic depends on it ([06-sprint-planning.md](06-sprint-planning.md)).

**Module mapping:** each epic maps to Phase 05 modules ([backend-architecture.md §2](../architecture/backend-architecture.md)). Checkout (M21) has no epic.


| Epic | Name | Goal | Phase 05 modules | Stories | MVP stories | Requirements covered |
|---|---|---|---|---|---|---|
| E01 | Authentication & Identity | Members register, log in and out and recover access through a same-origin Sanctum SPA session with CSRF protection, and catalogue routes follow one access setting. | Identity, Storefront (catalog.access) | US-001, US-002, US-003, US-004, US-005 | 4 of 5 | FR-ACC-08, NFR-SEC-01, NFR-SEC-02 |
| E02 | User Account | The account area: navigation, overview, personal details, newsletter, saved sizes, and the orders and returns entry pages (empty states only). | Account | US-006, US-007, US-008, US-009, US-010, US-011 | 0 of 6 | FR-ACC-01, FR-ACC-02, FR-ACC-03, FR-ACC-04, FR-ACC-05, FR-ACC-06, FR-ACC-07 |
| E03 | Catalog Management | The catalogue data model: brands, category tree, size systems and values, products, images, variants and attributes. | Catalog | US-012 | 1 of 1 | FR-CAT-04, FR-CAT-06, FR-PDP-01, FR-PDP-08, FR-BRD-01 |
| E04 | Categories & Brands | Category and brand listing contexts with their headers and breadcrumbs. | Catalog, Listing | US-013, US-014 | 2 of 2 | FR-CAT-01, FR-CAT-04, FR-BRD-01, FR-BRD-02 |
| E05 | Campaign Management | Landings, campaigns and campaign articles, the derived lifecycle, headlines, countdowns and upcoming sales. | Sales | US-015, US-016, US-017, US-018, US-019, US-020, US-021, US-022 | 3 of 8 | FR-EVT-01, FR-EVT-02, FR-EVT-03, FR-EVT-04, FR-EVT-05, FR-CMP-01, FR-CMP-02, FR-CMP-03, FR-CMP-04, FR-CMP-05, FR-CMP-06, FR-CMP-07, FR-CMP-08, FR-CMP-09, FR-CMP-10, FR-UPC-01, FR-UPC-02, FR-UPC-03, FR-UPC-04 |
| E06 | Product Listing | The one shared listing: tiles, sort, filters and facets, pagination and tile states. | Listing | US-023, US-024, US-025, US-026, US-027, US-028, US-029 | 4 of 7 | FR-CMP-06, FR-CAT-01, FR-CAT-02, FR-CAT-03, FR-CAT-04, FR-CAT-05, FR-CAT-06, FR-CAT-07, FR-CAT-08, FR-CAT-09, FR-STK-01, FR-STK-03, FR-ANL-01 |
| E07 | Product Details | The product detail page: content, prices, sizes, stock hint and size validation. | Sales, Inventory | US-030, US-031, US-032, US-033 | 3 of 4 | FR-PDP-01, FR-PDP-02, FR-PDP-04, FR-PDP-05, FR-PDP-06, FR-PDP-07, FR-PDP-08, FR-PRC-01, NFR-I18N-02 |
| E08 | Search | Brand suggestions while typing, and search results across campaigns. | Listing, Catalog | US-034, US-035 | 2 of 2 | FR-SRC-01, FR-SRC-02 |
| E09 | Inventory Management | Stock per campaign article and size, with atomic reserve and release. | Inventory | US-036 | 1 of 1 | FR-STK-01, FR-STK-02, NFR-CON-01 |
| E10 | Cart & Reservations | Reserve, view and remove items, reservation expiry, and the hand-off button at the checkout boundary. | Cart | US-037, US-038, US-039, US-040, US-041, US-042 | 4 of 6 | FR-NAV-05, FR-PDP-03, FR-PRC-02, FR-CRT-01, FR-CRT-02, FR-CRT-03, FR-CRT-04, FR-CRT-05, FR-CRT-06, FR-CRT-07, FR-CRT-08, NFR-SEC-05 |
| E11 | Storefront UI | App shell, header, navigation, time and money formatting, timezone and responsive structure. | SPA (app, shared, features) | US-043, US-044, US-045, US-046, US-047, US-048 | 6 of 6 | FR-NAV-01, FR-NAV-02, FR-NAV-03, FR-NAV-04, NFR-I18N-03, NFR-TIME-01, NFR-RESP-01 |
| E12 | Content & Help Pages | Help centre, terms and legal pages, footer and footnotes, cookie consent. | Content | US-049, US-050, US-051, US-052 | 4 of 4 | FR-DLV-01, FR-DLV-02, FR-CNT-01, FR-CNT-02, FR-CNT-03, FR-CNT-04, FR-CNT-05, NFR-PRV-01 |
| E13 | Quality Assurance | Cross-cutting verification: concurrency and time-travel tests, accessibility, French copy, performance. | All modules | US-053, US-054, US-055, US-056 | 4 of 4 | FR-CMP-05, FR-CRT-02, NFR-I18N-01, NFR-I18N-02, NFR-A11Y-01, NFR-CON-01, NFR-PERF-01 |
| E14 | Infrastructure & Deployment | Docker environment, CI, API foundation, transport security and the MySQL behaviour spike. Scheduled first. | Platform, Storefront (settings) | US-057, US-058, US-059, US-060, US-061 | 5 of 5 | NFR-TECH-01, NFR-SEC-03, NFR-SEC-04, NFR-CON-01, NFR-CACHE-01, NFR-OBS-01 |
| E15 | Recommendations | Added epic. Recommendation rails are CONFIRMED UI with UNKNOWN logic and have their own module (Recommendations) and open questions. | Recommendations | US-062, US-063 | 0 of 2 | FR-REC-01, FR-REC-02 |
| E16 | Data Administration | Added epic. No back-office is designed (C-16), yet data must exist: synthetic seed data now, production data entry once Q-50 is answered. | All modules (seeders) | US-064, US-065 | 2 of 2 | FR-EVT-01, FR-CMP-05, FR-CMP-09, FR-CAT-01 |
