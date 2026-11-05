# Storefront (working name) Constitution

The product name is undecided (Q-75). "Storefront" is a neutral placeholder. Reference-platform
branding is never used.

## Core Principles

### I. Traceable Requirements, No Invention (NON-NEGOTIABLE)
- Every Spec Kit feature implements exactly one Phase 06 user story (`US-0xx`) and MUST cite it,
  together with its exact requirement IDs (`FR-…`, `NFR-…`) and acceptance criteria (`AC-…`).
  - Story: [docs/phase-06/04-user-stories.md](../../docs/phase-06/04-user-stories.md)
  - Requirements: [docs/requirements/SRS.md](../../docs/requirements/SRS.md)
- Sources of truth, in order of precedence:
  1. Phase 04 requirements and acceptance criteria.
  2. Phase 05 architecture (`docs/architecture/`), including the ADRs and the API contracts.
  3. The Phase 06 story and its tasks.
- A spec MUST NOT add a business rule that these documents don't contain.
- Labels are preserved: CONFIRMED, PROPOSED, HYPOTHESIS, UNKNOWN.
  - Every UNKNOWN or HYPOTHESIS point becomes a `[NEEDS CLARIFICATION: Q-xx]` marker that names
    its open question in [docs/requirements/open-questions.md](../../docs/requirements/open-questions.md).
  - It is never silently resolved.
- A new open question gets the next free Q-ID in that register before the spec proceeds.

Rationale: the project exists to implement an analysed, authorised scope. Invented rules are
defects, even when they look reasonable.

### II. Scope Boundaries
- Checkout and payment (M21, FR-OOS-01) are OUT OF SCOPE:
  - no order, payment, voucher or fee-charging code, table, endpoint or integration
  - the checkout boundary stays a contract outline (api-contracts §10)
- Also not built unless a later approved specification adds them:
  - the rewards programme (FR-OOS-02)
  - wishlist (C-01)
  - addresses (C-02)
  - account deletion (Q-73)
  - notify-me (FR-UPC-04)
  - a back-office UI (Q-50)
- No source code, branding, copy, images, identifiers or data from the reference platform.
  Seed and test data are synthetic.

Rationale: scope exclusions were agreed in Phases 01–06, and independence is a legal and ethical
constraint.

### III. Architecture Conformance
- **Backend:** a modular monolith in `app/Modules/<Module>/`. The modules are Identity, Account,
  Catalog, Sales, Inventory, Cart, Listing, Recommendations, Content and Storefront.
  - Dependencies point one way (backend-architecture §2), and an architecture test enforces that.
- **Backend layers:**
  - controllers are thin
  - validation and authorization at the boundary live in Form Requests
  - business rules live in actions and services
  - complex reads use query objects, not generic repositories
- **API:** REST under `/api/v1`, exactly as in
  [api-contracts.md](../../docs/architecture/api/api-contracts.md).
  - Errors are `application/problem+json` per
    [error-format.md](../../docs/architecture/api/error-format.md).
  - Every success response carries `meta.server_time`.
- **Front end:** a React feature-based structure (frontend-architecture §16). Server state lives in
  RTK Query; filter state lives in the URL.
- **Authentication:** a same-origin Sanctum SPA session with CSRF (ADR-003). No API tokens.
- **Changing the design:** any deviation from Phase 05 needs an ADR or an approved erratum first.
  This applies to schema columns, endpoints, module boundaries, auth and the libraries in use.

Rationale: the design was validated as a whole. Local shortcuts break its consistency checks.

### IV. Data Integrity and Concurrency
- **Database:** MySQL 8 with InnoDB and `utf8mb4_0900_ai_ci`.
  - Migrations follow [schema.md](../../docs/architecture/database/schema.md) and are reversible.
- **Money and time:** money is integer cents with a currency; times are stored and exchanged in UTC.
- **Campaign state:** it is derived from the request clock through the single live predicate. It is
  never stored.
- **Stock:** `inventory_items.quantity_reserved` changes only through `ReserveUnit` and
  `ReleaseUnit`.
  - Each change is a single conditional atomic update inside a transaction; never a read-then-write.
  - The invariant `reserved + sold ≤ allocated` always holds.
- **Unverified MySQL behaviours (US-061):** the Catalog, Sales and Inventory migrations are not
  frozen before the spike results are approved. Q-70 must be answered first.

Rationale: overselling and lifecycle errors are the highest-impact risks (R-01–R-10).

### V. Test-First Verification
- Each acceptance criterion maps to at least one automated test, written before or with the code.
- Tests that involve constraints, transactions or concurrency run on MySQL 8, never SQLite.
- Time-dependent rules use time-travel tests on the request clock.
- Stock rules pass the parallel-connection suite (US-053).
- Tests never touch the development database (`backend/phpunit.xml` forces isolation).
- A check is reported as passed only when it was actually run. Failures are reported with their
  exact output.

Rationale: the backlog's Definition of Done depends on evidence, not on claims.

### VI. Security and Privacy by Default
- **CSRF:** every unsafe method requires a valid CSRF token.
- **Identity:** member resources are resolved from the session. No user ID appears in a URL or
  body, and a foreign resource answers 404.
- **Caching:** cart, account and availability responses carry `Cache-Control: no-store`.
- **Secrets:** no secret, credential, token or personal data in code, logs, fixtures, commits or
  snapshots. Only `*.env.example` files with placeholders are committed.
- **Consent:** no third-party script loads before consent, and no personal attribute reaches
  analytics without it (NFR-PRV-01).

Rationale: NFR-SEC-01–NFR-SEC-04 and NFR-PRV-01 are Must requirements.

### VII. Decisions as Configuration
- **Policy values:** values the PO hasn't decided live in `config/storefront.php`, read from the
  environment, and fail closed in production.
  - Examples: reservation duration, quantity limits, return period, timezone, catalogue access.
  - They never get a silent default in code.
- **HYPOTHESIS behaviours:** each sits behind one switch that stays off until the PO records a
  validation decision. They are FR-CMP-08, FR-CRT-08, FR-PDP-04, FR-STK-03, FR-UPC-04, FR-EVT-03,
  FR-EVT-05 and NFR-SEC-05.

Rationale: open decisions must stay changeable without code changes, and visible as release gates.

### VIII. French-First, Accessible UI
- **Copy:** every UI string, including ARIA labels, lives in
  `frontend/src/shared/i18n/messages.fr.js`.
  - Copy is the observed French wording or PO-approved text.
  - Plurals use `Intl.PluralRules('fr-FR')`, and money and dates use `Intl` with fr-FR.
- **Accessibility:** keyboard operation, visible focus, focus trap and return in dialogs,
  announced alerts, and alt text on every image (NFR-A11Y-01).
- **Layout:** mobile-first structure. Breakpoint behaviour waits for Q-15.

Rationale: NFR-I18N-01–03 and NFR-A11Y-01 are Must requirements.

### IX. Simplicity
- Build the smallest change that satisfies the story's acceptance criteria.
- No speculative abstraction, option or "for later" code.
- No new dependency for a few lines of code. A new dependency needs a stated reason in the plan.
- Prefer framework features, and code already in the repository, over new code.

Rationale: a small, readable codebase is easier to verify against the specification.

## Technology and Environment Constraints
- **Stack (NFR-TECH-01):**
  - Laravel 13 on PHP 8.3+ (8.4 in Docker)
  - React 19 with Vite, React Router, Redux Toolkit / RTK Query and Tailwind CSS
  - MySQL 8, Nginx, Docker Compose and Git
  - A change of stack requires PO approval.
- **Local environment (Phase 07):** `compose.yaml` with nginx, app, scheduler, queue, mysql and
  vite. Redis and Mailpit are optional profiles. Everything runs on one origin, `localhost:8080`.
- **Tools run inside the containers:** `docker compose exec app …` and
  `docker compose exec vite …`.
- **Line endings are LF** (`.gitattributes`).

## Development Workflow and Quality Gates
1. **Order:** stories are taken in the Phase 06 sprint order
   ([docs/phase-06/06-sprint-planning.md](../../docs/phase-06/06-sprint-planning.md)).
   - A story enters a sprint only if its decision gate is met.
   - A story with a Blocked dependency isn't started ("Waits on" column in
     [03-product-backlog.md](../../docs/phase-06/03-product-backlog.md)).
2. **One Spec Kit feature per story:**
   - Branch: `feature/NNN-us-0xx-short-name`, created by the git extension.
   - Spec directory: `specs/NNN-us-0xx-short-name/`.
3. **Command sequence:** `/speckit-specify` → `/speckit-clarify` (whenever the spec has
   `[NEEDS CLARIFICATION]` markers) → `/speckit-plan` → `/speckit-tasks` → `/speckit-analyze` →
   `/speckit-implement`.
   - The plan's Constitution Check MUST pass Principles I–IX, or record a justified exception.
4. **Clarification:** `/speckit-clarify` records answers only from the PO or the documents. An open
   Q-ID stays open in the spec and in the register; it is never answered by assumption.
5. **Done:** a story is Done only against the Phase 06 Definition of Done
   ([07-definition-of-done.md](../../docs/phase-06/07-definition-of-done.md)). That includes
   updating `tools/phase06/backlog.txt` and passing `python -I tools/phase06/gen.py .`.
6. **Commits and pushes happen only when the user asks.** Spec Kit auto-commit hooks stay
   disabled.

## Governance
- **Precedence:** this constitution governs Phase 08 and later development. Where it conflicts
  with a convenience or habit, the constitution wins. Where it conflicts with a Phase 04–06
  document, the conflict is raised as an open question; it isn't decided by either side.
- **Amendments:** they need a written proposal, the reason, the affected principles and the
  user's approval. They are recorded in this file with a version bump.
- **Versioning:** semantic.
  - MAJOR: a principle is removed or redefined.
  - MINOR: a principle or section is added, or guidance is materially expanded.
  - PATCH: wording only.
- **Compliance review:** every `/speckit-plan` Constitution Check, every `/speckit-analyze` report
  and every review checks compliance with Principles I–IX. Violations are fixed before merge, or
  documented as an approved exception.

**Version**: 1.0.0 | **Ratified**: 2026-11-05 | **Last Amended**: 2026-11-05
