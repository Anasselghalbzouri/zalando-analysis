# Open questions — Phase 02, updated in Phase 04

These are questions the observation couldn't answer. Each one blocks or weakens a requirement. **Owner** says who can resolve it:
- **User**: an authorization decision.
- **PO**: a product decision for the new application.
- **Analysis**: a further read-only capture that's already in scope.

## A. Authorization (status after the user's answers on 2026-11-04)

- **Q-01 partly granted:** cart and account pages read-only, plus one add-to-cart with removal. Done; see M07, M13, M14, M15. Still **not** authorized: "Informez moi", newsletter changes and "Activez Plus".
- **Q-02 granted:** done; see M12, M17, M18, M21.
- **Q-03:** still open.
- **Q-04:** still open.

Original questions:
| ID | Question | Why it matters | Blocks |
|---|---|---|---|
| Q-01 | May the analysis open the cart and account pages, click "Ajouter au panier", "Informez moi", the newsletter link, or "Activez Plus"? Each one changes the account's state, and add-to-cart may reserve real stock that other shoppers then see as "Réservé". | Without this, M04, M07 (purchase), M13 (reservation), M14, M15 and M16 internals stay OUT OF SCOPE. | M04, M07, M13, M14, M15, M16 |
| Q-02 | May the public help, CGV and size-guide pages (footer links on the same domain) be read? | They likely state the fee amount (CGV §2), return rules and delivery costs, which would turn several HYPOTHESIS items into CONFIRMED rules. | M12, M17, M18 |
| Q-03 | May a logged-out (guest) session be observed, e.g. a separate browser profile you control? | The guest role and the login wall are entirely UNKNOWN. | all modules, §2 roles |
| Q-04 | Checkout and payment (M21) are excluded. Confirm the new application's checkout will be specified from scratch, not reverse-engineered. | Avoids an implicit requirement gap. | M21 |

## B. Read-only analysis still pending (in scope, not yet done)
| ID | Question | Planned method |
|---|---|---|
| Q-10 | Is "06:00" (next-day start time) in Paris time or UTC? **Likely** browser timezone (UTC under REA) = 07:00 Paris, matching the help text (M03 BR-03-17, HYPOTHESIS). Still to verify in a tab loaded without REA. | Open `/event/58` in a fresh tab with no REA scenario, then run a passive `inspect_web_page`. |
| Q-11 | How does a listing go beyond 60 of N products: infinite scroll or a control? | Screenshot and accessibility capture at the bottom of `/campaigns/{id}` after a real wheel scroll. |
| Q-12 | Does the campaign page show its countdown anywhere (sticky header, banner)? | Screenshot of `/campaigns/{id}`. |
| Q-13 | Where does the hero "Découvrir" go? | Click it (navigation only). |
| Q-14 | What do the Marque, Couleur, Composition and catégories filter dialogs contain? Is filter state reflected in the URL? | Open dialogs. Apply one filter and read the URL. |
| Q-15 | What does the mobile/responsive layout look like? | You enable the DevTools device toolbar on the tab, then I run a passive capture. |
| Q-16 | Does Premium (`/3849`) really have no footer or upcoming section? | A longer wait plus a wheel scroll. |
| Q-17 | What does an ended campaign URL return? | Re-open `/campaigns/ZZO4RMA` (it ends ~23:00 UTC on 2026-11-04) after that time. It's a known linked URL, so no guessing is involved. |

## C. Product decisions for the new application (PO)
| ID | Question | Observed reference behaviour |
|---|---|---|
| Q-20 | Campaign duration and start/end slots: fixed daily slots or free datetime? | Two daily end slots observed (HYPOTHESIS BR-03-08). A 3-day cohort was seen. |
| Q-21 | Can a product belong to several concurrent campaigns, at different prices? | The same model was seen in 2 campaigns as different SKUs (BR-03-14). |
| Q-22 | Can a campaign appear on several landing pages? | Yes, up to 4 (BR-03-13). |
| Q-23 | How is the "jusqu'à N%" headline computed: the maximum over SKUs, or entered manually? | UNKNOWN. |
| Q-24 | When is "Special" used instead of a percentage? | 5 of 126, on price-point or selection sales (BR-03-05). |
| Q-25 | ~~Cart reservation: is there one, and how long does it last?~~ **Resolved (reference):** yes, 20 minutes (BR-14-01, BR-14-02). The PO still decides the new app's duration. | "Réservé" label and a 20:00 countdown observed. |
| Q-26 | Low-stock threshold ("Plus que N disponibles")? **Partly answered:** N is live (3 → 1, BR-07-02). The threshold is still UNKNOWN. | N = 3 and N = 1 seen. |
| Q-27 | Should the upcoming list be global or per landing? | Global in the reference (BR-05-04). |
| Q-28 | What should the guest experience be? | UNKNOWN in the reference. |
| Q-29 | Which recommendation rails are required (personal, regional)? | Three rail types observed (M10). |
| Q-30 | ~~What is the packaging/handling fee model?~~ **Resolved (reference):** 3,90 € per order whatever the number of items (BR-12-06). Applying it is checkout (OUT OF SCOPE). The PO decides the new app's fee. | BR-12-03, BR-12-06. |
| Q-31 | Which return period applies: 30 days (help), 31 days (PDP) or the 14-day legal withdrawal (CGV)? | Three different values are published (BR-17-04). |
| Q-32 | Cart reservation timer: is it per item or shared, and does it extend when more items are added? | 20 min for one item (BR-14-02). |
| Q-33 | Should the new app replicate launch slots (07:00 weekdays / 08:00 weekends, plus an evening slot)? | BR-03-16, BR-03-18. |

## E. Raised in Phase 04 (requirements engineering)
Each question blocks or weakens the requirement(s) named. **PO** = product decision for the new app. **Analysis** = a further read-only capture in scope.

| ID | Question | Blocks | Owner | Reference behaviour |
|---|---|---|---|---|
| Q-35 | When does an upcoming tile switch from "Démarre dans …" to "Démarre le …"? | FR-UPC-02 | PO (Analysis can narrow it) | Both formats seen; threshold between 1.4 h and 13 h (BR-03-12). |
| Q-36 | What triggers the "Dernières heures" badge, and at what threshold? | FR-STK-03 | PO | Badge seen on search tiles (BR-13-03, HYPOTHESIS). |
| Q-37 | What happens visibly when a reservation expires: notification, cart state, PDP state? | FR-CRT-08 | PO | Not observed (BR-14-06, HYPOTHESIS). |
| Q-38 | What are the return and withdrawal flow steps after "commencez un retour"? | FR-ACC-04 | PO / Analysis (needs an order, out of scope) | Entry pages only (BR-15-06). |
| Q-39 | Do saved size filters pre-filter listings? | FR-ACC-07 | PO | Saved-size UI exists (BR-15-05). |
| Q-40 | Which login methods does the new app offer (email/password, social login)? The reference uses the shared Zalando identity, which doesn't apply to an independent app. | FR-ACC-08 | PO | Zalando credentials and Facebook/Gmail/Apple login (BR-15-01). |
| Q-41 | What do registration, personal-details and address forms contain? | FR-ACC-01, FR-ACC-08 | PO | Not opened (PII). |
| Q-42 | What are the performance and launch-time load targets? Is bot protection needed? | NFR-PERF-01, NFR-SEC-05 | PO | One HAR sample (≈ 3 s server wait) is not representative (network-analysis N-05). |
| Q-43 | What does the default sort "Préférés" rank by? | FR-CAT-03 | PO | UNKNOWN. |
| Q-44 | Free-text search: Enter behaviour, suggestion types beyond brands, no-results state, minimum length? | FR-SRC-01, FR-SRC-02 | PO / Analysis | Brand suggestions only seen (BR-08-01). |
| Q-45 | When does a listing tile show "Réservé" (all units reserved, some, the member's own)? | FR-STK-01 | PO | Seen on a brand tile (`a11y/brand_H1X`). |
| Q-46 | Maximum quantity per article per member, and what replaces "Ajouter au panier" after adding? | FR-PDP-04 | PO | Button disappears after adding (BR-07-08). |
| Q-47 | "Informez moi": channel (email or push), data stored, confirmation, unsubscribe? | FR-UPC-04 | User (authorization) / PO | Button only (M04). |
| Q-48 | Legal entity, contract language and consumer mediator of the new app? | FR-CNT-03 | PO / Legal | Zalando SE, French only, FEVAD (BR-18-03, BR-18-04). |
| Q-49 | How is the delivery window computed (per-product lead time, carrier, working days)? | FR-PDP-06, FR-DLV-01 | PO | 4-day window starting 2 days ahead, once (BR-07-04, BR-17-08). |
| Q-50 | Back-office: who creates events, campaigns, assortments, headlines, ordering, heroes and stock allocations, and how? | FR-EVT-03, FR-CMP-05, FR-CMP-09 | PO | Never observable (dependencies.md BO1–BO5). |
| Q-51 | Success copy after adding to the cart? | FR-PDP-03 | PO | "Le produit a été mis à jour avec succès." (M07). |

## F. Raised in Phase 03 (HAR) — transport details not observed
These matter only if the new app wants to mirror the reference API behaviour. The new REST API is designed independently, so they don't block the SRS.

| ID | Question | Planned method |
|---|---|---|
| Q-60 | How are pagination, filters and sort transported (query string, GraphQL variables, path)? | HAR of a campaign listing with one filter and one sort applied (network-analysis N-06). |
| Q-61 | What are the cart mutation endpoints and the non-empty cart schema? | HAR of add → open cart → remove (authorized in Phase 02). |
| Q-62 | What does the search API look like? | HAR of a header search. |
| Q-63 | What are the login flow, identity provider and session lifetime? | User-recorded logout → login HAR, sanitized before sharing. Session lifetime: PO decision. |
| Q-64 | What do API error envelopes look like? | Not planned: forcing errors is out of scope. |

**Correction recorded in Phase 03:** the reference page actively unregisters its service worker (frontend-analysis F-08), so the Phase 01 M20 note about a registered service worker doesn't describe an active feature.

## G. Raised in Phase 05 (system design)
These were raised while designing [the architecture](../architecture/system-overview.md). Each one names the design seam that waits for it. The C-IDs refer to the contradictions register in system-overview §10.

| ID | Question | Blocks / design seam | Owner |
|---|---|---|---|
| Q-70 | Product granularity: is a catalogue product a *model* or a *model + colour*? Can the *same* sellable item run in two campaigns at once, at different prices and with separate stock? | `products`, `campaign_articles`, `inventory_items` (C-06, C-07, ADR-004, ADR-005) | PO |
| Q-71 | Catalogue access for visitors who aren't logged in: members only (as the help text suggests) or public browsing with login at reservation time? | `catalog_access` setting, route middleware (C-05) | PO |
| Q-72 | Hosting target and expected launch traffic: is Redis (sessions, cache, queue, locks) needed from day one? | Docker services, cache and queue drivers (C-12, ADR-007) | PO / Ops |
| Q-73 | Account deletion and data retention: hard delete, anonymisation or soft delete? What retention applies? | `users` (no soft delete in v1), GDPR erasure job | PO / Legal |
| Q-74 | How far ahead does "Prochainement" list upcoming campaigns? | `upcoming_horizon_hours` setting (FR-UPC-01) | PO |
| Q-75 | Product name, brand identity and final front-end route names. Reference branding must not be reused | UI copy and route table (C-17) | PO |
| Q-76 | Is a wishlist or favourites feature wanted? None was observed on the reference | Excluded entity (C-01) | PO |
| Q-77 | Discount percentage rounding: floor, round half up, or merchandiser-entered? | `discount_percent` generated column, headline computation (C-15) | PO |
| Q-78 | "Popular" rail: what defines popularity, and does "dans votre région" need a region (from the address, IP or none)? | ArticlePopularity strategy (C-10) | PO |

## H. Raised in Phase 06 (planning)
These were raised while building [the Phase 06 backlog](../phase-06/09-open-questions.md), which lists the affected user stories for every question.

| ID | Question | Blocks | Owner |
|---|---|---|---|
| Q-80 | With checkout OUT OF SCOPE, is the MVP an internal demo, a closed pilot or a public launch, and what does "Commander" do in it? | US-041, US-065 (MVP release gates) | Sponsor / PO |
| Q-81 | Team size, skills, sprint length and availability? | Sprint dates and capacity (06-sprint-planning) | Sponsor / Scrum Master |
| Q-82 | Git host, CI/CD platform and deployment environments? | US-058 | Tech Lead / DevOps |
| Q-83 | The unsubscribe reason options? | US-009 (FR-ACC-06) | PO |
| Q-84 | How to enforce root-category slug uniqueness, given MySQL treats NULL parents as distinct in UQ (parent_id, slug)? A Phase 05 schema erratum | US-012 | Architect |
| Q-85 | Who writes and supplies MVP content (help, USP claims, footnotes, images), and in what format? | US-043, US-049 | PO / Content |
| Q-86 | Which cookie-consent solution and which consent purposes? | US-052 (FR-CNT-04) | PO / DPO |

## D. Known defects observed (don't replicate)
- German ARIA labels in the French search (M08).
- "Maks" instead of "Max" in the price filter (M06).
- Brand breadcrumb shows the internal code "H1X" (M09).
- "Plus que 1 disponibles" (plural with 1) on the PDP (M07).
- Footer cites "§ 2. des CGV" for fees, but the fee clause is §3 (M12).
- Typo in the tracking parameter `highligts_carousel` (M20). It's internal, but don't copy it.
