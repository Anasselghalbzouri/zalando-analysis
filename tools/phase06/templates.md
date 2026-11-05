=== 01 ===
# MVP scope — Phase 06

This document defines the minimum viable product from the Phase 04 requirements and the Phase 05 design, and classifies every requirement. The backlog itself is in [03-product-backlog.md](03-product-backlog.md), and the stories are in [04-user-stories.md](04-user-stories.md).

**Generated:** the classification table in §5 is produced by `tools/phase06/gen.py` from `tools/phase06/backlog.txt`. Edit the source, not this file, then re-run `python -I tools/phase06/gen.py .` from the project root.

## 1. Documents analysed
- **Phase 04 requirements:**
  - [PRD.md](../requirements/PRD.md) and [SRS.md](../requirements/SRS.md)
  - [business-rules.md](../requirements/business-rules.md) and [use-cases.md](../requirements/use-cases.md)
  - [user-stories.md](../requirements/user-stories.md) and [acceptance-criteria.md](../requirements/acceptance-criteria.md)
  - [traceability-matrix.md](../requirements/traceability-matrix.md), [dependencies.md](../requirements/dependencies.md) and [open-questions.md](../requirements/open-questions.md)
- **Phase 05 architecture:**
  - [system-overview.md](../architecture/system-overview.md)
  - [erd.md](../architecture/database/erd.md), [schema.md](../architecture/database/schema.md) and [indexing-strategy.md](../architecture/database/indexing-strategy.md)
  - [api-contracts.md](../architecture/api/api-contracts.md) and [error-format.md](../architecture/api/error-format.md)
  - [authentication-design.md](../architecture/authentication-design.md), [backend-architecture.md](../architecture/backend-architecture.md) and [frontend-architecture.md](../architecture/frontend-architecture.md)
  - [docker-architecture.md](../architecture/docker-architecture.md), the [ADRs](../architecture/adr/README.md) and [design-validation.md](../architecture/design-validation.md)

## 2. What the documents establish
| Topic | Finding | Source |
|---|---|---|
| **Confirmed requirements** | {{REQ_CLASSES}}. There are 78 functional and 17 non-functional requirements | SRS, traceability matrix |
| **Proposed decisions** | Everything in Phase 05 is PROPOSED: the modular monolith, Sanctum SPA sessions, campaign articles, inventory per article × size, derived campaign state, RTK Query, MySQL FULLTEXT, database drivers with optional Redis. The 8 HYPOTHESIS behaviours each sit behind one switch or seam | system-overview §1–§2, ADR-001–ADR-010 |
| **Unknown business rules** | 11 of the 12 policy values have no confirmed value (system-overview §7; `listing_page_size` = 60 is CONFIRMED). Each one fails closed in production, and each has a Q-ID. The guest experience, recommendation logic, back-office and checkout are not specified | system-overview §7, §10; open-questions |
| **Technical dependencies** | Catalog ← Sales ← Inventory ← Cart, with Listing reading from all three (backend-architecture §2). The scheduler drives expiry and boundary events. Countdowns depend on `meta.server_time` | backend-architecture §2, §10; ADR-009 |
| **Risks** | {{NR}} risks are registered in [08-risk-register.md](08-risk-register.md) | — |

### 2.1 Inconsistencies found and how the backlog handles them
None of these is resolved silently. Each one is either raised as a question or recorded as a risk.

| ID | Inconsistency | Handling in Phase 06 |
|---|---|---|
| I-01 | **Phase 05 schema erratum.** `categories` has UQ (parent_id, slug), but `parent_id` is nullable. MySQL unique indexes treat NULLs as distinct, so two root categories can share a slug | Risk R-07 and question Q-84. The fix is a task on US-012. [schema.md](../architecture/database/schema.md) is **not edited** until the architect approves an erratum |
| I-02 | FR-CRT-08 (reservation expiry) is a Phase 04 HYPOTHESIS with Could priority. Yet C-08 says a timer without expiry means nothing: a reserved unit would never return to stock | US-040 keeps priority Could and status Needs Clarification. It is flagged as an MVP-critical decision (R-01, Q-37) |
| I-03 | PRD §8 says UNKNOWN requirements "have to be specified before design". Phase 05 designed seams for them anyway (settings, structure only) | NFR-TIME-01, NFR-PERF-01 and NFR-RESP-01 stay Must and Blocked (US-047, US-056, US-048) |
| I-04 | Phase 04 US-10 states the 20-minute reservation as CONFIRMED. Phase 05 makes the duration a PO setting, because 20 minutes is the reference's value, not the new app's (Q-25) | US-037 uses `reservation_minutes`; acceptance needs the PO value |
| I-05 | FR-CRT-07 ("Commander" leads to checkout) is a Must, but checkout is OUT OF SCOPE | US-041 is Blocked (Q-04, Q-80). It builds the button only |
| I-06 | Phase 04 US-16 (recommendations) is CONFIRMED, but the logic is UNKNOWN. AC-FR-REC-01-1 ("both rails are shown") can't pass while v1 rails are empty | US-062 is Needs Clarification (Q-29). Risk R-21 |
| I-07 | Phase 04 US-19 combines CONFIRMED account pages with order and return data that belong to checkout | Split: US-006 (navigation and overview), US-007 (personal details) and US-011 (empty states only) |
| I-08 | FR-DLV-02 (Must) needs the returns help article to state the same period as the PDP and the terms, but the help centre (FR-CNT-02, FR-DLV-01) is a Should | US-049 is pulled into the MVP sprints with that reason |
| I-09 | design-validation §10 calls the catalogue "Ready to build", yet Q-70 must be answered before the migrations are frozen, and the migrations are the first thing built | US-012, US-015 and US-036 carry Q-70. Q-70 is a Sprint 0 gate |
| I-10 | Phase 04 US-23 and US-24 use the merchandiser persona, which is a HYPOTHESIS, and no back-office is designed | The lifecycle is reframed from the member's view (US-015). Data entry is split into seed data (US-064, Ready) and production data entry (US-065, Blocked on Q-50) |
| I-11 | Password reset and e-mail verification exist in the Phase 05 API, but no Phase 04 requirement asks for them | US-003 and US-004 are marked as design-derived and need PO confirmation. US-004 is lowered to Should, with the reason stated |
| I-12 | The Phase 05 validation scripts weren't kept, so design-validation §1 can't be re-run as it stands | US-058 re-scripts them. The Phase 06 checks live in the repository (`tools/phase06/gen.py`) |

## 3. MVP definition
**MVP = the core journey J1 (browse → campaign → listing sort and filter → PDP → reserve → cart), brand search J4, and the legal, security and localisation baselines.** This is the PRD §8 "Must" rule, so the PO still has to confirm it.

- **What the MVP can do:** a member registers and logs in, browses live sales, finds articles by listing, category, brand and search, reserves a size, and manages the reserved items.
- **What it can't do:** **the MVP can't take orders.** Checkout and payment (M21) are OUT OF SCOPE. Whether the MVP is an internal demo, a closed pilot or a public launch is therefore a decision for the sponsor (Q-80).
- **Release state:** the MVP is **buildable, but not releasable**. It can't be released until Q-70 is answered, the FR-CRT-08 expiry decision is made, the 11 open policy values are set, and the legal content exists. The full list is in [10-phase-06-summary.md §4](10-phase-06-summary.md).

## 4. Classification rules (applied mechanically by the generator)
| Classification | Rule |
|---|---|
| **MVP** | CONFIRMED and priority Must in Phase 04 |
| **MVP (pulled in)** | CONFIRMED Should or Could, kept at its priority, but delivered in Sprints 0–5 because an MVP story needs it. A stated reason is required |
| **MVP, blocked by unresolved decision** | UNKNOWN requirement with priority Must. It needs a decision before it can be built or accepted |
| **Blocked by unresolved decision** | HYPOTHESIS requirement. It can't be built as confirmed behaviour until the PO validates it |
| **Post-MVP** | CONFIRMED Should or Could that no MVP story needs |
| **Out of scope** | Phase 04 OUT OF SCOPE (FR-OOS-01 checkout and payment, FR-OOS-02 rewards programme) |

## 5. Requirement classification
**Counts:** {{REQ_COUNTS}}.

{{REQ_TABLE}}

## 6. Features by class (stories)
{{FEATURES}}

## 7. Out of scope or not in the backlog
| Item | Why | Reference |
|---|---|---|
| Checkout, payment, orders, vouchers, fee charging (M21) | OUT OF SCOPE by the project brief; it needs its own specification | FR-OOS-01, Q-04, api-contracts §10 |
| Rewards programme ("Activez Plus") | OUT OF SCOPE; it belongs to the reference group | FR-OOS-02 |
| Wishlist | Never observed; not designed | C-01, Q-76 |
| Addresses | Their only consumer is checkout; not designed | C-02, Q-41 |
| Account deletion and retention | Not designed; needs a PO and Legal decision | Q-73 |
| Notify-me behaviour | HYPOTHESIS and not designed. US-022 holds the place without tasks | FR-UPC-04, Q-47 |
| Back-office UI and imports | Not designed. US-065 holds the release gate without tasks | C-16, Q-50 |
| Mobile apps | None exist (FR-CNT-05) | FR-CNT-05 |
| Marketing-tool sync of newsletter preferences | Tool not chosen | backend-architecture §10 |

=== 02 ===
# Epics — Phase 06

The suggested epic list from the brief is kept, numbered as given, with two changes the documentation justifies:
- **E15 Recommendations (added):** the rails are CONFIRMED UI with UNKNOWN logic. They have their own Phase 05 module and their own open questions (Q-29, Q-78), so they don't fit the listing or storefront epics.
- **E16 Data Administration (added):** no back-office is designed (C-16), but data has to exist for anything to work. It holds the synthetic seed data (Ready) and production data entry (Blocked on Q-50).

**Order of work:** E14 (infrastructure) carries the E14 number from the brief but is **scheduled first**, because every other epic depends on it ([06-sprint-planning.md](06-sprint-planning.md)).

**Module mapping:** each epic maps to Phase 05 modules ([backend-architecture.md §2](../architecture/backend-architecture.md)). Checkout (M21) has no epic.

=== 03 ===
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
| **Ready** | Its own scope needs no decision: every requirement it cites is CONFIRMED, and it has no open question. It **starts once its dependencies are done**. If a dependency is Blocked, the story isn't startable yet; the "Startable" and "Waits on" columns show this |
| **Needs Clarification** | Can be built against the Phase 05 seam (setting, switch or placeholder), but at least one open question must be answered before acceptance. Every HYPOTHESIS story is here or Blocked |
| **Blocked** | Can't be built or can't be accepted at all until a decision is made: an UNKNOWN requirement, an undesigned behaviour, or a schema that can't be frozen |

## 3. Summary
{{SUMMARY}}

## 4. Backlog
=== 04 ===
# User stories — Phase 06

These stories refine the 27 Phase 04 stories ([user-stories.md](../requirements/user-stories.md)) into implementation-sized units, mapped to the Phase 05 design.

## How to read a story
- **IDs:** Phase 06 IDs have three digits (`US-001`). Phase 04 IDs have two (`US-01`), and each story names its Phase 04 parent, or "new" for requirements that had no Phase 04 story (FR-NAV-01, FR-CMP-06, FR-CAT-09, FR-PDP-04, FR-CNT-05, FR-ANL-01 and most NFRs).
- **Sources:** "Source requirements" are exact SRS IDs, with their Phase 04 class. Enabler stories (infrastructure, spike, seed data) also cite the FR or NFR they enable.
- **Status:** see [03-product-backlog.md §2](03-product-backlog.md). The open questions blocking a story are listed next to its status.
- **Acceptance criteria:**
  - They reuse the Phase 04 criteria (`AC-…` IDs and observed French copy) and the Phase 05 contract behaviour (HTTP codes, error codes).
  - A criterion that implements a Phase 05 **PROPOSED** rule says so.
  - A **Pending** criterion names what can't be accepted yet and why. It is a placeholder, not a requirement.
- **Labels:** no story turns a HYPOTHESIS or UNKNOWN item into confirmed behaviour. HYPOTHESIS stories say "pending PO validation" in their note.
- **Generated:** from `tools/phase06/backlog.txt` by `tools/phase06/gen.py`.

=== 05 ===
# Technical tasks — Phase 06

Tasks are listed per story and grouped into Backend, Frontend and Infrastructure. Test tasks are marked "(test)".

**Task IDs:** `T-<story number>-<type><n>`, where the types are:
- `B` backend
- `BT` backend test
- `F` frontend
- `FT` frontend test
- `I` infrastructure

**Not duplicated:** shared foundations are built once and reused, not repeated per story:
- the API base, error renderer, request ID, server time and settings (US-059)
- the API client, auth state and app shell (US-002, US-043)
- the shared listing feature (US-023)
- the MySQL test harness (US-053, US-058)
- formatting and the server clock (US-046)

**Scope of a task:** each task names the Phase 05 building block it implements: module, controller, Form Request, action, query object, component or service. The task list is a plan: no code, migrations, SQL, Docker files or components are written in this phase.

=== 06 ===
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

=== 08 ===
# Risk and dependency register — Phase 06

**Scales:** probability and impact are qualitative (High, Medium, Low), assessed from the documentation. No quantitative data exists.

**Owner roles:**
- Product Owner, Sponsor, Architect, Tech Lead
- Backend Lead, Frontend Lead, QA Lead, DevOps
- Scrum Master, Legal / DPO

**Focus areas named in the brief:**

| Focus area | Risks |
|---|---|
| Cart reservation expiry lifecycle | R-01, R-02, R-05, R-06 |
| Atomic stock reservation and release | R-03 |
| Concurrent reservation scenarios | R-03, R-04, R-05 |
| Root category slug uniqueness in MySQL | R-07 |
| Campaign/product/variant consistency | R-08, R-09 |
| Unverified MySQL behaviours | R-10 |
| Data administration (back-office, imports, seeders) | R-11, R-12 |
| Checkout boundary | R-13, R-14 |
| Unresolved business rules | R-15, R-16, R-19, R-20, R-21, R-22, R-23, R-26, R-29 |

**Key dependencies:**
- Q-70 → US-012 / US-015 / US-036 (schema)
- US-061 spike → every migration
- US-058 MySQL CI → US-053 concurrency tests
- the `scheduler` service → US-040 and the campaign boundary events
- Q-80 → US-041 and US-065

=== 09 ===
# Open questions register — Phase 06

This register lists every decision the backlog depends on.
- **IDs:** existing IDs are reused from [open-questions.md](../requirements/open-questions.md), with no renumbering. New Phase 06 questions start at **Q-80** and are also added there as section H, so there is still one register.
- **UNKNOWN stays UNKNOWN:** no UNKNOWN decision is converted into a confirmed requirement. Each one blocks the stories listed.
- **Affected stories:** computed by the generator from each story's blocking questions and text.

**Not listed, because they don't block implementation:**
- Q-02: answered.
- Q-22: the design allows a campaign on several landings.
- Q-60–Q-62 and Q-64: Phase 03 transport details. The new REST API is designed independently.

=== 10 ===
# Phase 06 summary — Project planning and backlog

## 1. What was produced
| File | Content |
|---|---|
| [01-mvp-scope.md](01-mvp-scope.md) | Documents analysed, findings, inconsistencies I-01–I-12, the MVP definition, and the classification of all {{NREQ}} requirements |
| [02-epics.md](02-epics.md) | {{NE}} epics (the brief's 14, plus Recommendations and Data Administration), mapped to modules and requirements |
| [03-product-backlog.md](03-product-backlog.md) | The prioritised backlog of {{N}} stories, with status definitions |
| [04-user-stories.md](04-user-stories.md) | {{N}} stories with Given/When/Then criteria, API and table dependencies |
| [05-technical-tasks.md](05-technical-tasks.md) | {{T}} tasks split into Backend, Frontend and Infrastructure |
| [06-sprint-planning.md](06-sprint-planning.md) | Sprint 0 to Sprint 7 as a provisional sequence, with decision gates |
| [07-definition-of-done.md](07-definition-of-done.md) | Project-specific Definition of Done |
| [08-risk-register.md](08-risk-register.md) | {{NR}} risks with owners |
| [09-open-questions.md](09-open-questions.md) | {{NQ}} decisions, with affected stories |

**Source and tooling:**
- **Source:** `tools/phase06/backlog.txt` is the single source for files 01–06 and 08–10.
- **Generator:** `tools/phase06/gen.py` writes them and runs the checks in §2. Re-run it after any edit: `python -I tools/phase06/gen.py .` from the project root. It's documentation tooling, not application code.
- **Register:** `docs/requirements/open-questions.md` gained section H (Q-80–Q-86).
- **Phase 05 untouched:** no Phase 05 file was changed. The schema erratum is recorded, not applied (I-01).

## 2. Final validation (generated)
{{VALIDATION}}

**Limit:** the checks prove that the documents are consistent: IDs exist, the dependency order holds, and the labels are preserved. They don't prove that the plan is right. That's for the decision workshop and sprint reviews.

## 3. Backlog readiness
{{BY_STATUS}}

{{MVP_LINE}}

**Ready and startable now (Sprint 0):** {{S0_READY}}.

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
