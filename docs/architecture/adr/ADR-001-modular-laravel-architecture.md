# ADR-001: Modular Laravel architecture (modular monolith)

- **Status:** Proposed
- **Date:** 2026-11-04 (Phase 05)
- **Related requirements:** FR-CMP-*, FR-CRT-*, NFR-CON-01, NFR-TECH-01

## Context
A single team builds a flash-sale shop. Its core invariant spans campaigns, stock and the cart: no overselling under concurrent adds (NFR-CON-01). Load targets are UNKNOWN (Q-42). The Phase 02 dependency graph shows one-directional dependencies: catalogue → sales → inventory → cart, with listing and recommendations reading from them.

## Decision
Build **one Laravel application**, organised as domain modules under `app/Modules/<Module>`: Identity, Account, Catalog, Sales, Inventory, Cart, Listing, Recommendations, Content, Storefront.
- **Ownership:** each module owns its tables, models, HTTP layer, services and events.
- **Cross-module access:** only through public services and domain events.
- **Direction:** the dependency direction is enforced by architecture tests ([backend-architecture.md §2](../backend-architecture.md)).
- **No repository layer:** Eloquent is used directly. Listing uses query objects.

## Alternatives considered
- **Microservices** (catalogue, cart, inventory as services): the reservation would need distributed transactions or sagas, plus operational overhead, with no load evidence to justify it.
- **Default Laravel layout** (`app/Http`, `app/Models` flat): simplest to start, but module boundaries erode as the code grows.
- **Packages per module** (separate Composer packages): stronger isolation, but too much ceremony for one team.

## Consequences
- **Positive:** one deployable; ACID transactions for reservations; a clear seam to extract a module later.
- **Negative:** discipline is needed to keep boundaries, which the architecture tests help with. One database is a shared scaling unit.
- **Follow-up:** revisit if the PO's load targets (Q-42) exceed what one MySQL primary can serve.
