# Product backlog — Phase 06

This is the prioritised backlog: one row per story, ordered by sprint, then MoSCoW, then ID. The full stories are in [04-user-stories.md](04-user-stories.md), and the tasks are in [05-technical-tasks.md](05-technical-tasks.md).

## 1. How priorities were set
- **MoSCoW is inherited from Phase 04.** A story's priority is the highest Phase 04 priority among its requirements (PRD §8 rule, PO to confirm). One exception is stated: US-004 is lowered to Should with a reason. The generator checks this rule.
- **The order inside the MoSCoW bands comes from technical dependency and risk:**
  - infrastructure and the MySQL spike first
  - then the data model for Catalog, Sales and Inventory, the critical path gated by Q-70
  - then the core journey J1, then search, legal and hardening
- **Won't (MVP):** every Post-MVP and Blocked story is outside the MVP release. None of them is labelled Won't, because Phase 04 didn't rule any requirement out except FR-OOS-01 and FR-OOS-02, which have no stories.
- **Business value:** the PRD §8 rule ranks the core journey (J1) and brand search (J4) highest. No other business priorities were given, so none are invented.
- **Complexity and risk:** no story points are given (see [06-sprint-planning.md](06-sprint-planning.md)). The highest-risk stories (US-036, US-037, US-040 and US-053) sit in Sprints 3–4, after the MySQL spike, so concurrency problems surface early.

## 2. Status definitions
| Status | Meaning |
|---|---|
| **Done** | Built and accepted by the PO against the [Definition of Done](07-definition-of-done.md) (Phase 08). It counts as Ready for every readiness rule |
| **Ready** | Its own scope needs no decision: every requirement it cites is CONFIRMED, and it has no open question. It **starts once its dependencies are done**. If a dependency is Blocked, the story isn't startable yet; the "Startable" and "Waits on" columns show this |
| **Needs Clarification** | Can be built against the Phase 05 seam (setting, switch or placeholder), but at least one open question must be answered before acceptance. Every HYPOTHESIS story is here or Blocked |
| **Blocked** | Can't be built or can't be accepted at all until a decision is made: an UNKNOWN requirement, an undesigned behaviour, or a schema that can't be frozen |

## 3. Summary
| Measure | Count |
|---|---|
| Stories | 65 |
| MoSCoW Must | 41 |
| MoSCoW Should | 15 |
| MoSCoW Could | 9 |
| Status Done | 1 |
| Status Ready | 23 |
| Status Needs Clarification | 33 |
| Status Blocked | 8 |
| Class MVP | 41 |
| Class MVP (pulled in) | 4 |
| Class Post-MVP | 12 |
| Class Blocked | 8 |


## 4. Backlog

| Rank | ID | Title | Epic | MoSCoW | Status | Startable | MVP class | Sprint | Dependencies | Waits on (Blocked ancestors) | Source requirements | Open questions |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | US-046 | French price, date and countdown formatting | E11 | Must | Ready | yes | MVP | S0 | US-059 | — | NFR-I18N-03 | — |
| 2 | US-057 | Local Docker environment | E14 | Must | Ready | yes | MVP | S0 | — | — | NFR-TECH-01 | — |
| 3 | US-058 | Continuous integration | E14 | Must | Needs Clarification | yes | MVP | S0 | US-057 | — | NFR-TECH-01 | Q-82 |
| 4 | US-059 | API foundation | E14 | Must | Done | yes | MVP | S0 | US-057 | — | NFR-OBS-01, NFR-TECH-01 | — |
| 5 | US-060 | Transport security and caching headers | E14 | Must | Ready | yes | MVP | S0 | US-057 | — | NFR-SEC-03, NFR-SEC-04, NFR-CACHE-01 | — |
| 6 | US-061 | MySQL behaviour spike | E14 | Must | Ready | yes | MVP | S0 | US-057 | — | NFR-CON-01, NFR-TECH-01 | — |
| 7 | US-001 | Register as an adult member | E01 | Must | Needs Clarification | yes | MVP | S1 | US-059, US-060 | — | FR-ACC-08 | Q-40, Q-41 |
| 8 | US-002 | Log in and log out with a protected session | E01 | Must | Needs Clarification | yes | MVP | S1 | US-001 | — | FR-ACC-08, NFR-SEC-01, NFR-SEC-02 | Q-40, Q-63 |
| 9 | US-003 | Reset a forgotten password | E01 | Must | Needs Clarification | yes | MVP | S1 | US-002 | — | FR-ACC-08 | Q-40 |
| 10 | US-005 | Apply one catalogue access mode | E01 | Must | Needs Clarification | yes | MVP | S1 | US-002, US-059 | — | FR-ACC-08 | Q-71, Q-28, Q-03 |
| 11 | US-012 | Catalogue data model | E03 | Must | Blocked | no | MVP | S1 | US-061 | — | FR-PDP-01, FR-PDP-08, FR-CAT-04, FR-CAT-06, FR-BRD-01 | Q-70, Q-84 |
| 12 | US-043 | App shell and header | E11 | Must | Needs Clarification | yes | MVP | S1 | US-002, US-046 | — | FR-NAV-01, FR-NAV-02 | Q-75, Q-85 |
| 13 | US-015 | Campaigns and landings with a derived lifecycle | E05 | Must | Blocked | no | MVP | S2 | US-012, US-061 | US-012 (Q-70, Q-84) | FR-CMP-05, FR-CMP-06, FR-CMP-07, FR-CMP-09, FR-CMP-10, FR-EVT-02 | Q-70, Q-77, Q-20, Q-33 |
| 14 | US-016 | Event landing pages | E05 | Must | Ready | no | MVP | S2 | US-015, US-043, US-064 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-EVT-01, FR-EVT-02, FR-EVT-04 | — |
| 15 | US-017 | Campaign tile with headline and countdown | E05 | Must | Needs Clarification | no | MVP | S2 | US-016, US-046 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-CMP-01, FR-CMP-02, FR-CMP-03, FR-CMP-04 | Q-23, Q-24, Q-77 |
| 16 | US-044 | Main navigation by vertical | E11 | Must | Ready | no | MVP | S2 | US-016 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-NAV-03 | — |
| 17 | US-064 | Synthetic seed data | E16 | Must | Ready | no | MVP | S2 | US-012, US-015 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-EVT-01, FR-CMP-09, FR-CAT-01 | — |
| 18 | US-051 | Footer and footnotes | E12 | Should | Ready | yes | MVP (pulled in) | S2 | US-043 | — | FR-CNT-01, FR-CNT-05 | — |
| 19 | US-013 | Browse a category listing | E04 | Must | Ready | no | MVP | S3 | US-023, US-012 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-CAT-01, FR-CAT-04 | — |
| 20 | US-023 | Campaign listing with tiles and pages | E06 | Must | Needs Clarification | no | MVP | S3 | US-015, US-017, US-064 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-CAT-01, FR-CAT-02, FR-CAT-09, FR-CMP-06 | Q-11, Q-12 |
| 21 | US-024 | Sort a listing | E06 | Must | Needs Clarification | no | MVP | S3 | US-023 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-CAT-03 | Q-43 |
| 22 | US-025 | Filter a listing | E06 | Must | Needs Clarification | no | MVP | S3 | US-023 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-CAT-04 | Q-14 |
| 23 | US-027 | "Réservé" state on tiles | E06 | Must | Needs Clarification | no | MVP | S3 | US-023, US-036 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-STK-01 | Q-45 |
| 24 | US-036 | Atomic stock reservation and release | E09 | Must | Needs Clarification | no | MVP | S3 | US-015 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | NFR-CON-01, FR-STK-01, FR-STK-02 | Q-70 |
| 25 | US-045 | Mega-menu by category | E11 | Should | Ready | no | MVP (pulled in) | S3 | US-044, US-013 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-NAV-04 | — |
| 26 | US-030 | Product detail page | E07 | Must | Needs Clarification | no | MVP | S4 | US-023, US-036 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-PDP-01, FR-PDP-06, FR-PDP-07, FR-PDP-08, FR-PRC-01 | Q-31, Q-49 |
| 27 | US-031 | Low-stock indicator | E07 | Must | Needs Clarification | no | MVP | S4 | US-030 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-PDP-05, NFR-I18N-02 | Q-26 |
| 28 | US-032 | Size required before adding | E07 | Must | Ready | no | MVP | S4 | US-030 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-PDP-02 | — |
| 29 | US-037 | Reserve an article in my cart | E10 | Must | Needs Clarification | no | MVP | S4 | US-032, US-036, US-002 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-PDP-03, FR-CRT-02, FR-NAV-05 | Q-25, Q-32, Q-46, Q-51 |
| 30 | US-038 | View my cart | E10 | Must | Ready | no | MVP | S4 | US-037 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-CRT-01, FR-CRT-03, FR-CRT-05, FR-CRT-06, FR-PRC-02 | — |
| 31 | US-039 | Remove an item after confirming | E10 | Must | Ready | no | MVP | S4 | US-038 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-CRT-04 | — |
| 32 | US-053 | Concurrency and time-travel test suite | E13 | Must | Ready | no | MVP | S4 | US-037, US-039, US-058 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | NFR-CON-01, FR-CMP-05, FR-CRT-02 | — |
| 33 | US-014 | Browse a brand page | E04 | Should | Ready | no | MVP (pulled in) | S4 | US-023 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-BRD-01, FR-BRD-02 | — |
| 34 | US-040 | Release expired reservations | E10 | Could | Needs Clarification | no | Blocked | S4 | US-037 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-CRT-08 | Q-37, Q-32 |
| 35 | US-034 | Brand suggestions while typing | E08 | Must | Needs Clarification | no | MVP | S5 | US-035, US-043 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-SRC-01 | Q-44 |
| 36 | US-035 | Search results across campaigns | E08 | Must | Needs Clarification | no | MVP | S5 | US-023 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-SRC-02 | Q-44 |
| 37 | US-041 | Hand-off button from cart to checkout | E10 | Must | Blocked | no | MVP | S5 | US-038 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-CRT-07 | Q-04, Q-80 |
| 38 | US-047 | Show times in the decided timezone | E11 | Must | Blocked | no | MVP | S5 | US-046 | — | NFR-TIME-01 | Q-10 |
| 39 | US-048 | Responsive layouts | E11 | Must | Blocked | no | MVP | S5 | US-043 | — | NFR-RESP-01 | Q-15 |
| 40 | US-050 | Terms, legal pages and one return period | E12 | Must | Needs Clarification | yes | MVP | S5 | US-049, US-051 | — | FR-CNT-03, FR-DLV-02 | Q-48, Q-31 |
| 41 | US-052 | Cookie consent and privacy | E12 | Must | Needs Clarification | yes | MVP | S5 | US-051 | — | FR-CNT-04, NFR-PRV-01 | Q-86 |
| 42 | US-054 | Keyboard and screen-reader access to the core journey | E13 | Must | Ready | no | MVP | S5 | US-039 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | NFR-A11Y-01 | — |
| 43 | US-055 | French-only, correctly pluralised copy | E13 | Must | Ready | no | MVP | S5 | US-039 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | NFR-I18N-01, NFR-I18N-02 | — |
| 44 | US-056 | Performance targets and load test | E13 | Must | Blocked | no | MVP | S5 | US-039, US-035 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | NFR-PERF-01 | Q-42, Q-72 |
| 45 | US-049 | Help centre | E12 | Should | Needs Clarification | yes | MVP (pulled in) | S5 | US-051 | — | FR-CNT-02, FR-DLV-01 | Q-49, Q-85 |
| 46 | US-004 | Verify my e-mail address | E01 | Should | Needs Clarification | yes | Post-MVP | S6 | US-001 | — | FR-ACC-08 | Q-40 |
| 47 | US-006 | Account navigation and overview | E02 | Should | Ready | yes | Post-MVP | S6 | US-002, US-043 | — | FR-ACC-01, FR-ACC-02 | — |
| 48 | US-007 | Edit my personal details | E02 | Should | Needs Clarification | yes | Post-MVP | S6 | US-006 | — | FR-ACC-01 | Q-41 |
| 49 | US-008 | Choose which sales e-mails I receive | E02 | Should | Ready | yes | Post-MVP | S6 | US-006 | — | FR-ACC-05 | — |
| 50 | US-009 | Unsubscribe with a reason | E02 | Should | Needs Clarification | yes | Post-MVP | S6 | US-008 | — | FR-ACC-06 | Q-83 |
| 51 | US-010 | Save my sizes | E02 | Should | Ready | no | Post-MVP | S6 | US-006, US-012 | US-012 (Q-70, Q-84) | FR-ACC-07 | — |
| 52 | US-011 | Orders and returns entry pages | E02 | Should | Ready | yes | Post-MVP | S6 | US-006 | — | FR-ACC-03, FR-ACC-04 | — |
| 53 | US-018 | Upcoming sales | E05 | Should | Needs Clarification | no | Post-MVP | S6 | US-016, US-017 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-UPC-01, FR-UPC-02, FR-UPC-03 | Q-35, Q-74, Q-27 |
| 54 | US-026 | Filter dialog refinements | E06 | Should | Ready | no | Post-MVP | S6 | US-025 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-CAT-05, FR-CAT-06, FR-CAT-07, FR-CAT-08 | — |
| 55 | US-062 | Recommendation rails on the default landing | E15 | Should | Needs Clarification | no | Post-MVP | S7 | US-016, US-023 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-REC-01 | Q-29 |
| 56 | US-063 | Popular rail | E15 | Should | Needs Clarification | no | Post-MVP | S7 | US-062, US-037 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-REC-02 | Q-78, Q-29 |
| 57 | US-019 | Merchandised order in a landing grid | E05 | Could | Needs Clarification | no | Blocked | S7 | US-016 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-EVT-03 | Q-50 |
| 58 | US-020 | Curated landing template | E05 | Could | Needs Clarification | no | Blocked | S7 | US-016, US-018, US-051 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-EVT-05 | Q-16 |
| 59 | US-021 | Refuse reservations outside a campaign's live window | E05 | Could | Needs Clarification | no | Blocked | S7 | US-037 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-CMP-08 | Q-17, Q-21 |
| 60 | US-028 | "Dernières heures" badge | E06 | Could | Needs Clarification | no | Blocked | S7 | US-023, US-017 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-STK-03 | Q-36 |
| 61 | US-029 | Link-source attribution | E06 | Could | Ready | no | Post-MVP | S7 | US-023 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-ANL-01 | — |
| 62 | US-033 | Button after adding an article | E07 | Could | Needs Clarification | no | Blocked | S7 | US-037 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-PDP-04 | Q-46 |
| 63 | US-042 | Throttle bulk add-to-cart | E10 | Could | Needs Clarification | no | Blocked | S7 | US-037 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | NFR-SEC-05 | Q-42 |
| 64 | US-065 | Production data entry | E16 | Must | Blocked | no | MVP | Backlog (unscheduled) | US-012, US-015 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-CMP-05, FR-CMP-09 | Q-50, Q-80 |
| 65 | US-022 | Ask to be notified when a sale starts | E05 | Could | Blocked | no | Blocked | Backlog (unscheduled) | US-018 | US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) | FR-UPC-04 | Q-47, Q-01 |
