# Sprint planning — Phase 06

**Provisional sequence, not a commitment.**
- **Capacity and length:** team size, skills, availability and sprint length are unknown (Q-81), so there are no story points, no velocity and no dates. Each sprint is a dependency-ordered slice of the backlog.
- **Re-planning:** sprint duration and capacity must be validated in Sprint 0 planning. After two sprints of measured throughput, sprints can be split or merged, as long as the dependency order is kept. The generator checks that order.

**Shape:**
- **Sprint 0:** foundations and decisions.
- **Sprints 1–5:** the MVP release candidate.
- **Sprints 6–7:** Post-MVP work, and the HYPOTHESIS stories the PO has validated.

**HYPOTHESIS and blocked stories:** a story with status Blocked or Needs Clarification enters a sprint only if its decision gate is met at sprint planning. Otherwise it moves to the next sprint, and the stories that depend on it move with it.

**Decision workshop (Sprint 0):** the PO, the architect and the tech lead answer the gate questions listed under S0, before Sprint 1 planning. The most important is Q-70: it gates the Catalog, Sales and Inventory migrations, which are the critical path.



## S0 — Foundations and decisions

- **Sprint goal:** A running local stack, CI on MySQL 8, the API foundation and security headers; the MySQL spike done; the Sprint 0 decision workshop held.
- **Decision gate:** Before Sprint 1 planning: Q-70 (product granularity), Q-71 (catalogue access), Q-40 (login methods), Q-80 (MVP release purpose), Q-81 (capacity), Q-82 (CI platform), Q-84 (root slug fix)
- **User stories:** US-046 French price, date and countdown formatting (Must, Ready); US-057 Local Docker environment (Must, Ready); US-058 Continuous integration (Must, Needs Clarification); US-059 API foundation (Must, Ready); US-060 Transport security and caching headers (Must, Ready); US-061 MySQL behaviour spike (Must, Ready)
- **Backend tasks:** T-046-B1, T-059-B1, T-060-B1
- **Frontend tasks:** T-046-F1
- **Testing tasks:** T-046-FT1, T-057-BT1, T-058-BT1, T-059-BT1, T-060-BT1, T-061-BT1
- **Infrastructure tasks:** T-057-I1, T-057-I2, T-058-I1, T-058-I2, T-059-I1, T-060-I1, T-061-I1
- **Dependencies on earlier sprints:** none
- **Expected deliverables:** Docker stack on localhost:8080; CI pipeline; problem+json renderer, request IDs, server time, settings with fail-closed check; Nginx headers; spike report with any schema errata; shared formatting utilities
- **Risks:** R-04 (Concurrency tests give false confidence if run on SQLite or a single connection), R-10 (Unverified MySQL behaviours: CHECK constraints combined with FK referential actions, unsigned subtraction in generated columns (`quantity_available`, `discount_percent`), and composite FKs), R-15 (Policy settings unset: production fails closed (503 `settings_incomplete`) while any of the 11 PO-decided values is missing (`listing_page_size` is CONFIRMED)), R-24 (Team capacity and sprint length unknown (Q-81): the sprint plan is a sequence, not a commitment)

## S1 — Identity and catalogue core

- **Sprint goal:** Members can register, log in, log out and reset a password; the app shell exists; the catalogue tables are migrated.
- **Decision gate:** US-012 can't start until Q-70 and Q-84 are answered
- **User stories:** US-001 Register as an adult member (Must, Needs Clarification); US-002 Log in and log out with a protected session (Must, Needs Clarification); US-003 Reset a forgotten password (Must, Needs Clarification); US-005 Apply one catalogue access mode (Must, Needs Clarification); US-012 Catalogue data model (Must, Blocked); US-043 App shell and header (Must, Needs Clarification)
- **Backend tasks:** T-001-B1, T-001-B2, T-002-B1, T-002-B2, T-003-B1, T-005-B1, T-012-B1, T-012-B2, T-012-B3
- **Frontend tasks:** T-001-F1, T-001-F2, T-002-F1, T-002-F2, T-003-F1, T-005-F1, T-043-F1
- **Testing tasks:** T-001-BT1, T-001-FT1, T-002-BT1, T-002-FT1, T-003-BT1, T-003-FT1, T-005-BT1, T-005-FT1, T-012-BT1, T-043-FT1
- **Infrastructure tasks:** T-002-I1, T-003-I1, T-005-I1, T-043-I1
- **Dependencies on earlier sprints:** US-046 (S0), US-059 (S0), US-060 (S0), US-061 (S0)
- **Expected deliverables:** Auth pages and endpoints; catalogue access mode; app shell and header; Catalog migrations, models, factories with the root-slug fix
- **Risks:** R-07 (Phase 05 erratum: `categories` has UQ (parent_id, slug) with a nullable `parent_id`), R-08 (Product granularity (Q-70) unresolved: model vs model + colour changes `products`, `campaign_articles` and `inventory_items`; late answers mean migration rework across three modules), R-19 (Login methods undecided (Q-40): adding social login later changes registration, password reset and account linking), R-20 (Catalogue access mode undecided (Q-71): route guards, caching rules and SEO differ between `members` and `public`)

## S2 — Sales, landings and seed data

- **Sprint goal:** Landing pages show live campaigns with headlines and countdowns, driven by a derived lifecycle and synthetic seed data.
- **Decision gate:** US-015 needs Q-70; Q-23, Q-24 and Q-77 before US-017 acceptance
- **User stories:** US-015 Campaigns and landings with a derived lifecycle (Must, Blocked); US-016 Event landing pages (Must, Ready); US-017 Campaign tile with headline and countdown (Must, Needs Clarification); US-044 Main navigation by vertical (Must, Ready); US-064 Synthetic seed data (Must, Ready); US-051 Footer and footnotes (Should, Ready)
- **Backend tasks:** T-015-B1, T-015-B2, T-015-B3, T-016-B1, T-017-B1, T-044-B1, T-064-B1
- **Frontend tasks:** T-016-F1, T-017-F1, T-044-F1, T-051-F1
- **Testing tasks:** T-015-BT1, T-016-BT1, T-016-FT1, T-017-BT1, T-017-FT1, T-044-BT1, T-044-FT1, T-064-BT1, T-051-FT1
- **Infrastructure tasks:** T-015-I1, T-064-I1
- **Dependencies on earlier sprints:** US-012 (S1), US-043 (S1), US-046 (S0), US-061 (S0)
- **Expected deliverables:** Sales migrations and live predicate; boundary events; seeders for every campaign state; landing pages; campaign tiles; main navigation; footer and footnotes
- **Risks:** R-08 (Product granularity (Q-70) unresolved: model vs model + colour changes `products`, `campaign_articles` and `inventory_items`; late answers mean migration rework across three modules), R-09 (Campaign/product/variant inconsistency: an inventory item whose variant and campaign article belong to different products, or a variant from another product in an add request), R-12 (Seed data or fixtures copy reference-platform content (brand names, images, texts, IDs), breaking the independence constraint), R-17 (Stale landing caches keep showing ended campaigns or miss started ones)

## S3 — Listing and inventory core

- **Sprint goal:** Members can browse campaign and category listings with sort and filters; stock is reserved and released atomically.
- **Decision gate:** Q-11, Q-14, Q-43 and Q-45 before acceptance of US-023, US-025, US-024 and US-027
- **User stories:** US-013 Browse a category listing (Must, Ready); US-023 Campaign listing with tiles and pages (Must, Needs Clarification); US-024 Sort a listing (Must, Needs Clarification); US-025 Filter a listing (Must, Needs Clarification); US-027 "Réservé" state on tiles (Must, Needs Clarification); US-036 Atomic stock reservation and release (Must, Needs Clarification); US-045 Mega-menu by category (Should, Ready)
- **Backend tasks:** T-013-B1, T-023-B1, T-024-B1, T-025-B1, T-027-B1, T-036-B1, T-045-B1
- **Frontend tasks:** T-013-F1, T-023-F1, T-024-F1, T-025-F1, T-027-F1, T-045-F1
- **Testing tasks:** T-013-BT1, T-013-FT1, T-023-BT1, T-023-FT1, T-024-BT1, T-024-FT1, T-025-BT1, T-025-FT1, T-027-BT1, T-027-FT1, T-036-BT1, T-045-FT1
- **Infrastructure tasks:** T-036-I1
- **Dependencies on earlier sprints:** US-012 (S1), US-015 (S2), US-017 (S2), US-044 (S2), US-064 (S2)
- **Expected deliverables:** Shared listing and facets; category listing; mega-menu; inventory module with ReserveUnit and ReleaseUnit; tile availability states
- **Risks:** R-03 (Overselling under concurrent reservations if the reserve isn't a single conditional atomic update, or if a read-then-write slips in), R-09 (Campaign/product/variant inconsistency: an inventory item whose variant and campaign article belong to different products, or a variant from another product in an add request), R-23 (Performance targets unknown (Q-42): FULLTEXT search and facet counts are untested at real volume)

## S4 — Product page and cart

- **Sprint goal:** The core journey J1 works end to end: product page, size choice, reserve, cart drawer, remove, with concurrency tests passing.
- **Decision gate:** Q-25, Q-32, Q-46, Q-51 before US-037 acceptance; Q-37 (expiry) before the MVP release decision
- **User stories:** US-030 Product detail page (Must, Needs Clarification); US-031 Low-stock indicator (Must, Needs Clarification); US-032 Size required before adding (Must, Ready); US-037 Reserve an article in my cart (Must, Needs Clarification); US-038 View my cart (Must, Ready); US-039 Remove an item after confirming (Must, Ready); US-053 Concurrency and time-travel test suite (Must, Ready); US-014 Browse a brand page (Should, Ready); US-040 Release expired reservations (Could, Needs Clarification)
- **Backend tasks:** T-030-B1, T-031-B1, T-032-B1, T-037-B1, T-038-B1, T-039-B1, T-014-B1, T-040-B1
- **Frontend tasks:** T-030-F1, T-031-F1, T-032-F1, T-037-F1, T-038-F1, T-039-F1, T-014-F1, T-040-F1
- **Testing tasks:** T-030-BT1, T-030-FT1, T-031-BT1, T-031-FT1, T-032-BT1, T-032-FT1, T-037-BT1, T-037-FT1, T-038-BT1, T-038-FT1, T-039-BT1, T-039-FT1, T-053-BT1, T-053-BT2, T-014-BT1, T-014-FT1, T-040-BT1, T-040-FT1
- **Infrastructure tasks:** T-053-I1, T-040-I1
- **Dependencies on earlier sprints:** US-002 (S1), US-023 (S3), US-036 (S3), US-058 (S0)
- **Expected deliverables:** PDP; low-stock label; size validation; add, view and remove cart items; expiry sweep behind its switch; brand page; concurrency and time-travel suite
- **Risks:** R-01 (Reservation expiry lifecycle undecided), R-02 (Stock leak when a member re-adds the same size after expiry but before the sweep), R-03 (Overselling under concurrent reservations if the reserve isn't a single conditional atomic update, or if a read-then-write slips in), R-05 (The expiry sweep races with remove, re-add or a future checkout on the same row, releasing a unit twice), R-06 (The scheduler isn't running, so expired reservations are never released and boundary events are missed)

## S5 — Search, legal and hardening: MVP release candidate

- **Sprint goal:** Search works, legal and consent are in place, and the J1 journey is verified for accessibility and French copy.
- **Decision gate:** MVP release needs every release gate in 10-phase-06-summary.md §4
- **User stories:** US-034 Brand suggestions while typing (Must, Needs Clarification); US-035 Search results across campaigns (Must, Needs Clarification); US-041 Hand-off button from cart to checkout (Must, Blocked); US-047 Show times in the decided timezone (Must, Blocked); US-048 Responsive layouts (Must, Blocked); US-050 Terms, legal pages and one return period (Must, Needs Clarification); US-052 Cookie consent and privacy (Must, Needs Clarification); US-054 Keyboard and screen-reader access to the core journey (Must, Ready); US-055 French-only, correctly pluralised copy (Must, Ready); US-056 Performance targets and load test (Must, Blocked); US-049 Help centre (Should, Needs Clarification)
- **Backend tasks:** T-034-B1, T-035-B1, T-047-B1, T-050-B1, T-055-B1, T-049-B1
- **Frontend tasks:** T-034-F1, T-035-F1, T-041-F1, T-047-F1, T-048-F1, T-050-F1, T-052-F1, T-054-F1, T-055-F1, T-049-F1
- **Testing tasks:** T-034-BT1, T-034-FT1, T-035-BT1, T-035-FT1, T-041-FT1, T-047-FT1, T-048-FT1, T-050-BT1, T-050-FT1, T-052-FT1, T-054-FT1, T-055-FT1, T-056-BT1, T-049-BT1, T-049-FT1
- **Infrastructure tasks:** T-035-I1, T-052-I1, T-054-I1, T-056-I1
- **Dependencies on earlier sprints:** US-023 (S3), US-038 (S4), US-039 (S4), US-043 (S1), US-046 (S0), US-051 (S2)
- **Expected deliverables:** Search suggestions and results; checkout hand-off button per Q-80; help, terms and legal pages; consent; a11y and copy verification; timezone, responsive and performance stories if their decisions arrived
- **Risks:** R-11 (Data administration gap: no back-office, import format or admin UI is designed (C-16, Q-50), so real catalogue and campaign data can't be loaded), R-13 (Checkout boundary: the MVP can reserve but can't sell), R-14 (Scope creep into checkout: order tables, payment endpoints or fee charging added "temporarily"), R-16 (Display timezone undecided (NFR-TIME-01): start and end times shown an hour off for some members), R-22 (Legal content missing (Q-48, Q-31): terms, entity, mediator and one return period are needed before any public release), R-25 (Accessibility regressions in custom dialogs (filters, cart drawer, remove confirmation, mega-menu)), R-26 (Consent solution undecided (Q-86): third-party scripts loaded before consent would breach NFR-PRV-01)

## S6 — Post-MVP: account and discovery

- **Sprint goal:** The account area, upcoming sales and filter refinements are added.
- **Decision gate:** Q-41, Q-83, Q-35, Q-74 before acceptance
- **User stories:** US-004 Verify my e-mail address (Should, Needs Clarification); US-006 Account navigation and overview (Should, Ready); US-007 Edit my personal details (Should, Needs Clarification); US-008 Choose which sales e-mails I receive (Should, Ready); US-009 Unsubscribe with a reason (Should, Needs Clarification); US-010 Save my sizes (Should, Ready); US-011 Orders and returns entry pages (Should, Ready); US-018 Upcoming sales (Should, Needs Clarification); US-026 Filter dialog refinements (Should, Ready)
- **Backend tasks:** T-004-B1, T-006-B1, T-007-B1, T-008-B1, T-009-B1, T-010-B1, T-018-B1
- **Frontend tasks:** T-004-F1, T-006-F1, T-007-F1, T-008-F1, T-009-F1, T-010-F1, T-011-F1, T-018-F1, T-026-F1
- **Testing tasks:** T-004-BT1, T-004-FT1, T-006-BT1, T-006-FT1, T-007-BT1, T-007-FT1, T-008-BT1, T-008-FT1, T-009-BT1, T-009-FT1, T-010-BT1, T-010-FT1, T-011-FT1, T-018-BT1, T-018-FT1, T-026-FT1
- **Infrastructure tasks:** T-004-I1, T-010-I1
- **Dependencies on earlier sprints:** US-001 (S1), US-002 (S1), US-012 (S1), US-016 (S2), US-017 (S2), US-025 (S3), US-043 (S1)
- **Expected deliverables:** Account overview, profile, newsletter, unsubscribe, saved sizes, orders and returns entry pages; e-mail verification; upcoming section; filter dialog refinements
- **Risks:** R-21 (Recommendation logic UNKNOWN (Q-29): personal rails stay empty and hidden, so AC-FR-REC-01-1 can't pass), R-24 (Team capacity and sprint length unknown (Q-81): the sprint plan is a sequence, not a commitment)

## S7 — Post-MVP: recommendations and validated hypotheses

- **Sprint goal:** Recommendation rails, attribution, and whichever HYPOTHESIS behaviours the PO has validated.
- **Decision gate:** Each HYPOTHESIS story enters the sprint only after its PO validation
- **User stories:** US-062 Recommendation rails on the default landing (Should, Needs Clarification); US-063 Popular rail (Should, Needs Clarification); US-019 Merchandised order in a landing grid (Could, Needs Clarification); US-020 Curated landing template (Could, Needs Clarification); US-021 Refuse reservations outside a campaign's live window (Could, Needs Clarification); US-028 "Dernières heures" badge (Could, Needs Clarification); US-029 Link-source attribution (Could, Ready); US-033 Button after adding an article (Could, Needs Clarification); US-042 Throttle bulk add-to-cart (Could, Needs Clarification)
- **Backend tasks:** T-062-B1, T-063-B1, T-019-B1, T-020-B1, T-021-B1, T-028-B1, T-033-B1, T-042-B1
- **Frontend tasks:** T-062-F1, T-063-F1, T-020-F1, T-021-F1, T-028-F1, T-029-F1, T-033-F1, T-042-F1
- **Testing tasks:** T-062-BT1, T-062-FT1, T-063-BT1, T-019-BT1, T-020-FT1, T-021-BT1, T-021-FT1, T-028-FT1, T-029-FT1, T-033-FT1, T-042-BT1
- **Infrastructure tasks:** T-063-I1
- **Dependencies on earlier sprints:** US-016 (S2), US-017 (S2), US-018 (S6), US-023 (S3), US-037 (S4), US-051 (S2)
- **Expected deliverables:** Rails and popular strategy; link attribution; grid order, curated template, live-window guard, last-hours badge, after-add button, bulk-add throttle (each only if validated)
- **Risks:** R-21 (Recommendation logic UNKNOWN (Q-29): personal rails stay empty and hidden, so AC-FR-REC-01-1 can't pass), R-27 (Bulk reservation abuse at launch hoards limited stock (NFR-SEC-05 is a HYPOTHESIS)), R-29 (HYPOTHESIS behaviours shipped as if confirmed)

## Unscheduled backlog

- **US-022 Ask to be notified when a sale starts** (Blocked: Q-47, Q-01). HYPOTHESIS and not designed in Phase 05 (api-contracts §11). No tasks until Q-47 gives the channel, stored data, confirmation and unsubscribe rules.
- **US-065 Production data entry** (Blocked: Q-50, Q-80). Release gate for any launch with real products. No back-office, import format or admin UI is designed (C-16). If Q-80 makes the MVP an internal demo, seed data (US-064) is enough.
