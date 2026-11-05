# ADR-005: Product variant and inventory modelling

- **Status:** Proposed
- **Date:** 2026-11-04 (Phase 05)
- **Related requirements:** FR-PDP-02, FR-PDP-05, FR-STK-01, FR-STK-02, NFR-CON-01

## Context
- **Sizes:** a member picks a size before adding (FR-PDP-02).
- **Low stock:** shown per size and live (FR-PDP-05).
- **Fair reservation:** a reserved unit must be unavailable to everyone else, and concurrent adds must never oversell (FR-STK-01, NFR-CON-01).
- **Not observed:** per-campaign stock allocation is a HYPOTHESIS (PRD §2.2), and no restocking happens during a sale (FR-STK-02).

## Decision
- **Variant:** a **variant** is product × size value (`product_variants`, with `size_values` per size system).
- **Stock row:** the **stock** row is campaign article × variant (`inventory_items`), holding the allocated, reserved and sold counters and a generated `quantity_available`.
- **Reserve:** a single **conditional atomic update** (`… WHERE id = ? AND quantity_available >= qty`) inside the add-to-cart transaction. Success is one affected row; otherwise 409 `out_of_stock`.
- **Integrity:** composite foreign keys force the variant and the article to share one product.
- **Ownership:** `quantity_sold` is reserved for the checkout boundary.

## Alternatives considered
- **Stock per variant only** (shared across campaigns): simpler, but can't give a campaign its own limited quantity. The PO may still choose it (Q-70), which would be a schema change before build.
- **Pessimistic locking** (`SELECT … FOR UPDATE`, then update): correct, but holds locks longer. The conditional update gets the same guarantee in one statement.
- **Counting reservations on the fly** (no counter): no redundancy, but every add needs a locked aggregate and grows slower with traffic.
- **Redis counters:** fast, but a second source of truth to reconcile with MySQL.

## Consequences
- **Positive:** oversell is impossible by construction; availability reads are cheap and indexable.
- **Negative:** `quantity_reserved` is a counter that must stay consistent with `cart_reservations`. Every change goes through `ReserveUnit` / `ReleaseUnit` in one transaction. A reconciliation command (recount from reservations) is PROPOSED as an operational safety net.
