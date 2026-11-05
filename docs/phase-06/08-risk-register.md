# Risk and dependency register — Phase 06

**Scales:** probability and impact are qualitative (High, Medium, Low), assessed from the documentation. No quantitative data exists.

**Owner roles:**
- Product Owner, Sponsor, Architect, Tech Lead
- Backend Lead, Frontend Lead, QA Lead, DevOps
- Scrum Master, Legal / DPO

**Focus areas named in the brief:**

| Focus area | Risks |
|---|---|
| Cart reservation expiry lifecycle | R-01, R-02, R-05, R-06 |
| Atomic stock reservation and release | R-03 |
| Concurrent reservation scenarios | R-03, R-04, R-05 |
| Root category slug uniqueness in MySQL | R-07 |
| Campaign/product/variant consistency | R-08, R-09 |
| Unverified MySQL behaviours | R-10 |
| Data administration (back-office, imports, seeders) | R-11, R-12 |
| Checkout boundary | R-13, R-14 |
| Unresolved business rules | R-15, R-16, R-19, R-20, R-21, R-22, R-23, R-26, R-29 |

**Key dependencies:**
- Q-70 → US-012 / US-015 / US-036 (schema)
- US-061 spike → every migration
- US-058 MySQL CI → US-053 concurrency tests
- the `scheduler` service → US-040 and the campaign boundary events
- Q-80 → US-041 and US-065


| Risk ID | Description | Probability | Impact | Mitigation | Related epic | Related stories | Owner role |
|---|---|---|---|---|---|---|---|
| R-01 | Reservation expiry lifecycle undecided. FR-CRT-08 is a HYPOTHESIS; if the PO turns `release_expired_reservations` off, a reserved unit is never returned to other members (C-08). | High | High | Decide Q-37 before the MVP release; build the sweep behind its switch (US-040); expired rows are never shown in the cart and never counted against the limit, even before the sweep. | E10, E09 | US-040, US-037 | Product Owner |
| R-02 | Stock leak when a member re-adds the same size after expiry but before the sweep. | Medium | High | AddToCart releases the stale row in the same transaction (ADR-010); dedicated time-travel test in US-053. | E10 | US-037, US-053 | Backend Lead |
| R-03 | Overselling under concurrent reservations if the reserve isn't a single conditional atomic update, or if a read-then-write slips in. | Medium | High | Only ReserveUnit changes `quantity_reserved` (ADR-005); CHECK invariant; parallel-connection test on MySQL; code review checklist item. | E09 | US-036, US-053 | Backend Lead |
| R-04 | Concurrency tests give false confidence if run on SQLite or a single connection. | High | High | CI uses a MySQL 8 service; the harness opens parallel connections (US-058, US-053). | E13, E14 | US-053, US-058 | QA Lead |
| R-05 | The expiry sweep races with remove, re-add or a future checkout on the same row, releasing a unit twice. | Medium | High | ReleaseUnit decrements only when it deletes the reservation row in the same transaction; batches lock rows; race tests in US-040. | E10 | US-040 | Backend Lead |
| R-06 | The scheduler isn't running, so expired reservations are never released and boundary events are missed. | Medium | High | Scheduler as its own service with a health check; boundary events use a watermark so missed minutes catch up; alert on a stale watermark. | E14, E10 | US-057, US-015, US-040 | DevOps |
| R-07 | Phase 05 erratum: `categories` has UQ (parent_id, slug) with a nullable `parent_id`. MySQL unique indexes treat NULLs as distinct, so two root categories can share a slug. Seeders or imports keyed by slug would then break. | High | Medium | Fix in US-012 with a method chosen under Q-84 (e.g. a generated non-null parent key in the unique index, or a service-level check). schema.md is left unchanged until the architect approves the erratum. | E03 | US-012 | Architect |
| R-08 | Product granularity (Q-70) unresolved: model vs model + colour changes `products`, `campaign_articles` and `inventory_items`; late answers mean migration rework across three modules. | High | High | Q-70 is a Sprint 0 gate; US-012, US-015 and US-036 don't freeze migrations before it. | E03, E05, E09 | US-012, US-015, US-036 | Product Owner |
| R-09 | Campaign/product/variant inconsistency: an inventory item whose variant and campaign article belong to different products, or a variant from another product in an add request. | Medium | High | Composite FKs through `product_id` (verified in the US-061 spike); `AddCartItemRequest` checks the variant belongs to the SKU's product; factories build consistent graphs. | E05, E09, E03 | US-061, US-015, US-036, US-032 | Backend Lead |
| R-10 | Unverified MySQL behaviours: CHECK constraints combined with FK referential actions, unsigned subtraction in generated columns (`quantity_available`, `discount_percent`), and composite FKs. Any of them may fail at migration time or raise a different error than designed. | Medium | Medium | US-061 spike in Sprint 0; errata approved before US-012 and US-015 freeze migrations. | E14, E03 | US-061 | Tech Lead |
| R-11 | Data administration gap: no back-office, import format or admin UI is designed (C-16, Q-50), so real catalogue and campaign data can't be loaded. | High | High | Synthetic seeders for build and demo (US-064); production data entry is a release gate (US-065) that needs Q-50. | E16 | US-064, US-065 | Product Owner |
| R-12 | Seed data or fixtures copy reference-platform content (brand names, images, texts, IDs), breaking the independence constraint. | Low | High | Seed review in the DoD; generated names and placeholder images only. | E16 | US-064 | Tech Lead |
| R-13 | Checkout boundary: the MVP can reserve but can't sell. Stakeholders may expect orders, and reserved stock is never converted to sold. | High | High | Q-80 decides the MVP purpose and the "Commander" target; M21 needs its own specification (Q-04) before any public launch that takes orders. | E10 | US-041 | Product Owner |
| R-14 | Scope creep into checkout: order tables, payment endpoints or fee charging added "temporarily". | Medium | Medium | DoD item and review check: no checkout, payment or order code; boundary contracts stay outlines (api-contracts §10). | E10 | US-041, US-038 | Scrum Master |
| R-15 | Policy settings unset: production fails closed (503 `settings_incomplete`) while any of the 11 PO-decided values is missing (`listing_page_size` is CONFIRMED). | High | Medium | Settings checklist with Q-IDs is a release gate; development placeholders are labelled and never reach production. | E14 | US-059 | Product Owner |
| R-16 | Display timezone undecided (NFR-TIME-01): start and end times shown an hour off for some members. | Medium | Medium | Store UTC; one formatter takes `display_timezone`; Q-10 before release (US-047). | E11 | US-047, US-046 | Product Owner |
| R-17 | Stale landing caches keep showing ended campaigns or miss started ones. | Medium | Medium | State is derived per request; caches are invalidated by boundary events with a watermark; time-travel tests. | E05 | US-015, US-016 | Backend Lead |
| R-18 | Client clocks are wrong, so countdowns mislead. | Low | Low | `useServerNow` from `meta.server_time` (ADR-009). | E11 | US-046 | Frontend Lead |
| R-19 | Login methods undecided (Q-40): adding social login later changes registration, password reset and account linking. | Medium | Medium | Build e-mail/password only after the PO confirms it; keep the Identity actions separate per method. | E01 | US-001, US-002, US-003 | Product Owner |
| R-20 | Catalogue access mode undecided (Q-71): route guards, caching rules and SEO differ between `members` and `public`. | Medium | Medium | Both modes built behind one setting (US-005); caching allowed only in `public` mode. | E01 | US-005 | Product Owner |
| R-21 | Recommendation logic UNKNOWN (Q-29): personal rails stay empty and hidden, so AC-FR-REC-01-1 can't pass. | High | Low | Provider interface with empty v1 bindings; rails hidden when empty; accept against the PROPOSED popular strategy only. | E15 | US-062, US-063 | Product Owner |
| R-22 | Legal content missing (Q-48, Q-31): terms, entity, mediator and one return period are needed before any public release. | High | High | Legal review as a release gate; reference texts never copied; the period comes from one setting. | E12 | US-050 | Legal / DPO |
| R-23 | Performance targets unknown (Q-42): FULLTEXT search and facet counts are untested at real volume. | Medium | Medium | Indexing verification plan with EXPLAIN on seed data at volume; load test once targets exist (US-056). | E13, E08, E06 | US-056, US-025, US-035 | Tech Lead |
| R-24 | Team capacity and sprint length unknown (Q-81): the sprint plan is a sequence, not a commitment. | High | Medium | Re-plan after Sprint 0 with measured throughput; keep the dependency order. | All | — | Scrum Master |
| R-25 | Accessibility regressions in custom dialogs (filters, cart drawer, remove confirmation, mega-menu). | Medium | Medium | Shared dialog primitives; keyboard E2E of J1 and automated scans in CI (US-054). | E13, E11 | US-054 | Frontend Lead |
| R-26 | Consent solution undecided (Q-86): third-party scripts loaded before consent would breach NFR-PRV-01. | Medium | High | No third-party script until the consent gate exists; CSP restricts origins. | E12 | US-052 | Legal / DPO |
| R-27 | Bulk reservation abuse at launch hoards limited stock (NFR-SEC-05 is a HYPOTHESIS). | Medium | Medium | Placeholder rate limit on add-to-cart; bot defence decided under Q-42. | E10 | US-042 | Tech Lead |
| R-28 | `products.search_text` drifts from brand or product data if the rebuild listener fails. | Low | Medium | Queued, retryable listener; failed-jobs monitoring; a rebuild command. | E08 | US-035 | Backend Lead |
| R-29 | HYPOTHESIS behaviours shipped as if confirmed. | Medium | Medium | Each sits behind one switch or its own story with Status Needs Clarification; DoD forbids enabling one without a recorded PO decision. | All | US-019, US-020, US-021, US-028, US-033, US-040, US-042 | Product Owner |
