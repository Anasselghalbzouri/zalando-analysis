# Business rules (observed) — Phase 02, indexed in Phase 04

These rules were derived only from observation of the authorized pages on 2026-11-04. Each rule carries its classification. The module file linked in each row holds the evidence pointer.

- **CONFIRMED** rules can be used as requirements for the new Laravel/React application.
- **HYPOTHESIS** rules need product-owner validation before they're implemented. They aren't requirements yet.
- Rules not listed here are **unknown**. Don't invent them. See [open-questions.md](open-questions.md).

## Phase 04 topic index
The rules below are the single catalogue of business rules. Phase 04 adds no new rule; it groups the existing IDs by business topic and links them to requirements in [SRS.md](SRS.md). IDs are never renumbered.

**Status for the new application:**
- **Requirement** means the rule backs a requirement as-is.
- **PO** means a policy value the PO must set.
- **Validate** means a HYPOTHESIS rule that needs confirmation.
- **Reference only** means the rule is OUT OF SCOPE or applies to the reference only.

| Topic | Rules | Requirements | Status for the new app |
|---|---|---|---|
| Campaign lifecycle | BR-03-01–BR-03-18, BR-04-01–BR-04-06, BR-05-08, BR-05-09 | FR-CMP-01–10, FR-UPC-01–04, FR-EVT-02, FR-EVT-03 | Requirement: BR-03-01–04, 09, 13, 14, BR-05-08, and BR-03-16 as a rule (scheduled launches; limited stock, not restocked). PO: the slot and duration values in BR-03-10, 16, 18 (Q-20, Q-33). Validate: BR-03-05 (trigger), 08, 11 (enforcement), 15, 17, BR-05-09. UNKNOWN: BR-03-12 threshold (Q-35) |
| Event landings | BR-05-01–BR-05-07, BR-11-01–BR-11-03 | FR-EVT-01–05, FR-CMP-10 | Requirement: BR-05-01, 03, 04, 06, BR-11-01, 03. Validate: BR-05-02 (alias), 05, BR-11-02 |
| Product catalogue | BR-03-01, BR-03-14, BR-06-07, BR-07-01, BR-07-05, BR-07-06 | FR-CAT-01, FR-CAT-02, FR-CAT-09, FR-PDP-01, FR-PDP-02, FR-PDP-08 | Requirement, except BR-07-05 (SKU format semantics) which is reference only |
| Brand management | BR-09-01, BR-09-02, BR-08-01 | FR-BRD-01, FR-BRD-02, FR-SRC-01 | Requirement (BR-09-02 as defect avoidance). Brand administration was never observed (Q-50) |
| Product pricing | BR-01-01, BR-03-04, BR-12-01–BR-12-07, BR-14-04, BR-14-05 | FR-PRC-01, FR-PRC-02, FR-CMP-03, FR-CRT-05, FR-NAV-02 | Requirement: BR-12-01, 02, 05, BR-14-04, 05. PO: BR-12-03, 06 (fee amount; charging it is checkout). Validate: BR-12-04 (rounding). Reference only: BR-12-07 (order-time price, checkout) |
| Stock indicators | BR-07-02, BR-13-01–BR-13-04, BR-07-08 | FR-PDP-05, FR-STK-01–03, FR-PDP-04, NFR-CON-01 | Requirement: BR-07-02, BR-13-01, 02, 04. PO: threshold (Q-26). Validate: BR-13-03, BR-07-08 |
| Search & filtering | BR-06-01–BR-06-08, BR-08-01–BR-08-03 | FR-CAT-03–08, FR-SRC-01, FR-SRC-02 | Requirement: BR-06-01–04, 06–08, BR-08-01, 02. Validate: BR-06-05, BR-08-03 |
| Cart (authorized scope) | BR-07-07, BR-14-01–BR-14-06 | FR-CRT-01–08, FR-PDP-03, FR-NAV-05 | Requirement: BR-07-07, BR-14-01–05. Validate: BR-14-06 (expiry). PO: multi-item timer (Q-32) |
| Customer account (authorized scope) | BR-15-01–BR-15-07, BR-16-01, BR-16-02 | FR-ACC-01–08, FR-OOS-02 | Requirement: BR-15-02–07. PO: BR-15-01 (login methods, Q-40). Reference only: BR-16-* (Plus) |
| Recommendations | BR-05-06, BR-05-07, BR-10-01–BR-10-03 | FR-REC-01, FR-REC-02 | Requirement (display): BR-05-06, BR-10-01, 02. Validate: BR-05-07 (ranking), BR-10-03. Algorithms UNKNOWN (Q-29) |
| Delivery & returns information | BR-07-03, BR-07-04, BR-15-06, BR-17-01–BR-17-08 | FR-PDP-06, FR-PDP-07, FR-DLV-01, FR-DLV-02, FR-ACC-04 | Requirement: BR-15-06, BR-17-01, 02. PO: BR-07-03 / BR-17-04 (period, Q-31), BR-17-03, 05–08 (carriers, area, options) |
| Navigation, footer, legal | BR-01-02, BR-02-01–BR-02-03, BR-18-01–BR-18-04, BR-20-01, BR-20-02, BR-22-01 | FR-NAV-01, FR-NAV-03, FR-NAV-04, FR-CNT-01–05, FR-ANL-01, NFR-SEC-05 | Requirement: BR-01-02, BR-02-01, 02, BR-18-01, 02, BR-20-01. PO: BR-18-03, 04 (Q-48). Validate: BR-02-03, BR-20-02 |
| Checkout & payment | BR-21-01–BR-21-06 | FR-OOS-01 | **Reference only (OUT OF SCOPE, M21).** Not requirements |

## Rule catalogue (by module)

| ID | Module | Class | Rule |
|---|---|---|---|
| BR-01-01 | [M01](../modules/M01.md) | CONFIRMED | the USP claim "Jusqu’à -75 %*" uses the same RRP footnote (*) as the campaign headlines (M12). |
| BR-01-02 | [M01](../modules/M01.md) | CONFIRMED | the header is identical across page types. The main nav (M02) is not: it appears on event pages only. |
| BR-02-01 | [M02](../modules/M02.md) | CONFIRMED | only event pages show the main nav. Campaign, PDP, brand and search pages don't. |
| BR-02-02 | [M02](../modules/M02.md) | CONFIRMED | Premium is a nav item without a mega-menu. |
| BR-02-03 | [M02](../modules/M02.md) | HYPOTHESIS | the Sports panel is empty because a curated event has no taxonomy. |
| BR-03-01 | [M03](../modules/M03.md) | CONFIRMED | Every product is addressed inside a campaign: `/campaigns/{campaignId}/articles/{sku}` |
| BR-03-02 | [M03](../modules/M03.md) | CONFIRMED | A live campaign shows an end countdown, "Se termine dans" |
| BR-03-03 | [M03](../modules/M03.md) | CONFIRMED (format) | The countdown shows days + hours + minutes when ≥ 1 day remains, and hours + minutes + seconds when < 1 day remains |
| BR-03-04 | [M03](../modules/M03.md) | CONFIRMED | The headline is "Économisez jusqu'à* N%", where * = "Par rapport au prix de vente conseillé" (vs RRP) |
| BR-03-05 | [M03](../modules/M03.md) | CONFIRMED (label) / HYPOTHESIS (rule: used when no single max-discount figure applies) | Some campaigns show "Special" instead of a percentage. These are price-point or selection campaigns, e.g. "Looks enfant à moins de 40 €", "Mode premium pour enfant à moins de 60 €", "BOSS Kidswear", "Diadora" |
| BR-03-06 | [M03](../modules/M03.md) | CONFIRMED (snapshot) | Observed max-discount range is 50–85%, with 75% the most common (57 of 126 on `/event` at ~17:09 UTC) |
| BR-03-07 | [M03](../modules/M03.md) | CONFIRMED (clustering) | End times cluster: all 126 countdowns in one capture fall into **two offsets mod 24 h** (≈ +5 h 50 and ≈ +23 h 49 at 17:09 UTC), plus whole days |
| BR-03-08 | [M03](../modules/M03.md) | HYPOTHESIS (clock arithmetic; the analysis clock matches REA's own capture timestamps, e.g. `2026-11-04T17:11:04Z` in `ev_bda5136d…`) | Campaigns end at a small number of fixed daily times. Based on the analysis machine clock (17:09 UTC), the two end times are ≈ 23:00 UTC (00:00 Paris) and ≈ 17:00 UTC (18:00 Paris). Arithmetic: 17:09 + 5:50 = 22:59, and 17:09 + 23:49 = 16:58 the next day |
| BR-03-09 | [M03](../modules/M03.md) | CONFIRMED (matched **by name only**, because upcoming tiles carry no ID) | A campaign moves from "Prochainement" (upcoming) to live at a scheduled time. The five sales listed as "Démarre dans 1 heure 21 minutes" in Phase 01 were all live in Phase 02, each with ≈ 2 j 23 h left |
| BR-03-10 | [M03](../modules/M03.md) | CONFIRMED (help text: 3–5 days) / HYPOTHESIS (same start/end slot) | Those newly started campaigns run ≈ 3 days, starting and ending at the same daily slot. The help page says 3–5 days on average |
| BR-03-11 | [M03](../modules/M03.md) | CONFIRMED (UI) / HYPOTHESIS (rule is enforced server-side) | Upcoming campaigns can't be browsed before they start: there is no link and no ID in the tile |
| BR-03-12 | [M03](../modules/M03.md) | CONFIRMED (both formats seen) / UNKNOWN (threshold) | Close to the start, the upcoming tile shows a relative countdown ("Démarre dans …", seen at 1 h 21 before start). Further out, it shows an absolute day and time ("Démarre le jeu. 5 novembre 06:00", seen at ≈ 13–14 h before start). The switch is somewhere between 1.4 h and 13 h, or the rule is "same day vs a later day" |
| BR-03-13 | [M03](../modules/M03.md) | CONFIRMED | One campaign can appear on several event landings at the same time (68 of 123 vertical-listed campaigns appear on 2–4 landings). Every vertical-listed campaign is also on `/event`, and 3 campaigns appear only on `/event` |
| BR-03-14 | [M03](../modules/M03.md) | CONFIRMED (one case) | The same model can be sold in more than one campaign (model `NI112P025` appears under `ZZO4RN5` and `ZZO4RMX` as different SKUs) |
| BR-03-15 | [M03](../modules/M03.md) | HYPOTHESIS | Price and availability depend on the campaign, and a product can't be bought after its campaign ends |
| BR-03-16 | [M03](../modules/M03.md) | CONFIRMED (published text) | Official rule (help page): "Chaque jour, une dizaine de nouvelles ventes sont mises en ligne, à 7 heures en semaine et à 8 heures le week-end". Sales run "3 à 5 jours en moyenne", quantities are limited, and ended or sold-out items aren't reordered |
| BR-03-17 | [M03](../modules/M03.md) | HYPOTHESIS (inference; it assumes the published 07:00 slot applies. Verify with a passive inspect of a tab loaded without REA) | The displayed start time "jeu. 5 novembre 06:00" (Thursday, a weekday) matches the published 07:00 Paris launch only if it was rendered in UTC. HYPOTHESIS: REA's UTC emulation affected the rendered times, and the site renders times in the browser's timezone |
| BR-03-18 | [M03](../modules/M03.md) | CONFIRMED (measured) / HYPOTHESIS (a regular evening slot) | Besides the morning launch, a cohort of 5 sales went live at **17:00 UTC (18:00 Paris)** on 2026-11-04. Measured: the Phase 01 Sports capture was taken at 15:38:53 UTC (REA result timestamp), and adding "Démarre dans 1 heure 21 minutes 6 secondes" gives 16:59:59 UTC. So launches also happen outside the published 07:00/08:00 slots |
| BR-04-01 | [M04](../modules/M04.md) | CONFIRMED | the upcoming list is identical across event landings, i.e. global (see BR-05-04). |
| BR-04-02 | [M04](../modules/M04.md) | CONFIRMED | tiles carry no campaign link or ID (BR-03-11). |
| BR-04-03 | [M04](../modules/M04.md) | CONFIRMED | start times use relative and absolute formats (BR-03-12). |
| BR-04-04 | [M04](../modules/M04.md) | CONFIRMED | all 33 next-day entries share one start time, "jeu. 5 novembre 06:00". The timezone is UNKNOWN (see M03 §12). |
| BR-04-05 | [M04](../modules/M04.md) | CONFIRMED (help page) | about ten new sales go live every day, at 07:00 on weekdays and 08:00 at weekends. |
| BR-04-06 | [M04](../modules/M04.md) | CONFIRMED | the newsletter offers daily and/or weekly sales emails (M15). It's the documented way to stay informed of upcoming sales. |
| BR-05-01 | [M05](../modules/M05.md) | CONFIRMED | Event IDs are numeric. The main nav exposes 1, 2, 3, 4, 58, 3774, 3849 |
| BR-05-02 | [M05](../modules/M05.md) | CONFIRMED (at capture time) / HYPOTHESIS (`/1` is an alias of the default event) | `/event` and `/event/1` show the same campaign set (126 = 126, zero difference) and the same personalised rails |
| BR-05-03 | [M05](../modules/M05.md) | CONFIRMED | A campaign can be listed in several events (68 of 123 on 2–4 verticals). Every vertical campaign is also on "all sales" |
| BR-05-04 | [M05](../modules/M05.md) | CONFIRMED (identical lists at ~17:06–17:10 UTC) | The "Prochainement" list is **global**: the same 33 entries, in the same order, appear on `/event`, `/1`, `/2`, `/3`, `/4` and `/58`, including sales unrelated to the vertical (e.g. adidas and kids' sales on Habitat) |
| BR-05-05 | [M05](../modules/M05.md) | CONFIRMED (absence in capture) / HYPOTHESIS (a different template for seasonal or curated events) | Premium (`/3849`) shows no "Prochainement" and no footer, even after scrolling |
| BR-05-06 | [M05](../modules/M05.md) | CONFIRMED (absent on the 6 verticals) | Personalised rails ("Recommandé pour vous", "Continuez votre shopping") appear only on `/event` and `/event/1` |
| BR-05-07 | [M05](../modules/M05.md) | CONFIRMED (comparisons) / HYPOTHESIS (regional bestseller feed with an audience filter and live ranking) | "Populaire dans votre région" (12 items) appears on every event. Item sets compared at ~17:05–17:10 UTC: `/event` = Femme, and Enfant = Premium. Habitat holds the same items as Enfant in a different order. Homme has a distinct, menswear-led set (e.g. "Tommy Hilfiger - Boxers"). The list is therefore neither fully global nor fully per-event, and its order changes within minutes |
| BR-05-08 | [M05](../modules/M05.md) | CONFIRMED | The grid shows only live campaigns. Upcoming ones are shown only in "Prochainement" |
| BR-05-09 | [M05](../modules/M05.md) | CONFIRMED (not sorted by name or countdown) / HYPOTHESIS (ranking logic) | Grid order isn't alphabetical or by end time. HYPOTHESIS: curated or ranked |
| BR-06-01 | [M06](../modules/M06.md) | CONFIRMED | Default sort is "Préférés" |
| BR-06-02 | [M06](../modules/M06.md) | CONFIRMED | Sort options: newest, preferred, price ascending, price descending, discount % |
| BR-06-03 | [M06](../modules/M06.md) | CONFIRMED (UI) / HYPOTHESIS (the count updates live as options change) | Filters are confirmed explicitly with a button that shows the result count ("Afficher 1 726 résultats"), not applied instantly |
| BR-06-04 | [M06](../modules/M06.md) | CONFIRMED (one tab seen) / HYPOTHESIS (clothing contexts show more tabs) | Size values are grouped by size system in tabs (e.g. "Chaussures") |
| BR-06-05 | [M06](../modules/M06.md) | HYPOTHESIS (8/299 observed) | The price filter range is bounded by the min/max price in the context |
| BR-06-06 | [M06](../modules/M06.md) | CONFIRMED | "Retirer" (clear this filter) is disabled when nothing is selected |
| BR-06-07 | [M06](../modules/M06.md) | CONFIRMED | A listing shows 60 products initially |
| BR-06-08 | [M06](../modules/M06.md) | CONFIRMED | Category listings add a "Longueur" (length) filter, so filters depend on context |
| BR-07-01 | [M07](../modules/M07.md) | CONFIRMED | the PDP repeats the campaign countdown (BR-03-02). |
| BR-07-02 | [M07](../modules/M07.md) | CONFIRMED | per-size low-stock label "Plus que N disponibles". N fell from 3 (Phase 01) to 1 (Phase 02) on the same size, so the value is live. The threshold is UNKNOWN. Grammar defect: "1 disponibles". |
| BR-07-03 | [M07](../modules/M07.md) | CONFIRMED | the PDP shows free returns within 31 days of delivery. This conflicts with the help page (30 days) and the CGV (14-day legal withdrawal); see BR-17-04. |
| BR-07-04 | [M07](../modules/M07.md) | CONFIRMED | a delivery date window is shown (4 days wide, starting 2 days after the visit). |
| BR-07-05 | [M07](../modules/M07.md) | HYPOTHESIS | the SKU prefix is the brand code for some articles, and `ZZO` is a platform number range. |
| BR-07-06 | [M07](../modules/M07.md) | CONFIRMED | size selection is required before adding to the cart (alert "Choisissez une taille"). |
| BR-07-07 | [M07](../modules/M07.md) | CONFIRMED | adding to the cart starts a **20-minute reservation** (BR-14-02). |
| BR-07-08 | [M07](../modules/M07.md) | CONFIRMED | after adding, the PDP hides "Ajouter au panier". HYPOTHESIS: one unit per SKU per member, or the button is replaced while the reservation is active. |
| BR-08-01 | [M08](../modules/M08.md) | CONFIRMED | the suggestions observed were brands only. |
| BR-08-02 | [M08](../modules/M08.md) | CONFIRMED | results span campaigns (products from several campaign IDs). |
| BR-08-03 | [M08](../modules/M08.md) | HYPOTHESIS | `searchAnonId` is an anonymous search-session ID for analytics. |
| BR-09-01 | [M09](../modules/M09.md) | CONFIRMED | brand listings aggregate products from several campaigns. |
| BR-09-02 | [M09](../modules/M09.md) | CONFIRMED (defect) | the breadcrumb shows the internal code instead of the brand name. |
| BR-10-01 | [M10](../modules/M10.md) | CONFIRMED | personal rails appear only on the default event. |
| BR-10-02 | [M10](../modules/M10.md) | CONFIRMED | "Populaire" varies by event and over time (BR-05-07). |
| BR-10-03 | [M10](../modules/M10.md) | HYPOTHESIS | "Continuez" is driven by browsing history. |
| BR-11-01 | [M11](../modules/M11.md) | CONFIRMED | one hero per vertical event, and none on `/event` or `/event/1`. |
| BR-11-02 | [M11](../modules/M11.md) | HYPOTHESIS | a hero links to a featured campaign. |
| BR-11-03 | [M11](../modules/M11.md) | CONFIRMED | thematic campaigns (price-point, occasion, size range, gift ideas) sit alongside brand campaigns. |
| BR-12-01 | [M12](../modules/M12.md) | CONFIRMED | discounts are expressed vs the RRP. |
| BR-12-02 | [M12](../modules/M12.md) | CONFIRMED | prices include VAT (CGV §3.1 "prix définitifs, toutes taxes comprises"). |
| BR-12-03 | [M12](../modules/M12.md) | CONFIRMED | a packaging and handling fee is charged per order (CGV §3.2). It's shown on the order page and may differ for bulky, heavy or valuable items (CGV §3.3). |
| BR-12-04 | [M12](../modules/M12.md) | CONFIRMED (observation) | sale prices end in ,90 / ,00 / ,50. A rounding rule is HYPOTHESIS. |
| BR-12-05 | [M12](../modules/M12.md) | CONFIRMED | the campaign headline is the maximum discount ("jusqu'à"). |
| BR-12-06 | [M12](../modules/M12.md) | CONFIRMED (help page) | the fee is **3,90 € per order, whatever the number of items**. |
| BR-12-07 | [M12](../modules/M12.md) | CONFIRMED (CGV §3.1) | the price charged is the one shown when the order is placed. |
| BR-13-01 | [M13](../modules/M13.md) | CONFIRMED | low stock is shown per size, and N changes over time (3 → 1). |
| BR-13-02 | [M13](../modules/M13.md) | CONFIRMED (upgraded from HYPOTHESIS) | cart items are *reserved* stock. |
| BR-13-03 | [M13](../modules/M13.md) | HYPOTHESIS | "Dernières heures" means the campaign ends within some threshold. |
| BR-13-04 | [M13](../modules/M13.md) | CONFIRMED (help page) | quantities are limited, and ended or sold-out items aren't reordered ("nous ne pouvons les recommander"). |
| BR-14-01 | [M14](../modules/M14.md) | CONFIRMED | the cart is a reservation. Items are listed as "Réservé". |
| BR-14-02 | [M14](../modules/M14.md) | CONFIRMED | the reservation lasts **20 minutes** from adding ("20 minutes et 0 secondes restantes" right after adding, then 16:59 and 14:07 on later reads). The countdown is shown in the header and the cart title. |
| BR-14-03 | [M14](../modules/M14.md) | CONFIRMED | removing needs explicit confirmation and releases the unit to other shoppers. |
| BR-14-04 | [M14](../modules/M14.md) | CONFIRMED | the cart shows the total saving vs RRP and the per-line discount %. Here -54% is 115 vs 250 (exactly 54%). |
| BR-14-05 | [M14](../modules/M14.md) | CONFIRMED | the cart total excludes the packaging/handling fee, which is added at checkout (3,90 € per order per the help page, BR-12-06). |
| BR-14-06 | [M14](../modules/M14.md) | HYPOTHESIS | when the timer reaches zero the reservation is released and the item leaves the cart. Not observed. |
| BR-15-01 | [M15](../modules/M15.md) | CONFIRMED | membership is free. Zalando group credentials work (shared identity), and social login is offered (help page). |
| BR-15-02 | [M15](../modules/M15.md) | CONFIRMED (CGV §9.4–9.5) | one account per customer, and members must be 18 or older. |
| BR-15-03 | [M15](../modules/M15.md) | CONFIRMED | newsletter frequency is a choice of daily and/or weekly sales. A separate opt-in allows newsletter tracking and personalisation. |
| BR-15-04 | [M15](../modules/M15.md) | CONFIRMED | unsubscribing requires a reason (the button is disabled until one is selected) and is scoped to Privé only. |
| BR-15-05 | [M15](../modules/M15.md) | CONFIRMED | members can save size filters per gender and garment group, in EU/US systems. HYPOTHESIS: they pre-filter listings (M06). |
| BR-15-06 | [M15](../modules/M15.md) | CONFIRMED | returns are self-service from the account (`/myaccount/return/selection`), with a separate withdrawal entry point (`?withdrawal=true`). CGV §6 says returns use a QR code or label from the account. |
| BR-15-07 | [M15](../modules/M15.md) | CONFIRMED | save buttons are disabled until a change is made. |
| BR-16-01 | [M16](../modules/M16.md) | CONFIRMED (copy) | free, no commitment. |
| BR-16-02 | [M16](../modules/M16.md) | UNKNOWN | modal frequency capping. No Phase 02 capture showed the modal, which suggests capping or a remembered dismissal (HYPOTHESIS). |
| BR-17-01 | [M17](../modules/M17.md) | CONFIRMED | returns are free. The window is stated inconsistently (see BR-17-04). |
| BR-17-02 | [M17](../modules/M17.md) | CONFIRMED | delivery is shown as a date range. Help: each product has its own delivery time, shown in the product details. |
| BR-17-03 | [M17](../modules/M17.md) | CONFIRMED | carriers. Help: "Privé by Zalando livre vos commandes en France grâce à Colissimo", Monday to Saturday. The footer also lists Colis Privé. |
| BR-17-04 | [M17](../modules/M17.md) | CONFIRMED (conflict) | three return periods are published: PDP 31 days from delivery; help 30 days from receipt (unworn items); CGV §6 14-day legal withdrawal, return costs covered with the account QR code or label within France. PO decision needed (Q-31). |
| BR-17-05 | [M17](../modules/M17.md) | CONFIRMED (CGV §2.7) | delivery only to metropolitan France, Corsica and Monaco. Help: only metropolitan France when logged in on .fr. |
| BR-17-06 | [M17](../modules/M17.md) | CONFIRMED (help) | delivery options are home delivery ("Livraison à domicile") or a relay point ("8 000 nouveaux bureaux"). Express delivery exists only for some articles. |
| BR-17-07 | [M17](../modules/M17.md) | CONFIRMED (help) | items of one order can't ship separately, separate orders can't be merged, and the address can't be changed after the order is validated. |
| BR-17-08 | [M17](../modules/M17.md) | CONFIRMED (CGV §2.5) | the delivery time is counted in working days from receipt of payment. |
| BR-18-01 | [M18](../modules/M18.md) | CONFIRMED | footnote markers * ¹ ³ are defined globally. |
| BR-18-02 | [M18](../modules/M18.md) | CONFIRMED | help articles live at `/help/<category>/<article-slug>`. |
| BR-18-03 | [M18](../modules/M18.md) | CONFIRMED (CGV §2.2) | the contracting party is Zalando SE (Berlin), and contracts are in French only. |
| BR-18-04 | [M18](../modules/M18.md) | CONFIRMED (CGV §10) | consumer mediation via FEVAD. |
| BR-20-01 | [M20](../modules/M20.md) | CONFIRMED | every product link records the surface it came from, for attribution. |
| BR-20-02 | [M20](../modules/M20.md) | HYPOTHESIS | bot protection blocked the fresh launch-mode browser in Phase 01. |
| BR-21-01 | [M21](../modules/M21.md) | CONFIRMED (CGV text) | the order becomes final with the "confirmer la commande" button, and an email confirmation follows (§2.3). |
| BR-21-02 | [M21](../modules/M21.md) | CONFIRMED (CGV text) | shipping happens after full payment (advance payment = reservation). Payment is due within 7 days of the order confirmation (§2.4). |
| BR-21-03 | [M21](../modules/M21.md) | CONFIRMED (CGV text) | methods per CGV are advance payment, credit card and PayPal. Some may be withheld per order, and only EU bank accounts are accepted (§4.1). The footer additionally shows Amex, Apple Pay and Klarna. |
| BR-21-04 | [M21](../modules/M21.md) | CONFIRMED (CGV text) | vouchers are free promotional codes with an expiry date. They're single use, one per order, can't be combined, may exclude brands, may have a minimum order, and must be entered before the order is sent. The voucher value isn't refunded on return (§5). |
| BR-21-05 | [M21](../modules/M21.md) | CONFIRMED (CGV text) | invoices are electronic only (§4.4). |
| BR-21-06 | [M21](../modules/M21.md) | CONFIRMED (CGV text) | a claim may be assigned to Zalando Payments GmbH (§2.8). |
| BR-22-01 | [M22](../modules/M22.md) | CONFIRMED | app installs are attributed via Adjust, with footer campaign parameters. |
