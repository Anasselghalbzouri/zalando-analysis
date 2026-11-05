# Phase 08 — Progressive development with Spec Kit

Phase 08 builds the Phase 06 backlog story by story, sprint by sprint, with **GitHub Spec Kit 1.2.4** (spec-driven development). This guide explains the set-up and the loop. It also gives the ready-to-run feature list for Sprint 0.

## 1. What is installed
| Item | Where | Notes |
|---|---|---|
| Spec Kit project files | `.specify/` | Templates, Python scripts (`--script py`, so no PowerShell 7 needed), workflow, git extension |
| Claude Code skills | `.claude/skills/speckit-*` | Versioned in Git (local Claude settings stay ignored) |
| Constitution | [.specify/memory/constitution.md](../../.specify/memory/constitution.md) | v1.0.0, nine principles from Phases 04–07. Every plan is checked against it |
| Git extension | `.specify/extensions/git/` | Creates one branch per feature: `feature/NNN-<slug>`. **Auto-commit is off**: commits only when you ask |

The skills are available as slash commands in a **new** Claude Code session started in the repository root:
- `/speckit-constitution`, `/speckit-specify`, `/speckit-clarify`, `/speckit-plan`
- `/speckit-tasks`, `/speckit-analyze`, `/speckit-implement`, `/speckit-checklist`
- `/speckit-converge`, `/speckit-git-*`

## 2. Mapping rules (agreed)
- **One Spec Kit feature = one Phase 06 story.** For example, `US-059` becomes branch `feature/001-us-059-api-foundation` and folder `specs/001-us-059-api-foundation/`.
  - The number `NNN` is assigned by Spec Kit in creation order.
  - The `US-0xx` part keeps the traceability.
- **Sprints set the order.** Follow [06-sprint-planning.md](../phase-06/06-sprint-planning.md).
  - Skip a story whose "Startable" column is `no` in [03-product-backlog.md](../phase-06/03-product-backlog.md): it waits on a Blocked story.
  - A sprint starts only when its decision gate is met.
- **The spec doesn't re-invent the story.** It points to the story, its requirements and the Phase 05 contracts.
  - Every open point stays a `[NEEDS CLARIFICATION: Q-xx]` marker (constitution, Principle I).

## 3. The loop for each story
| Step | Command | Output | Check before moving on |
|---|---|---|---|
| 1 | Start Docker: `docker compose up -d --wait` | Running stack | `docker compose ps` all healthy |
| 2 | `/speckit-specify <story prompt from §4>` | Branch and `specs/NNN-…/spec.md` | The spec cites the US, FR/NFR and AC IDs; open questions keep their Q-IDs |
| 3 | `/speckit-clarify` (only if the spec has markers) | Answers recorded in the spec | Only PO answers or document facts; still-open Q-IDs stay open |
| 4 | `/speckit-plan` | `plan.md`, `research.md`, `data-model.md`, `contracts/`, `quickstart.md` | Constitution Check passes; the plan matches api-contracts, schema and module boundaries |
| 5 | `/speckit-tasks` | `tasks.md` | Covers the story's `T-0xx-*` tasks from [05-technical-tasks.md](../phase-06/05-technical-tasks.md); tests first |
| 6 | `/speckit-analyze` | Consistency report | No critical findings |
| 7 | `/speckit-implement` | Code and tests | Tests pass in the containers; [Definition of Done](../phase-06/07-definition-of-done.md) met |
| 8 | Update `tools/phase06/backlog.txt`, then run `python -I tools/phase06/gen.py .` | Backlog docs regenerated | All checks pass |
| 9 | Ask for the commit and the merge | Commit on the feature branch | Only when you ask |

## 4. Sprint 0 features (ready to run)
**US-057** (Docker environment) was built in Phase 07. It needs only PO acceptance against its criteria, not a new feature.

Run the rest in this order. Each prompt goes after `/speckit-specify`.

| Order | Story | Prompt | Gate |
|---|---|---|---|
| 1 | US-059 API foundation | `US-059 API foundation. Implement exactly the story in docs/phase-06/04-user-stories.md#us-059 (NFR-OBS-01, NFR-TECH-01): /api/v1 route groups by auth code, the problem+json renderer covering the error-format.md catalogue, request ID and server time middleware, config/storefront.php and GET /api/v1/storefront/settings that fails closed in production. Policy values are PO decisions: no silent defaults (system-overview §7).` | None (Ready) |
| 2 | US-060 Transport security and caching headers | `US-060 transport security and caching headers. Implement exactly docs/phase-06/04-user-stories.md#us-060 (NFR-SEC-03, NFR-SEC-04, NFR-CACHE-01) in docker/nginx and the backend: HSTS (production-like mode only), CSP, frame restriction, no-store on S endpoints and availability reads, immutable hashed assets.` | None (Ready) |
| 3 | US-061 MySQL behaviour spike | `US-061 MySQL behaviour spike. Implement exactly docs/phase-06/04-user-stories.md#us-061: verify on MySQL 8.4 CHECK constraints next to FK referential actions, unsigned subtraction in the generated columns quantity_available and discount_percent, and the composite FKs through product_id. Throwaway experiments only; record results as a proposed schema.md erratum for the architect.` | None (Ready) |
| 4 | US-046 French formatting and server clock | `US-046 French price, date and countdown formatting. Implement exactly docs/phase-06/04-user-stories.md#us-046 (NFR-I18N-03): Intl fr-FR money and date helpers, the clock slice and useServerNow from meta.server_time. No storefront page.` | After US-059 |
| 5 | US-058 Continuous integration | `US-058 continuous integration. Implement exactly docs/phase-06/04-user-stories.md#us-058 (NFR-TECH-01): CI with PHP and JS lint, back-end tests on a MySQL 8 service, front-end tests, SPA build, architecture tests, and the Phase 06 backlog checks.` | **Q-82** (Git host and CI platform) |

**Before Sprint 1:** hold the decision workshop on the S0 gate questions:
- **Q-70** product granularity
- **Q-71** catalogue access
- **Q-40** login methods
- **Q-80** MVP purpose
- **Q-81** capacity
- **Q-84** root-slug fix

US-012 (catalogue data model) and US-015 (sales model) stay Blocked until Q-70 and Q-84 are answered.

## 5. Rules that keep Spec Kit aligned with the backlog
- **Don't run `/speckit-specify` with a free-text idea.** Always start from a `US-0xx` story. New ideas go to the PO and the backlog first.
- **`/speckit-converge`** is useful after a sprint: it lists remaining work as tasks. Its output must still map to existing stories.
- **Keep Spec Kit's auto-commit hooks disabled** (`.specify/extensions/git/git-config.yml`, `auto_commit.default: false`).
- **Never let `/speckit-implement` create** checkout, payment or order code, or anything else listed in Principle II.
