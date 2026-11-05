# ADR-002: React feature-based structure

- **Status:** Proposed
- **Date:** 2026-11-04 (Phase 05)
- **Related requirements:** FR-NAV-*, FR-CAT-*, FR-PDP-*, FR-CRT-*, FR-ACC-*

## Context
The SPA has distinct feature areas: landing, listing, product, search, cart, account, auth, content and recommendations. They share a small set of UI primitives: dialogs, countdowns and prices. Phase 04 acceptance criteria are written per feature.

## Decision
Organise `frontend/src` by **feature** (`features/<feature>/{components,pages,api,slice}`).
- **Shared code:** `shared/ui` holds generic accessible primitives, and `shared/lib` holds formatting and time utilities.
- **Dependency rule:** features may import from `shared/` and from another feature's **public index** only; never deep imports.

## Alternatives considered
- **By type** (`components/`, `pages/`, `hooks/`, `services/`): familiar, but a single feature is scattered across folders.
- **Atomic-design only:** good for the UI kit, poor for business features.

## Consequences
- **Positive:** a feature's code, tests and API endpoints sit together, and acceptance criteria map to folders.
- **Negative:** some duplication risk between features. It's mitigated by `shared/` and by code review.
