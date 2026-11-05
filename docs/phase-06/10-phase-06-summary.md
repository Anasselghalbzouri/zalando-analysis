# Phase 06 summary — Project planning and backlog

## 1. What was produced
| File | Content |
|---|---|
| [01-mvp-scope.md](01-mvp-scope.md) | Documents analysed, findings, inconsistencies I-01–I-12, the MVP definition, and the classification of all 95 requirements |
| [02-epics.md](02-epics.md) | 16 epics (the brief's 14, plus Recommendations and Data Administration), mapped to modules and requirements |
| [03-product-backlog.md](03-product-backlog.md) | The prioritised backlog of 65 stories, with status definitions |
| [04-user-stories.md](04-user-stories.md) | 65 stories with Given/When/Then criteria, API and table dependencies |
| [05-technical-tasks.md](05-technical-tasks.md) | 228 tasks split into Backend, Frontend and Infrastructure |
| [06-sprint-planning.md](06-sprint-planning.md) | Sprint 0 to Sprint 7 as a provisional sequence, with decision gates |
| [07-definition-of-done.md](07-definition-of-done.md) | Project-specific Definition of Done |
| [08-risk-register.md](08-risk-register.md) | 29 risks with owners |
| [09-open-questions.md](09-open-questions.md) | 58 decisions, with affected stories |

**Source and tooling:**
- **Source:** `tools/phase06/backlog.txt` is the single source for files 01–06 and 08–10.
- **Generator:** `tools/phase06/gen.py` writes them and runs the checks in §2. Re-run it after any edit: `python -I tools/phase06/gen.py .` from the project root. It's documentation tooling, not application code.
- **Register:** `docs/requirements/open-questions.md` gained section H (Q-80–Q-86).
- **Phase 05 untouched:** no Phase 05 file was changed. The schema erratum is recorded, not applied (I-01).

## 2. Final validation (generated)
| # | Check | Result |
|---|---|---|
| 1 | Every story cites at least one requirement, and every cited requirement exists in SRS.md | **Pass** |
| 2 | Every requirement that is not OUT OF SCOPE is covered by at least one story | **Pass** |
| 3 | No story cites an OUT OF SCOPE requirement (FR-OOS-01 checkout/payment M21, FR-OOS-02) | **Pass** |
| 4 | Every dependency points to an existing story | **Pass** |
| 5 | The dependency graph has no cycles | **Pass** |
| 6 | Each story's dependencies are scheduled in the same or an earlier sprint | **Pass** |
| 7 | No UNKNOWN or HYPOTHESIS requirement is treated as confirmed (status and class rules) | **Pass** |
| 8 | Story priority equals the highest Phase 04 priority of its requirements, unless a reason is stated | **Pass** |
| 9 | MVP classes agree with priority and sprint (Must = MVP in S0–S5 unless Blocked; pulled-in items carry a reason) | **Pass** |
| 10 | Every API reference is one of the endpoints in api-contracts.md | **Pass** |
| 11 | Every table reference exists in schema.md (domain or framework tables) | **Pass** |
| 12 | No story touches checkout, payment or order endpoints or tables (M21 stays excluded) | **Pass** |
| 13 | Every Q-ID cited exists in open-questions.md or is a new Phase 06 question (Q-80–Q-86) | **Pass** |
| 14 | Every AC-ID cited exists in acceptance-criteria.md | **Pass** |
| 15 | Every BR-, C- and ADR- reference exists | **Pass** |
| 16 | Every risk, sprint and story reference in the sprint plan and risk register exists | **Pass** |
| 17 | Every story has acceptance criteria, and every story that isn't Blocked has at least one task | **Pass** |
| 18 | Every story names its Phase 04 story or "new", and every Phase 04 story US-01–US-27 is carried forward | **Pass** |
| 19 | Every story is in a known epic and sprint | **Pass** |
| 20 | Every endpoint in api-contracts.md is used by at least one story | **Pass** |
| 21 | Every story reported as startable has no Blocked story among its transitive dependencies | **Pass** |

**Limit:** the checks prove that the documents are consistent: IDs exist, the dependency order holds, and the labels are preserved. They don't prove that the plan is right. That's for the decision workshop and sprint reviews.

## 3. Backlog readiness
| Status | Must | Should | Could | Total |
|---|---|---|---|---|
| Ready | 15 | 8 | 1 | 24 |
| Needs Clarification | 19 | 7 | 7 | 33 |
| Blocked | 7 | 0 | 1 | 8 |

45 stories are in the MVP scope (41 MVP, 4 pulled in): 18 Ready, 20 Needs Clarification, 7 Blocked. Of all 65 stories, **21 are startable** (not Blocked, with no Blocked ancestor), and 44 are Blocked or wait on a Blocked story. **28 of the 45 MVP stories wait on Q-70**, directly or through US-012 and US-015.

**Ready and startable now (Sprint 0):** US-046 French price, date and countdown formatting; US-057 Local Docker environment; US-059 API foundation; US-060 Transport security and caching headers; US-061 MySQL behaviour spike.

US-058 CI also belongs to Sprint 0, but it needs Q-82 (Git host and CI platform).

## 4. MVP readiness and release gates
**Verdict:** the MVP is **buildable, but not releasable**. Sprints 0–5 can start. The MVP can be released only when every gate below is closed.

| Gate | What's needed | Blocks |
|---|---|---|
| G-01 | **Q-70** product granularity, answered **before Sprint 1** | US-012, US-015, US-036 (critical path) |
| G-02 | **Q-84** root-slug fix approved by the architect, plus US-061 spike errata | US-012, US-015 |
| G-03 | **Q-37** reservation expiry: enable `release_expired_reservations` or not (MVP-critical, R-01) | US-040, US-037 |
| G-04 | **Q-80** MVP purpose and the "Commander" target. **Q-04** if orders are expected | US-041, US-065 |
| G-05 | **The policy values**, all fail-closed in production (system-overview §7). 11 need a PO decision; `listing_page_size` is already CONFIRMED: `reservation_minutes` (Q-25), `reservation_timer_mode` (Q-32), `max_quantity_per_article` (Q-46), `low_stock_threshold` (Q-26), `return_period_days` (Q-31), `packaging_fee_cents` (Q-30), `display_timezone` (Q-10), `upcoming_relative_threshold_minutes` (Q-35), `upcoming_horizon_hours` (Q-74), `last_hours_badge_threshold_hours` (Q-36), `listing_page_size` (60, CONFIRMED), `catalog_access` (Q-71) | US-059 and every story that reads them |
| G-06 | **Q-40** login methods, **Q-41** form fields, **Q-63** session lifetime | US-001, US-002, US-003 |
| G-07 | **Legal and content:** Q-48 legal entity, Q-85 content supply, Q-86 consent solution | US-050, US-043, US-049, US-052 |
| G-08 | **UNKNOWN NFRs:** Q-15 responsive, Q-42 performance targets | US-048, US-056 |
| G-09 | **Q-50** production data entry, needed only for a launch with real products | US-065 |

## 5. Critical unresolved decisions (in order)
1. **Q-70:** what a product is, model or model + colour. It decides three modules' tables.
2. **Q-37 / FR-CRT-08:** whether reservations expire. Without expiry, reserved stock is lost to everyone else.
3. **Q-80 (with Q-04):** what the MVP is for, given it can't take orders.
4. **Q-71:** members-only or public catalogue.
5. **Q-40:** login methods.
6. **Q-25, Q-32, Q-46:** reservation duration, timer mode and quantity limit.
7. **Q-10:** display timezone.
8. **Q-50:** how real data gets in.

## 6. Blockers to implementation
- **Must start now:** none. Sprint 0 is unblocked, apart from Q-82 for CI.
- **Blocks Sprint 1–2 schema work:** Q-70 and Q-84 (G-01, G-02).
- **Blocks acceptance, not building:** the Needs Clarification stories. They're built against settings and switches.
- **Blocks the release:** G-03 to G-09.

## 7. Recommended next phase
**Phase 07: Sprint 0 and the implementation foundation.**
1. Hold the decision workshop on the S0 gate questions, Q-70 first.
2. Validate team capacity and sprint length (Q-81), then turn the sequence into dated sprints.
3. Build US-057, US-059, US-060 and US-061, plus US-046. Add US-058 once Q-82 is answered.
4. Record the spike results and the root-slug fix as an approved Phase 05 erratum. Then freeze the Catalog and Sales migrations.

This is the first phase in which application code, migrations and Docker files are written.

## 8. Not checked
- Story sizes and complexity: no estimates were made.
- Whether the PRD §8 priority rule matches the business's real priorities: no business priorities were given.
- Mermaid diagrams and MySQL behaviours from Phase 05: they remain unverified until US-061.
