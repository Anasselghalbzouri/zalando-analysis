# MySQL schema — Phase 05

**Status:** PROPOSED design for MySQL 8. Entities and sources are in [erd.md](erd.md); index rationale is in [indexing-strategy.md](indexing-strategy.md). This is a specification in tables: Laravel migrations will be written in the implementation phase, and **no DDL is part of this document**.

## 1. Conventions
| Topic | Convention | Class |
|---|---|---|
| Engine, charset | InnoDB; `utf8mb4` with `utf8mb4_0900_ai_ci`, which is accent- and case-insensitive. So "ete" matches "été" in search and sort | PROPOSED |
| Table names | snake_case plural (`campaign_articles`); pivots are named after both sides (`landing_campaigns`) | PROPOSED |
| Primary keys | `id BIGINT UNSIGNED AUTO_INCREMENT`; pivots use a composite PK | PROPOSED |
| Foreign keys | `<singular>_id`, always declared. `ON DELETE RESTRICT` unless stated; `CASCADE` only for rows owned by their parent (images, variants, preferences, reservations of a deleted user) | PROPOSED |
| Time | All temporal columns are `DATETIME` **in UTC**: the application and connection timezone are UTC. `created_at`/`updated_at` on every mutable table (ADR-009) | PROPOSED |
| Money | `INT UNSIGNED` cents, currency EUR implied, VAT included (BR-12-02, ADR-009) | PROPOSED |
| Booleans | `TINYINT(1) NOT NULL` with an explicit default | PROPOSED |
| Enums | MySQL `ENUM` only for small, code-owned sets (role, landing type, headline mode, page type, gender). Anything editable is a lookup table | PROPOSED |
| Soft deletes | **None in v1.** The only justification found, keeping catalogue rows for order history, belongs to the out-of-scope checkout spec. Account deletion policy is UNKNOWN (Q-73). Integrity relies on `RESTRICT` foreign keys plus campaign unpublication | PROPOSED |
| CHECK constraints | Used for local invariants. MySQL enforces them from 8.0.16; verify the server version. MySQL restricts CHECK constraints on columns that are also used by foreign-key referential actions. Wherever a CHECK below touches an FK column (`unsubscribe_reason_id`, `help_category_id`), the rule is **also** enforced in the Form Request or service, so it holds even if the DB constraint has to be dropped | PROPOSED |

Column notation: **PK** primary key, **FK** foreign key, **UQ** unique, **GEN** generated (stored) column, **NN** not null.

## 2. Identity and account

### `users`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| first_name | VARCHAR(100) | NN | — | Account greeting (FR-ACC-02). Other profile fields: UNKNOWN (Q-41) |
| email | VARCHAR(255) | NN | — | UQ `users_email_unique` (BR-15-02, one account per customer) |
| email_verified_at | DATETIME | NULL | NULL | PROPOSED security baseline |
| password | VARCHAR(255) | NN | — | Hash only (bcrypt/argon2id, framework default) |
| adult_confirmed_at | DATETIME | NN | — | 18+ confirmation (BR-15-02). The birth date isn't stored (data minimisation, PROPOSED) |
| terms_accepted_at | DATETIME | NN | — | Acceptance of the terms at registration (PROPOSED) |
| terms_version | VARCHAR(20) | NN | — | Version of `content_pages` (terms) that was accepted |
| role | ENUM('member','merchandiser','admin') | NN | 'member' | Authorization (authentication-design §5). Non-member roles are HYPOTHESIS (Q-50) |
| remember_token | VARCHAR(100) | NULL | NULL | Framework "remember me" |
| created_at, updated_at | DATETIME | NULL | NULL | |

### `newsletter_preferences`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| user_id | BIGINT UNSIGNED | NN | — | PK, FK → users.id CASCADE |
| daily_sales | TINYINT(1) | NN | 0 | "Ventes du jour" |
| weekly_sales | TINYINT(1) | NN | 0 | "Ventes de la semaine" |
| personalisation_opt_in | TINYINT(1) | NN | 0 | "Personnalisez vos newsletters". Opt-in, default off (NFR-PRV-01) |
| unsubscribed_at | DATETIME | NULL | NULL | |
| unsubscribe_reason_id | BIGINT UNSIGNED | NULL | NULL | FK → unsubscribe_reasons.id. CHECK: not null when `unsubscribed_at` is not null (BR-15-04) |
| updated_at | DATETIME | NULL | NULL | |

### `unsubscribe_reasons`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| label | VARCHAR(150) | NN | — | Values UNKNOWN (PO content) |
| position | SMALLINT UNSIGNED | NN | 0 | |
| is_active | TINYINT(1) | NN | 1 | Retired reasons stay for history |

### `garment_groups`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| code | VARCHAR(40) | NN | — | UQ |
| name | VARCHAR(80) | NN | — | e.g. "Chaussures", "Hauts / Tops" (observed) |
| position | SMALLINT UNSIGNED | NN | 0 | |

### `saved_size_profiles`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| user_id | BIGINT UNSIGNED | NN | — | FK → users.id CASCADE |
| gender | ENUM('women','men') | NN | — | Tabs Femmes / Hommes (observed) |
| garment_group_id | BIGINT UNSIGNED | NN | — | FK → garment_groups.id |
| size_system_id | BIGINT UNSIGNED | NN | — | FK → size_systems.id (EU/US…) |
| created_at, updated_at | DATETIME | NULL | NULL | |
| | | | | UQ (user_id, gender, garment_group_id) |

### `saved_size_values`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| saved_size_profile_id | BIGINT UNSIGNED | NN | — | PK part, FK → saved_size_profiles.id CASCADE |
| size_value_id | BIGINT UNSIGNED | NN | — | PK part, FK → size_values.id RESTRICT. Must belong to the profile's size system (enforced in `SaveSizeProfilesRequest`) |

## 3. Catalogue

### `brands`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| code | VARCHAR(16) | NN | — | UQ. Public, used in `/brands/{code}` (FR-BRD-01) |
| name | VARCHAR(150) | NN | — | Display name: breadcrumb and suggestions (FR-BRD-02, FR-SRC-01). Index `brands_name_index` for prefix suggestions |
| slug | VARCHAR(160) | NN | — | UQ |
| created_at, updated_at | DATETIME | NULL | NULL | |

### `categories`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK. Public, used in `/articles/categories/{id}` |
| parent_id | BIGINT UNSIGNED | NULL | NULL | FK → categories.id RESTRICT |
| name | VARCHAR(120) | NN | — | |
| slug | VARCHAR(140) | NN | — | UQ (parent_id, slug) |
| path | VARCHAR(255) | NN | — | Materialised ancestor path, e.g. `/2/15/87/`. Index for subtree filters. Maintained by the Catalog service |
| position | SMALLINT UNSIGNED | NN | 0 | Menu column and item order |
| created_at, updated_at | DATETIME | NULL | NULL | |

### `size_systems`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| code | VARCHAR(30) | NN | — | UQ |
| name | VARCHAR(60) | NN | — | Tab label, e.g. "Chaussures", "EU", "US" |

### `size_values`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| size_system_id | BIGINT UNSIGNED | NN | — | FK → size_systems.id CASCADE |
| label | VARCHAR(20) | NN | — | e.g. "39 1/3", "M". UQ (size_system_id, label) |
| sort_order | SMALLINT UNSIGNED | NN | 0 | Order on the PDP, in filter tabs and in saved sizes |

One list of valid sizes per system. It's shared by product variants (FR-PDP-08), the size filter (FR-CAT-06) and saved sizes (FR-ACC-07), so a size label is stored once.

### `products`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| brand_id | BIGINT UNSIGNED | NN | — | FK → brands.id |
| category_id | BIGINT UNSIGNED | NN | — | FK → categories.id |
| size_system_id | BIGINT UNSIGNED | NN | — | FK → size_systems.id |
| model_code | VARCHAR(32) | NN | — | Not unique (one model, several colours, Q-70). Indexed |
| name | VARCHAR(200) | NN | — | Type line, e.g. "Baskets basses". FULLTEXT with `search_text` |
| search_text | VARCHAR(500) | NN | '' | Brand name + name + colour value, maintained by the Catalog service for FULLTEXT (ADR-008). A justified denormalisation: FULLTEXT can't span joined tables |
| details | TEXT | NULL | NULL | "Détails" accordion |
| size_and_fit | TEXT | NULL | NULL | "Taille & Coupe" |
| composition | TEXT | NULL | NULL | "Composition" accordion (display). The filter uses attributes |
| manufacturing_info | TEXT | NULL | NULL | Manufacturing information |
| delivery_days_min | TINYINT UNSIGNED | NULL | NULL | Per-product lead time (BR-17-02). The computation is UNKNOWN (Q-49) |
| delivery_days_max | TINYINT UNSIGNED | NULL | NULL | CHECK ≥ delivery_days_min |
| created_at, updated_at | DATETIME | NULL | NULL | |

### `product_images`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| product_id | BIGINT UNSIGNED | NN | — | FK → products.id CASCADE |
| path | VARCHAR(255) | NN | — | Storage path (object storage or `storage/app/public`) |
| alt_text | VARCHAR(255) | NN | — | Required (NFR-A11Y-01) |
| position | TINYINT UNSIGNED | NN | — | UQ (product_id, position). Positions 1–2 are the tile images (FR-CAT-02) |

### `product_variants`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| product_id | BIGINT UNSIGNED | NN | — | FK → products.id CASCADE |
| size_value_id | BIGINT UNSIGNED | NN | — | FK → size_values.id RESTRICT. UQ (product_id, size_value_id). Must belong to the product's size system (enforced in the Catalog service; a DB check would need a trigger) |
| | | | | UQ (id, product_id), the target of the composite FK from `inventory_items` |

### `attributes`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| code | VARCHAR(40) | NN | — | UQ: `couleur`, `composition`, `longueur`… |
| name | VARCHAR(80) | NN | — | Filter label |
| is_filterable | TINYINT(1) | NN | 1 | |
| position | SMALLINT UNSIGNED | NN | 0 | Filter order |

### `attribute_values`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| attribute_id | BIGINT UNSIGNED | NN | — | FK → attributes.id CASCADE |
| value | VARCHAR(100) | NN | — | Display value |
| slug | VARCHAR(110) | NN | — | UQ (attribute_id, slug). Used in filter URLs |
| position | SMALLINT UNSIGNED | NN | 0 | |

### `product_attribute_values`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| product_id | BIGINT UNSIGNED | NN | — | PK part, FK → products.id CASCADE |
| attribute_value_id | BIGINT UNSIGNED | NN | — | PK part, FK → attribute_values.id CASCADE. Secondary index (attribute_value_id, product_id) |

## 4. Sales

### `landings`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK. Public numeric ID in `/event/{id}` (FR-EVT-01) |
| slug | VARCHAR(80) | NN | — | UQ. Human-readable key for seeding and the back-office. The API uses the numeric `id` or `default` |
| title | VARCHAR(120) | NN | — | Nav label and H2 |
| type | ENUM('aggregate','vertical','curated') | NN | — | §erd 2.3 |
| default_flag | TINYINT UNSIGNED | NULL | NULL | 1 for the default landing, NULL otherwise. UQ, so there's at most one default |
| nav_position | SMALLINT UNSIGNED | NULL | NULL | NULL = not in the main nav (FR-NAV-03) |
| menu_category_id | BIGINT UNSIGNED | NULL | NULL | FK → categories.id. NULL = no mega-menu (FR-NAV-04) |
| hero_image_path | VARCHAR(255) | NULL | NULL | Vertical landings only (FR-EVT-04) |
| hero_image_alt | VARCHAR(255) | NULL | NULL | CHECK: not null when the image is set |
| hero_title | VARCHAR(150) | NULL | NULL | |
| hero_campaign_id | BIGINT UNSIGNED | NULL | NULL | FK → campaigns.id SET NULL. Hero target: HYPOTHESIS (BR-11-02, Q-13) |
| created_at, updated_at | DATETIME | NULL | NULL | |

### `campaigns`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK. Public, used in `/campaigns/{id}` |
| name | VARCHAR(200) | NN | — | Free text: brand, theme or price-point campaigns (FR-CMP-10) |
| slug | VARCHAR(220) | NN | — | Not unique (names recur), cosmetic only |
| image_path | VARCHAR(255) | NN | — | Tile image |
| image_alt | VARCHAR(255) | NN | — | NFR-A11Y-01 |
| starts_at | DATETIME | NN | — | UTC |
| ends_at | DATETIME | NN | — | UTC. CHECK `ends_at > starts_at` |
| headline_mode | ENUM('computed','manual','special') | NN | — | **No default**: the merchandiser chooses (Q-23, Q-24) |
| headline_percent | TINYINT UNSIGNED | NULL | NULL | CHECK: set if and only if mode = 'manual'; 1–99 |
| published_at | DATETIME | NULL | NULL | NULL = draft. State is derived (erd §2.3) |
| created_at, updated_at | DATETIME | NULL | NULL | |

### `landing_campaigns`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| landing_id | BIGINT UNSIGNED | NN | — | PK part, FK → landings.id CASCADE |
| campaign_id | BIGINT UNSIGNED | NN | — | PK part, FK → campaigns.id CASCADE. Secondary index (campaign_id) |
| position | INT UNSIGNED | NULL | NULL | Merchandising order (FR-EVT-03, PROPOSED). NULL = fall back to `starts_at DESC` |

For `aggregate` landings, rows are optional: they hold only an ordering override. Membership is implied by "all live campaigns".

### `campaign_articles`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| campaign_id | BIGINT UNSIGNED | NN | — | FK → campaigns.id RESTRICT |
| product_id | BIGINT UNSIGNED | NN | — | FK → products.id RESTRICT. UQ (campaign_id, product_id) |
| sku | VARCHAR(32) | NN | — | UQ. In the URL `/campaigns/{campaignId}/articles/{sku}` (FR-CMP-06) |
| rrp_cents | INT UNSIGNED | NN | — | CHECK > 0. RRP, VAT included |
| sale_price_cents | INT UNSIGNED | NN | — | CHECK > 0 and ≤ rrp_cents |
| discount_percent | TINYINT UNSIGNED | — | GEN | `FLOOR((rrp_cents − sale_price_cents) × 100 / rrp_cents)`, STORED. The rounding rule is UNKNOWN (Q-77, PROPOSED floor). Stored so "Réduction en %" sorting can use an index |
| merch_position | INT UNSIGNED | NULL | NULL | Placeholder for the "Préférés" sort (Q-43) |
| created_at, updated_at | DATETIME | NULL | NULL | `created_at` backs the "Le plus récent" sort (PROPOSED) |
| | | | | UQ (id, product_id), the target of the composite FK from `inventory_items` |

## 5. Inventory and cart

### `inventory_items`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| campaign_article_id | BIGINT UNSIGNED | NN | — | Composite FK (campaign_article_id, product_id) → campaign_articles(id, product_id) RESTRICT |
| product_variant_id | BIGINT UNSIGNED | NN | — | Composite FK (product_variant_id, product_id) → product_variants(id, product_id) RESTRICT |
| product_id | BIGINT UNSIGNED | NN | — | Integrity column only: it forces the variant and the article to share one product |
| quantity_allocated | INT UNSIGNED | NN | — | Limited stock (FR-STK-02) |
| quantity_reserved | INT UNSIGNED | NN | 0 | Changed only by conditional atomic updates (ADR-005) |
| quantity_sold | INT UNSIGNED | NN | 0 | **Written by checkout (OUT OF SCOPE).** Nothing in scope writes it |
| quantity_available | INT | — | GEN | `quantity_allocated − quantity_reserved − quantity_sold`, STORED |
| updated_at | DATETIME | NULL | NULL | |
| | | | | UQ (campaign_article_id, product_variant_id); CHECK `quantity_reserved + quantity_sold ≤ quantity_allocated` |

### `cart_reservations`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| user_id | BIGINT UNSIGNED | NN | — | FK → users.id CASCADE. The service releases stock before deleting a user |
| inventory_item_id | BIGINT UNSIGNED | NN | — | FK → inventory_items.id RESTRICT. UQ (user_id, inventory_item_id) |
| quantity | TINYINT UNSIGNED | NN | 1 | CHECK ≥ 1. Max = `max_quantity_per_article` (Q-46) |
| reserved_at | DATETIME | NN | — | |
| expires_at | DATETIME | NN | — | `reserved_at + reservation_minutes` (Q-25, Q-32). Index (expires_at) for the sweep; (user_id, expires_at) for cart reads |

### `article_reservation_stats` (PROPOSED, FR-REC-02 strategy)
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| campaign_article_id | BIGINT UNSIGNED | NN | — | PK part, FK → campaign_articles.id CASCADE |
| bucket_hour | DATETIME | NN | — | PK part, truncated to the hour (UTC) |
| reservations | INT UNSIGNED | NN | 0 | |

## 6. Content

### `help_categories`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| slug | VARCHAR(100) | NN | — | UQ |
| title | VARCHAR(150) | NN | — | |
| position | SMALLINT UNSIGNED | NN | 0 | |

### `content_pages`
| Column | Type | Null | Default | Constraint / notes |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NN | auto | PK |
| type | ENUM('help','legal') | NN | — | |
| help_category_id | BIGINT UNSIGNED | NULL | NULL | FK → help_categories.id RESTRICT. CHECK: not null if and only if type = 'help' |
| path | VARCHAR(255) | NN | — | UQ, e.g. `help/<category>/<slug>` or `terms` |
| title | VARCHAR(200) | NN | — | |
| body | MEDIUMTEXT | NN | — | Sanitised HTML (an allow-list sanitiser on write) |
| version | VARCHAR(20) | NULL | NULL | Terms version (FR-CNT-03) |
| published_at | DATETIME | NULL | NULL | NULL = draft |
| created_at, updated_at | DATETIME | NULL | NULL | |

## 7. Framework tables (Laravel standard)
These are created by the framework's standard migrations. They're listed so the whole schema is visible:
- `password_reset_tokens`
- `sessions`, when the database session driver is used (the baseline; Redis optional, ADR-007)
- `cache` and `cache_locks`
- `jobs`, `job_batches` and `failed_jobs`

**No `personal_access_tokens`:** SPA cookie authentication issues no API tokens (ADR-003).

## 8. Key design questions from the brief

### 8.1 Campaign pricing
- **Where prices live:** prices live on **`campaign_articles`**, not on `products`. The same product can carry different RRP and sale prices in different campaigns (FR-CMP-07, ADR-004). The displayed discount stays stable for the duration of the campaign.
- **Discount:** `discount_percent` is a stored generated column, so it is never maintained by hand.
- **Headline:**
  - `computed` = `MAX(discount_percent)` over the campaign's articles (BR-12-05)
  - `manual` = `headline_percent`
  - `special` = the label
  - Which mode the PO wants is UNKNOWN (Q-23).
- **Price per size:** not supported. No observation shows sizes priced differently.

### 8.2 Variants and stock
- **Variant:** a **variant** is product × size (`product_variants`).
- **Stock:** the **stock** row is campaign article × variant (`inventory_items`). The same variant sold in two campaigns has two independent stock pools (PROPOSED, Q-70, ADR-005).
- **Availability:** a generated column. Reservations change `quantity_reserved` only through a conditional update, so concurrent adds can't oversell (NFR-CON-01).

### 8.3 Campaign lifecycle
- **No status column:** the state is derived from `published_at`, `starts_at`, `ends_at` and the current UTC time. There's one shared "live" predicate in the Sales module (erd §2.3, ADR-009).
- **Scheduler:** it emits `CampaignStarted` / `CampaignEnded` for cache invalidation. Missing a run never leaves a wrong state.

## 9. Normalisation decisions
| Decision | Normal form / rationale |
|---|---|
| Catalogue (product), sale (article) and stock (inventory) are separate tables | 3NF: price depends on (campaign, product), stock on (article, size). Each fact is stored once |
| Colour, composition and length as attribute values | Removes repeating facet columns and supports context-dependent filters (BR-06-08) |
| Saved sizes split into profile + values | Removes the repeating group of sizes, and puts the one-system-per-group rule on the profile key |
| Size labels in `size_values` | One list of valid sizes per system, referenced by variants and saved sizes. There are no free-text size labels to keep in sync |
| No `carts` table | A cart has no attributes of its own. It's the set of a user's active reservations |
| No stored campaign status | Avoids a derived value that can drift from the clock |
| **Justified denormalisations** | `discount_percent` and `quantity_available` (generated, so they can't drift); `categories.path` (subtree queries); `products.search_text` (FULLTEXT can't span joins; rebuilt by the service on change); `inventory_items.product_id` (integrity, via the composite FKs) |
| No `customer_profiles`, `addresses` or `wishlists` | Not justified by confirmed scope (system-overview §8.1, C-01, C-02) |
