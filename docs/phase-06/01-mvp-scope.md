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
| **Confirmed requirements** | 95 requirements: 82 CONFIRMED, 8 HYPOTHESIS, 3 UNKNOWN, 2 OUT OF SCOPE. There are 78 functional and 17 non-functional requirements | SRS, traceability matrix |
| **Proposed decisions** | Everything in Phase 05 is PROPOSED: the modular monolith, Sanctum SPA sessions, campaign articles, inventory per article × size, derived campaign state, RTK Query, MySQL FULLTEXT, database drivers with optional Redis. The 8 HYPOTHESIS behaviours each sit behind one switch or seam | system-overview §1–§2, ADR-001–ADR-010 |
| **Unknown business rules** | 11 of the 12 policy values have no confirmed value (system-overview §7; `listing_page_size` = 60 is CONFIRMED). Each one fails closed in production, and each has a Q-ID. The guest experience, recommendation logic, back-office and checkout are not specified | system-overview §7, §10; open-questions |
| **Technical dependencies** | Catalog ← Sales ← Inventory ← Cart, with Listing reading from all three (backend-architecture §2). The scheduler drives expiry and boundary events. Countdowns depend on `meta.server_time` | backend-architecture §2, §10; ADR-009 |
| **Risks** | 29 risks are registered in [08-risk-register.md](08-risk-register.md) | — |

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
**Counts:** Blocked by unresolved decision: 8; MVP: 47; MVP (pulled in): 19; MVP, blocked by unresolved decision: 3; Out of scope: 2; Post-MVP: 16.

| Requirement | Phase 04 class | Phase 04 priority | MVP classification | Reason | Stories |
|---|---|---|---|---|---|
| FR-NAV-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-75, Q-85 | US-043 |
| FR-NAV-02 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: bundled in the Must story US-043 | US-043 |
| FR-NAV-03 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline | US-044 |
| FR-NAV-04 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: it is the entry point to category listings (FR-CAT-01, a Must). | US-045 |
| FR-NAV-05 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-25, Q-32, Q-46, Q-51 | US-037 |
| FR-EVT-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline | US-016, US-064 |
| FR-EVT-02 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-20, Q-33, Q-70, Q-77 | US-015, US-016 |
| FR-EVT-03 | HYPOTHESIS | Could | **Blocked by unresolved decision** | HYPOTHESIS: built only behind a switch or as its own story, pending PO validation (Q-50) | US-019 |
| FR-EVT-04 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: bundled in the Must story US-016 | US-016 |
| FR-EVT-05 | HYPOTHESIS | Could | **Blocked by unresolved decision** | HYPOTHESIS: built only behind a switch or as its own story, pending PO validation (Q-16) | US-020 |
| FR-CMP-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-23, Q-24, Q-77 | US-017 |
| FR-CMP-02 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-23, Q-24, Q-77 | US-017 |
| FR-CMP-03 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-23, Q-24, Q-77 | US-017 |
| FR-CMP-04 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: bundled in the Must story US-017 | US-017 |
| FR-CMP-05 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-20, Q-33, Q-50, Q-70, Q-77, Q-80 | US-015, US-053, US-065 |
| FR-CMP-06 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-11, Q-12, Q-20, Q-33, Q-70, Q-77 | US-015, US-023 |
| FR-CMP-07 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: bundled in the Must story US-015 | US-015 |
| FR-CMP-08 | HYPOTHESIS | Could | **Blocked by unresolved decision** | HYPOTHESIS: built only behind a switch or as its own story, pending PO validation (Q-17, Q-21) | US-021 |
| FR-CMP-09 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: bundled in the Must story US-015 | US-015, US-064, US-065 |
| FR-CMP-10 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: bundled in the Must story US-015 | US-015 |
| FR-UPC-01 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-018 |
| FR-UPC-02 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-018 |
| FR-UPC-03 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-018 |
| FR-UPC-04 | HYPOTHESIS | Could | **Blocked by unresolved decision** | HYPOTHESIS: built only behind a switch or as its own story, pending PO validation (Q-01, Q-47) | US-022 |
| FR-CAT-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-11, Q-12 | US-013, US-023, US-064 |
| FR-CAT-02 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-11, Q-12 | US-023 |
| FR-CAT-03 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-43 | US-024 |
| FR-CAT-04 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-14, Q-70, Q-84 | US-012, US-013, US-025 |
| FR-CAT-05 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-026 |
| FR-CAT-06 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: bundled in the Must story US-012 | US-012, US-026 |
| FR-CAT-07 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-026 |
| FR-CAT-08 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-026 |
| FR-CAT-09 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-11, Q-12 | US-023 |
| FR-PDP-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-31, Q-49, Q-70, Q-84 | US-012, US-030 |
| FR-PDP-02 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline | US-032 |
| FR-PDP-03 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-25, Q-32, Q-46, Q-51 | US-037 |
| FR-PDP-04 | HYPOTHESIS | Could | **Blocked by unresolved decision** | HYPOTHESIS: built only behind a switch or as its own story, pending PO validation (Q-46) | US-033 |
| FR-PDP-05 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-26 | US-031 |
| FR-PDP-06 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: bundled in the Must story US-030 | US-030 |
| FR-PDP-07 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-31, Q-49 | US-030 |
| FR-PDP-08 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: bundled in the Must story US-012 | US-012, US-030 |
| FR-SRC-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-44 | US-034 |
| FR-SRC-02 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-44 | US-035 |
| FR-BRD-01 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: bundled in the Must story US-012 | US-012, US-014 |
| FR-BRD-02 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: the PDP brand heading must open `/brands/{code}` (AC-FR-PDP-01-2, a Must). | US-014 |
| FR-PRC-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-31, Q-49 | US-030 |
| FR-PRC-02 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: bundled in the Must story US-038 | US-038 |
| FR-STK-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-45, Q-70 | US-027, US-036 |
| FR-STK-02 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-70 | US-036 |
| FR-STK-03 | HYPOTHESIS | Could | **Blocked by unresolved decision** | HYPOTHESIS: built only behind a switch or as its own story, pending PO validation (Q-36) | US-028 |
| FR-CRT-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline | US-038 |
| FR-CRT-02 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-25, Q-32, Q-46, Q-51 | US-037, US-053 |
| FR-CRT-03 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline | US-038 |
| FR-CRT-04 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline | US-039 |
| FR-CRT-05 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline | US-038 |
| FR-CRT-06 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline | US-038 |
| FR-CRT-07 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-04, Q-80 | US-041 |
| FR-CRT-08 | HYPOTHESIS | Could | **Blocked by unresolved decision** | HYPOTHESIS: built only behind a switch or as its own story, pending PO validation (Q-32, Q-37) | US-040 |
| FR-ACC-01 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-006, US-007 |
| FR-ACC-02 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-006 |
| FR-ACC-03 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-011 |
| FR-ACC-04 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-011 |
| FR-ACC-05 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-008 |
| FR-ACC-06 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-009 |
| FR-ACC-07 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-010 |
| FR-ACC-08 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-03, Q-28, Q-40, Q-41, Q-63, Q-71 | US-001, US-002, US-003, US-004, US-005 |
| FR-REC-01 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-062 |
| FR-REC-02 | CONFIRMED | Should | **Post-MVP** | CONFIRMED Should: secondary to the core journey (PRD §8 rule) | US-063 |
| FR-DLV-01 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: FR-DLV-02 (a Must) needs the returns help article to state the same period as the PDP and the terms. | US-049 |
| FR-DLV-02 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-31, Q-48 | US-050 |
| FR-CNT-01 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: the "*" footnotes of FR-NAV-02 and FR-CMP-03 (Musts) are defined in the footer, and the consent link (FR-CNT-04) lives there. | US-051 |
| FR-CNT-02 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: FR-DLV-02 (a Must) needs the returns help article to state the same period as the PDP and the terms. | US-049 |
| FR-CNT-03 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-31, Q-48 | US-050 |
| FR-CNT-04 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-86 | US-052 |
| FR-CNT-05 | CONFIRMED | Could | **MVP (pulled in)** | CONFIRMED Could, kept at that priority but delivered in the MVP sprints: the "*" footnotes of FR-NAV-02 and FR-CMP-03 (Musts) are defined in the footer, and the consent link (FR-CNT-04) lives there. | US-051 |
| FR-ANL-01 | CONFIRMED | Could | **Post-MVP** | CONFIRMED Could: secondary to the core journey (PRD §8 rule) | US-029 |
| FR-OOS-01 | OUT OF SCOPE | Won't (this spec) | **Out of scope** | Excluded by the project scope (M21 checkout and payment, or the reference group's rewards programme). Not designed | — |
| FR-OOS-02 | OUT OF SCOPE | Won't (this spec) | **Out of scope** | Excluded by the project scope (M21 checkout and payment, or the reference group's rewards programme). Not designed | — |
| NFR-TECH-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-82 | US-057, US-058, US-059, US-061 |
| NFR-I18N-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline | US-055 |
| NFR-I18N-02 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-26 | US-031, US-055 |
| NFR-I18N-03 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline | US-046 |
| NFR-TIME-01 | UNKNOWN | Must | **MVP, blocked by unresolved decision** | Must placeholder for an UNKNOWN requirement; the value or target must be decided first (Q-10) | US-047 |
| NFR-A11Y-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline | US-054 |
| NFR-SEC-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-40, Q-63 | US-002 |
| NFR-SEC-02 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-40, Q-63 | US-002 |
| NFR-SEC-03 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline | US-060 |
| NFR-SEC-04 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline | US-060 |
| NFR-SEC-05 | HYPOTHESIS | Could | **Blocked by unresolved decision** | HYPOTHESIS: built only behind a switch or as its own story, pending PO validation (Q-42) | US-042 |
| NFR-PRV-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-86 | US-052 |
| NFR-CON-01 | CONFIRMED | Must | **MVP** | CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline; acceptance gated by Q-70 | US-036, US-053, US-061 |
| NFR-CACHE-01 | CONFIRMED | Should | **MVP (pulled in)** | CONFIRMED Should, kept at that priority but delivered in the MVP sprints: bundled in the Must story US-060 | US-060 |
| NFR-OBS-01 | CONFIRMED | Could | **MVP (pulled in)** | CONFIRMED Could, kept at that priority but delivered in the MVP sprints: bundled in the Must story US-059 | US-059 |
| NFR-PERF-01 | UNKNOWN | Must | **MVP, blocked by unresolved decision** | Must placeholder for an UNKNOWN requirement; the value or target must be decided first (Q-42, Q-72) | US-056 |
| NFR-RESP-01 | UNKNOWN | Must | **MVP, blocked by unresolved decision** | Must placeholder for an UNKNOWN requirement; the value or target must be decided first (Q-15) | US-048 |

## 6. Features by class (stories)
- **Blocked** (8): US-019 Merchandised order in a landing grid; US-020 Curated landing template; US-021 Refuse reservations outside a campaign's live window; US-022 Ask to be notified when a sale starts; US-028 "Dernières heures" badge; US-033 Button after adding an article; US-040 Release expired reservations; US-042 Throttle bulk add-to-cart.
- **MVP** (41): US-001 Register as an adult member; US-002 Log in and log out with a protected session; US-003 Reset a forgotten password; US-005 Apply one catalogue access mode; US-012 Catalogue data model; US-013 Browse a category listing; US-015 Campaigns and landings with a derived lifecycle; US-016 Event landing pages; US-017 Campaign tile with headline and countdown; US-023 Campaign listing with tiles and pages; US-024 Sort a listing; US-025 Filter a listing; US-027 "Réservé" state on tiles; US-030 Product detail page; US-031 Low-stock indicator; US-032 Size required before adding; US-034 Brand suggestions while typing; US-035 Search results across campaigns; US-036 Atomic stock reservation and release; US-037 Reserve an article in my cart; US-038 View my cart; US-039 Remove an item after confirming; US-041 Hand-off button from cart to checkout; US-043 App shell and header; US-044 Main navigation by vertical; US-046 French price, date and countdown formatting; US-047 Show times in the decided timezone; US-048 Responsive layouts; US-050 Terms, legal pages and one return period; US-052 Cookie consent and privacy; US-053 Concurrency and time-travel test suite; US-054 Keyboard and screen-reader access to the core journey; US-055 French-only, correctly pluralised copy; US-056 Performance targets and load test; US-057 Local Docker environment; US-058 Continuous integration; US-059 API foundation; US-060 Transport security and caching headers; US-061 MySQL behaviour spike; US-064 Synthetic seed data; US-065 Production data entry.
- **MVP (pulled in)** (4): US-014 Browse a brand page; US-045 Mega-menu by category; US-049 Help centre; US-051 Footer and footnotes.
- **Post-MVP** (12): US-004 Verify my e-mail address; US-006 Account navigation and overview; US-007 Edit my personal details; US-008 Choose which sales e-mails I receive; US-009 Unsubscribe with a reason; US-010 Save my sizes; US-011 Orders and returns entry pages; US-018 Upcoming sales; US-026 Filter dialog refinements; US-029 Link-source attribution; US-062 Recommendation rails on the default landing; US-063 Popular rail.

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
