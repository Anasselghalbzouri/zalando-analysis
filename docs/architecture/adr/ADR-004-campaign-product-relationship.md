# ADR-004: Campaign and product relationship

- **Status:** Proposed
- **Date:** 2026-11-04 (Phase 05)
- **Related requirements:** FR-CMP-06, FR-CMP-07, FR-CMP-03, FR-PRC-01, FR-CAT-03

## Context
Reference facts:
- **CONFIRMED:** products are addressed inside a campaign (`/campaigns/{campaignId}/articles/{sku}`, BR-03-01).
- **CONFIRMED:** one *model code* appeared in two campaigns with *different SKU suffixes* (BR-03-14).
- **Not seen:** an identical full SKU in two campaigns.
- **HYPOTHESIS:** price and availability are bound to the campaign (BR-03-15).

Whether a product means model or model + colour is UNKNOWN (Q-70).

## Decision
Separate the **catalogue product** (`products`: brand, type, texts, images, size system) from the **campaign article** (`campaign_articles`):
- **Article fields:** campaign, product, globally unique SKU, RRP, sale price, generated `discount_percent`.
- **Uniqueness:** one article per (campaign, product).
- **Prices** live on the article. The headline is computed, manual or "Special" (Q-23).
- **Allowed, not confirmed:** the schema **allows** the same product in several campaigns, with different prices. That's a PROPOSED capability, not a confirmed rule.

## Alternatives considered
- **Price on `products`, with campaigns as a tag:** can't express different prices per campaign, and breaks the campaign-scoped URL.
- **Copy products per campaign** (no shared catalogue): duplicates brand, texts and images, so edits drift.
- **Price history table with validity ranges:** models time-based pricing, but campaign scoping already gives the validity window. Unnecessary.

## Consequences
- **Positive:** matches the campaign-scoped addressing; catalogue data is stored once; the discount sort is indexable.
- **Negative:** listings always join article → product → brand. Acceptable at the observed scale (indexing-strategy §6).
- **Open:** if Q-70 decides that product = model + colour, nothing changes structurally. If it means model only, colour becomes a variant dimension (a schema change before build).
