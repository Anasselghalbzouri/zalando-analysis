# Open questions register — Phase 06

This register lists every decision the backlog depends on.
- **IDs:** existing IDs are reused from [open-questions.md](../requirements/open-questions.md), with no renumbering. New Phase 06 questions start at **Q-80** and are also added there as section H, so there is still one register.
- **UNKNOWN stays UNKNOWN:** no UNKNOWN decision is converted into a confirmed requirement. Each one blocks the stories listed.
- **Affected stories:** computed by the generator from each story's blocking questions and text.

**Not listed, because they don't block implementation:**
- Q-02: answered.
- Q-22: the design allows a campaign on several landings.
- Q-60–Q-62 and Q-64: Phase 03 transport details. The new REST API is designed independently.


| ID | Question | Why it matters | Impact on implementation | Affected user stories | Responsible stakeholder | Status |
|---|---|---|---|---|---|---|
| Q-01 | May the analysis click "Informez moi", change newsletter settings or "Activez Plus" on the reference? | The notify-me behaviour can't be observed otherwise. | FR-UPC-04 stays undesigned. | US-022 | User (authorization) | Partly granted; these actions still not authorized |
| Q-03 | May a logged-out (guest) session be observed? | The guest experience is UNKNOWN. | Catalogue access mode can't be grounded in evidence. | US-005 | User (authorization) | Open |
| Q-04 | Confirm checkout will be specified from scratch as a separate specification. | Without it, "Commander" has no target and nothing can be sold. | US-041 blocked; no orders, returns or fee charging. | US-011, US-041 | Product Owner | Open |
| Q-10 | Is the reference's next-day start time shown in Paris time or the browser's timezone, and which timezone will the new app display? | Every start and end time on screen depends on it. | `display_timezone` unset: production fails closed. | US-047 | Product Owner (Analysis can narrow it) | Open |
| Q-11 | How does a listing go beyond the first 60 products: a control or infinite scroll? | Defines the listing UI pattern. | API is page-based; UI pagination pattern can't be accepted. | US-023 | Product Owner / Analysis | Open |
| Q-12 | Does the campaign page show its countdown anywhere? | Decides the campaign listing header. | Placement of the countdown on `/campaigns/{id}`. | US-023 | Analysis | Open |
| Q-13 | Where does the landing hero link to? | The hero target is a HYPOTHESIS. | Hero rendered without a decided link target. | US-016 | Analysis / Product Owner | Open |
| Q-14 | What do the Marque, Couleur, Composition and catégories filter dialogs contain, and is filter state in the URL on the reference? | Filter dialog content. | Facets return what the data holds; dialog details unaccepted. | US-025 | Analysis / Product Owner | Open |
| Q-15 | What are the responsive breakpoints and mobile behaviour? | NFR-RESP-01 is UNKNOWN. | Mobile layouts can't be built or accepted. | US-048 | Product Owner / Analysis | Open |
| Q-16 | Does the curated (Premium-type) landing really hide the upcoming section and footer? | FR-EVT-05 is a HYPOTHESIS. | Curated template unvalidated. | US-020 | Analysis | Open |
| Q-17 | What does an ended campaign URL return? | The ended-campaign behaviour is PROPOSED (410). | Live-window guard and ended reads unvalidated. | US-021, US-023 | Analysis / Product Owner | Open |
| Q-20 | Campaign duration and start/end slots: fixed daily slots or free datetimes? | Scheduling rules. | Any datetime accepted until slots are configured. | US-015 | Product Owner | Open |
| Q-21 | Can a product belong to several concurrent campaigns at different prices? | Shapes campaign-article rules. | Allowed by the schema; validation of the guard. | US-021 | Product Owner | Open |
| Q-23 | How is the "jusqu'à N%" headline computed: maximum over articles or entered manually? | Headline correctness. | `headline_mode` has no default; tile acceptance waits. | US-017 | Product Owner | Open |
| Q-24 | When is "Special" used instead of a percentage? | Headline rule. | Merchandiser input only; acceptance waits. | US-017 | Product Owner | Open |
| Q-25 | How long does a reservation last in the new app? | The timer is the core cart rule. | `reservation_minutes` unset: production fails closed. | US-037 | Product Owner | Reference answered (20 min); PO value open |
| Q-26 | What is the low-stock threshold for "Plus que N disponibles"? | When the hint shows. | `low_stock_threshold` unset. | US-031 | Product Owner | Partly answered (N is live); threshold open |
| Q-27 | Should the upcoming list be global or per landing? | Upcoming section content. | Built global (reference behaviour); PO to confirm. | US-018 | Product Owner | Open |
| Q-28 | What should the guest experience be? | Access model. | With Q-71, decides `catalog_access`. | US-005 | Product Owner | Open |
| Q-29 | Which recommendation rails and logic are required? | Rails can't be filled otherwise. | Personal rails empty and hidden in v1. | US-062, US-063 | Product Owner | Open |
| Q-30 | What packaging and handling fee does the new app charge? | Display text only in scope; charging is checkout. | `packaging_fee_cents` unset. | US-038 | Product Owner | Reference answered (3,90 €); PO value open |
| Q-31 | Which single return period applies (30, 31 or 14 days on the reference)? | The PDP, help and terms must agree. | `return_period_days` unset; legal pages blocked. | US-030, US-050 | Product Owner / Legal | Open |
| Q-32 | Is the reservation timer per item or shared, and does it extend when items are added? | Cart timer semantics. | `reservation_timer_mode` unset. | US-037, US-040 | Product Owner | Open |
| Q-33 | Should the new app use fixed launch slots? | Scheduling. | With Q-20. | US-015 | Product Owner | Open |
| Q-35 | When does an upcoming tile switch from "Démarre dans …" to "Démarre le …"? | Copy rule. | `upcoming_relative_threshold_minutes` unset. | US-018 | Product Owner | Open |
| Q-36 | What triggers "Dernières heures", and at what threshold? | FR-STK-03 is a HYPOTHESIS. | Badge stays off. | US-028 | Product Owner | Open |
| Q-37 | What happens visibly when a reservation expires? | FR-CRT-08 is a HYPOTHESIS but expiry is MVP-critical (C-08). | Sweep switch decision; no expiry notification built. | US-040 | Product Owner | Open |
| Q-38 | What are the return and withdrawal flow steps? | Owned by checkout data. | Only entry pages built. | US-011 | Product Owner | Open |
| Q-39 | Do saved sizes pre-filter listings? | Possible listing behaviour. | Not built. | US-010 | Product Owner | Open |
| Q-40 | Which login methods does the new app offer? | Registration and recovery depend on it. | E-mail/password stories wait for confirmation; social login not built. | US-001, US-002, US-003, US-004 | Product Owner | Open |
| Q-41 | What do registration and personal-details forms contain? | Form fields and data stored. | Only first name, e-mail and password built. | US-001, US-007 | Product Owner / DPO | Open |
| Q-42 | What are the performance and launch-load targets, and is bot protection needed? | NFR-PERF-01 is UNKNOWN; NFR-SEC-05 is a HYPOTHESIS. | No load test acceptance; placeholder rate limits only. | US-042, US-056 | Product Owner | Open |
| Q-43 | What does the default sort "Préférés" rank by? | Default listing order. | Placeholder: merchandising position, then newest. | US-024 | Product Owner | Open |
| Q-44 | Search: Enter behaviour, other suggestion types, no-results state, minimum length? | Search UX. | Brand suggestions only; acceptance of the rest waits. | US-034, US-035 | Product Owner / Analysis | Open |
| Q-45 | When does a tile show "Réservé"? | Tile state rule. | PROPOSED rule: nothing available but something reserved. | US-027 | Product Owner | Open |
| Q-46 | Maximum quantity per article per member, and what replaces "Ajouter au panier" after adding? | Cart limit and PDP button. | `max_quantity_per_article` has no default. | US-033, US-037 | Product Owner | Open |
| Q-47 | "Informez moi": channel, stored data, confirmation, unsubscribe? | FR-UPC-04 not designed. | US-022 has no tasks. | US-022 | User (authorization) / Product Owner | Open |
| Q-48 | Legal entity, contract language and consumer mediator of the new app? | Terms page content. | Legal pages can't be accepted. | US-050 | Product Owner / Legal | Open |
| Q-49 | How is the delivery window computed? | PDP delivery window and help content. | Window null when no lead time; computation seam only. | US-030, US-049 | Product Owner | Open |
| Q-50 | Back-office: who creates landings, campaigns, assortments, headlines and stock allocations, and how? | No real data can be loaded without it. | Production data entry blocked (US-065). | US-015, US-019, US-065 | Product Owner | Open |
| Q-51 | What success copy is shown after adding to the cart? | Copy. | Placeholder copy until decided. | US-037 | Product Owner | Open |
| Q-63 | What is the session lifetime? | Session settings. | Lifetime from config; value unset. | US-002 | Product Owner | Open |
| Q-70 | Is a catalogue product a model or a model + colour, and can the same sellable item run in two campaigns at once with separate stock? | Shapes three modules' tables. | US-012, US-015 and US-036 can't freeze migrations. | US-012, US-015, US-036 | Product Owner | Open |
| Q-71 | Catalogue access for visitors who aren't logged in: members only or public browsing? | Route guards and caching. | `catalog_access` unset. | US-005 | Product Owner | Open |
| Q-72 | Hosting target and launch traffic: is Redis needed from day one? | Driver choice. | Database drivers until decided. | US-056 | Product Owner / DevOps | Open |
| Q-73 | Account deletion and data retention rules? | GDPR erasure. | No deletion story; flagged for a later backlog item. | no story (not designed) | Product Owner / Legal | Open |
| Q-74 | How far ahead does "Prochainement" list upcoming campaigns? | Upcoming content. | `upcoming_horizon_hours` unset. | US-018 | Product Owner | Open |
| Q-75 | Product name, brand identity and final route names? | Every page shows them; reference branding can't be used. | Shell built with neutral placeholders. | US-043 | Product Owner | Open |
| Q-76 | Is a wishlist wanted? None was observed. | Possible new module. | No story; out of the backlog until decided. | no story (not designed) | Product Owner | Open |
| Q-77 | Discount rounding: floor, round half up, or entered by hand? | Displayed percentages. | PROPOSED floor in the generated column. | US-015, US-017 | Product Owner | Open |
| Q-78 | What defines "popular", and does it need a region? | Popular rail. | PROPOSED reservation-count strategy, no region. | US-063 | Product Owner | Open |
| Q-80 | With checkout OUT OF SCOPE, is the MVP an internal demo, a closed pilot or a public launch, and what does "Commander" do in it? | Decides whether reserved items can ever be bought, and which release gates apply. | US-041 blocked; US-065 needed only for a launch with real data. | US-041, US-065 | Sponsor / Product Owner | Open (new, Phase 06) |
| Q-81 | What are the team's size, skills, sprint length and availability? | The sprint plan is only a sequence without them. | No dates or story points can be given. | no story (not designed) | Sponsor / Scrum Master | Open (new, Phase 06) |
| Q-82 | Which Git host, CI/CD platform and deployment environments will be used? | CI and environments are Sprint 0 work. | US-058 can't be finished. | US-056, US-058 | Tech Lead / DevOps | Open (new, Phase 06) |
| Q-83 | What are the unsubscribe reason options? | FR-ACC-06 needs a list; values are UNKNOWN. | Seeded placeholders only. | US-009 | Product Owner | Open (new, Phase 06) |
| Q-84 | How should root-category slug uniqueness be enforced, given MySQL treats NULL parents as distinct in UQ (parent_id, slug)? | Phase 05 schema erratum (R-07). | US-012 migration for `categories`. | US-012 | Architect | Open (new, Phase 06) |
| Q-85 | Who writes and supplies MVP content (help articles, USP claims, footnotes, campaign and product images), and in what format? | Pages can't be accepted without content. | Placeholder content only. | US-043, US-049 | Product Owner / Content | Open (new, Phase 06) |
| Q-86 | Which cookie-consent solution and which consent purposes? | FR-CNT-04 entry point exists; the tool is UNKNOWN. | US-052 can't be accepted. | US-052 | Product Owner / DPO | Open (new, Phase 06) |
