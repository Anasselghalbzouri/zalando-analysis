# ADR-006: Front-end state management (RTK Query + minimal slices)

- **Status:** Proposed
- **Date:** 2026-11-04 (Phase 05)
- **Related requirements:** FR-CRT-02, FR-NAV-05, FR-CAT-03–09, FR-CMP-02

## Context
The brief asks to evaluate Redux Toolkit / RTK Query. Most state is server state:
- landings, listings, articles and the cart
- reservation-driven invalidation (adding to the cart changes the cart, article availability and tile state)
- server-time-based countdowns

UI state is small: drawer open, toasts. Filters must survive reloads and be shareable.

## Decision
- **Server state:** RTK Query for all server state, with tag invalidation (`Cart`, `Article:{sku}`, `Listing`, `Me`, …).
- **Slices:** Redux Toolkit slices only for `ui` and the server/client `clock` offset.
- **Filters:** filters, sort and page live in the **URL**.
- **Forms:** local component state.

Detail is in [frontend-architecture.md §7](../frontend-architecture.md).

## Alternatives considered
- **TanStack Query + Zustand:** excellent, especially for infinite queries, but two libraries and a departure from the stated stack.
- **Plain Redux + thunks:** hand-rolled caching and invalidation.
- **Context + fetch:** no cache; re-render cascades.

## Consequences
- **Positive:** one store and devtools; declarative invalidation matches the reservation flows; de-duplicated requests.
- **Negative:** heavier than the minimal alternatives. If Q-11 picks infinite scroll, verify the current RTK Query infinite-query support, or merge pages manually.
