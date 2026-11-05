# Indexing strategy — Phase 05

**Status:** PROPOSED. The indexes are derived from the access paths the requirements need (listing, filtering, sorting, search, cart, lifecycle).
- **No volumes or targets:** they're UNKNOWN (NFR-PERF-01, Q-42), so every index must be checked with `EXPLAIN ANALYZE` on realistic seed data before release.
- **Sizes for test seeds:** the reference observation gives a working order of magnitude. That's ~126 live campaigns on one day, and one campaign with 1,726 results.

## 1. Principles
1. **Index the access path, not the column.** Composite indexes start with the equality column (e.g. `campaign_id`), followed by the sort column.
2. **The live predicate is shared:** `published_at IS NOT NULL AND starts_at <= :now AND ends_at > :now` is used everywhere (ADR-009). `:now` is bound once per request, so every module uses the same instant.
3. **No indexes on low-cardinality flags alone** (`is_active`, booleans).
4. **Generated columns are stored**, which lets them be indexed (`discount_percent`, `quantity_available`).
5. **Writes are cheap enough.** The hot write tables are `inventory_items` and `cart_reservations`, and they carry only the indexes their invariants and sweeps need.

## 2. Index catalogue
| Table | Index | Columns | Serves | Requirement |
|---|---|---|---|---|
| users | UQ | (email) | login, one account per customer | FR-ACC-08 |
| brands | UQ | (code) | `/brands/{code}` | FR-BRD-01 |
| brands | idx | (name) | brand suggestions, prefix `LIKE 'nik%'` (case- and accent-insensitive collation) | FR-SRC-01 |
| categories | UQ | (parent_id, slug) | tree integrity | FR-NAV-04 |
| categories | idx | (path) | subtree listing: `path LIKE '/2/15/%'` | FR-CAT-01 |
| products | idx | (brand_id) | brand listing | FR-BRD-01 |
| products | idx | (category_id) | category listing | FR-CAT-01 |
| products | idx | (model_code) | back-office lookup | FR-CMP-07 |
| products | FULLTEXT | (search_text) | free-text search (ADR-008) | FR-SRC-02 |
| size_values | UQ | (size_system_id, label) | size tabs, saved-size options | FR-CAT-06, FR-ACC-07 |
| product_variants | UQ | (product_id, size_value_id) | PDP sizes | FR-PDP-08 |
| product_variants | idx | (size_value_id, product_id) | size filter: products having size X | FR-CAT-06 |
| product_attribute_values | PK + idx | (product_id, attribute_value_id) + (attribute_value_id, product_id) | facet filter in both directions | FR-CAT-04 |
| landings | UQ | (slug), (default_flag) | landing lookup, a single default | FR-EVT-01 |
| landing_campaigns | PK + idx | (landing_id, campaign_id) + (campaign_id) | grid of a landing; landings of a campaign | FR-EVT-02 |
| campaigns | idx | (ends_at, starts_at) | live set: range on `ends_at > now`, then filter on start | FR-EVT-02, FR-CMP-05 |
| campaigns | idx | (starts_at) | upcoming list ordered by start | FR-UPC-01 |
| campaign_articles | UQ | (sku) | PDP route | FR-CMP-06 |
| campaign_articles | UQ | (campaign_id, product_id) | one article per product per campaign | FR-CMP-07 |
| campaign_articles | UQ | (id, product_id) | composite-FK target | NFR-CON-01 |
| campaign_articles | idx | (campaign_id, merch_position) | campaign listing, "Préférés" (placeholder) | FR-CAT-03 |
| campaign_articles | idx | (campaign_id, sale_price_cents) | price sorts and price-range filter | FR-CAT-03, FR-CAT-07 |
| campaign_articles | idx | (campaign_id, discount_percent) | "Réduction en %" sort; headline `MAX()` | FR-CAT-03, FR-CMP-03 |
| campaign_articles | idx | (campaign_id, created_at) | "Le plus récent" | FR-CAT-03 |
| campaign_articles | idx | (product_id) | cross-campaign listings join from products | FR-CAT-01 |
| inventory_items | UQ | (campaign_article_id, product_variant_id) | PDP sizes, reserve lookup | FR-PDP-05 |
| inventory_items | idx | (campaign_article_id, quantity_available) | "has stock" and "Réservé" state per article | FR-STK-01 |
| cart_reservations | UQ | (user_id, inventory_item_id) | idempotent add, one row per unit type | FR-CRT-02 |
| cart_reservations | idx | (user_id, expires_at) | cart read: active only | FR-CRT-01 |
| cart_reservations | idx | (expires_at) | expiry sweep in batches | FR-CRT-08 (PROPOSED) |
| article_reservation_stats | PK + idx | (campaign_article_id, bucket_hour) + (bucket_hour) | popular rail aggregation, pruning | FR-REC-02 (PROPOSED) |
| content_pages | UQ | (path) | `/help/<category>/<slug>`, `terms` | FR-CNT-02 |
| content_pages | idx | (help_category_id, published_at) | help category page | FR-CNT-02 |

## 3. Access paths

### 3.1 Landing grid (FR-EVT-02)
- **Vertical landing:** `landing_campaigns` by `landing_id` → `campaigns` by PK, with the live predicate. Order by `position`, then `starts_at DESC`.
- **Aggregate landing:** `campaigns` range on `(ends_at, starts_at)`, left-joined to `landing_campaigns` for the ordering override.
- **Cache:** the result is cached per landing (§5).

### 3.2 Listing with filters and sort (FR-CAT-01–09)
- **Context first:** campaign → `campaign_articles (campaign_id, <sort column>)`. Category, brand and search contexts first select candidate products (category subtree, brand or FULLTEXT), then join `campaign_articles (product_id)` and live `campaigns`.
- **Filters** become semi-joins:
  - size: `EXISTS` on `product_variants (size_value_id, product_id)`
  - attribute value: `EXISTS` on `product_attribute_values (attribute_value_id, product_id)`
  - price range on `sale_price_cents`
- **Sort:** when the context is one campaign, the composite index delivers rows in sort order. Cross-campaign contexts sort a filtered set in memory. That's acceptable at the observed scale, but must be measured (§6).
- **Pagination:** offset, 60 per page (BR-06-07). Deep pages are unlikely in a flash-sale assortment. Keyset pagination is the upgrade if measurements say so. The UI mechanism is UNKNOWN (Q-11).

### 3.3 Facets and result count, "Afficher N résultats" (FR-CAT-05)
- **Facets:** one grouped query per facet over the filtered set, **excluding that facet's own filter**, so the user can still widen a selection. Size, brand and attribute values come with counts. The price facet returns min/max (FR-CAT-07).
- **Count:** `COUNT(*)` over the same filtered set.
- **Cache:** facets are cached for a short time per (context, normalised filter hash). PROPOSED TTL 60 s, invalidated by campaign events.

### 3.4 Search (FR-SRC-01, FR-SRC-02)
- **Suggestions:** brand-name prefix on `brands (name)`, limit 10. Only brands with at least one live article are kept, through a semi-join on live campaign articles. That condition is PROPOSED: suggesting brands with nothing on sale isn't observed.
- **Results:** `MATCH(search_text) AGAINST (:q IN BOOLEAN MODE)`, joined to live articles.
- **FULLTEXT settings:** InnoDB `innodb_ft_min_token_size` (default 3) and the stop-word list must be reviewed for French (ADR-008).

### 3.5 Reservation (NFR-CON-01)
- **Reserve:** a conditional update on `inventory_items` by PK, with the guard `quantity_available >= :qty`. There's no lock wait beyond the row lock, and no read-modify-write race.
- **Cart read:** `cart_reservations (user_id, expires_at > now)`.
- **Sweep:** `expires_at <= now`, ordered by `expires_at`, with `LIMIT 500` per transaction.

## 4. Deliberately not indexed
- `campaigns.published_at` alone: it's always combined with the time range, and it has low selectivity.
- `products.name` B-tree: search uses FULLTEXT, and sorting by name isn't a requirement.
- `users.role`: admin lookups are rare.

## 5. Caching (complements the indexes)
| What | Key | Invalidation | Class |
|---|---|---|---|
| Landing grid and upcoming list | landing slug | `CampaignStarted`, `CampaignEnded` and campaign-edit events. The TTL is capped at the next start or end boundary, so a missed event can't serve stale state | PROPOSED |
| Campaign headline (`MAX(discount_percent)`) | campaign ID | changes to its articles | PROPOSED |
| Facets | context + filter hash | TTL 60 s | PROPOSED |
| Availability, reservations, cart | — | **never cached** (NFR-SEC-03, NFR-CON-01) | CONFIRMED (requirement) |

The cache store is the database driver as a baseline, with Redis optional (ADR-007, Q-72).

## 6. Verification before release
1. Seed realistic data: ≥ 150 live campaigns, one campaign with ≥ 2,000 articles, and about 10 sizes per article.
2. Run `EXPLAIN ANALYZE` for every query in §3, with each sort and with 0, 1 and 3 filters.
3. Load-test concurrent add-to-cart on one inventory row and check that no oversell occurs (NFR-CON-01).
4. Record the results against the performance targets once the PO sets them (Q-42).
