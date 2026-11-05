# Module inventory (Phase 01 baseline, updated in Phase 02)

> **Phase 02 (2026-11-04):** each module now has a detailed file in [`docs/modules/`](../modules/), with 12 sections and a fourth class, OUT OF SCOPE. Business rules are in [business-rules.md](../requirements/business-rules.md), open questions in [open-questions.md](../requirements/open-questions.md), and dependencies in [dependencies.md](../requirements/dependencies.md). Where this baseline and a module file disagree, **the module file wins**. Phase 01 corrections:
> - M04 "Prochainement" is global, not Sports-only.
> - M05 routes `/1`, `/3`, `/4`, `/58` are CONFIRMED, and `/event/1` = `/event`.
> - M05 grid sizes (scrolled): Femme 82, Homme 70, Enfant 17, Habitat 15, Sports 16, Premium 21, all sales 126.
> - M14 Cart and M15 Account were analysed after user authorization (one add-to-cart, removed afterwards; account read-only, no PII pages). M21 remains OUT OF SCOPE.

**Platform:** `https://www.zalando-prive.fr` · **Captured:** 2026-11-04 · **Evidence index:** [`evidence/INDEX.md`](../../evidence/INDEX.md)
`a11y/…` = `evidence/a11y/…`, `shot/…` = `evidence/screenshots/…`. Confidence uses CONFIRMED / HYPOTHESIS / UNKNOWN (CLAUDE.md rule 7).

This inventory lists the **functional modules visible to a shopper**. Back-end modules (admin, supplier, stock, payment) are not observable within scope. Where one is inferred, it is marked HYPOTHESIS and is included only to seed later phases.

## Summary

| ID | Module | Pages | Confidence (Phase 01) | Phase 02 file |
|---|---|---|---|---|
| M01 | Global header & USP bar | all | CONFIRMED | [M01](../modules/M01.md) |
| M02 | Main navigation & mega-menus | home, event landings | CONFIRMED | [M02](../modules/M02.md) |
| M03 | Campaign (sale event) engine & countdowns | home, event landings, campaign, PDP | CONFIRMED (UI) | [M03](../modules/M03.md) |
| M04 | Upcoming sales & "notify me" | Sports landing | CONFIRMED (UI) | [M04](../modules/M04.md) |
| M05 | Event landings (verticals) | `/event/{id}` | CONFIRMED | [M05](../modules/M05.md) |
| M06 | Product listing, filtering & sorting | campaign, category, brand, search | CONFIRMED (controls) | [M06](../modules/M06.md) |
| M07 | Product detail page | PDP | CONFIRMED | [M07](../modules/M07.md) |
| M08 | Search with brand suggestions | header | CONFIRMED | [M08](../modules/M08.md) |
| M09 | Brand pages | `/brands/{code}` | CONFIRMED | [M09](../modules/M09.md) |
| M10 | Personalization & recommendations | home | CONFIRMED (UI) | [M10](../modules/M10.md) |
| M11 | Promotional banners & merchandising rails | home, landings | CONFIRMED | [M11](../modules/M11.md) |
| M12 | Pricing display (RRP vs sale price) | all listings, PDP | CONFIRMED | [M12](../modules/M12.md) |
| M13 | Stock / scarcity indicators | PDP, search, brand | CONFIRMED (UI) | [M13](../modules/M13.md) |
| M14 | Cart | header, PDP | Entry point CONFIRMED. Internals UNKNOWN | [M14](../modules/M14.md) |
| M15 | Account & returns | header, footer | Entry point CONFIRMED. Internals UNKNOWN | [M15](../modules/M15.md) |
| M16 | Loyalty: Zalando Plus | modal, PDP | CONFIRMED (upsell) | [M16](../modules/M16.md) |
| M17 | Delivery & returns information | PDP, footer | CONFIRMED (display) | [M17](../modules/M17.md) |
| M18 | Footer, legal & help content | all | CONFIRMED (links) | [M18](../modules/M18.md) |
| M19 | Consent management | footer | CONFIRMED (link) | [M19](../modules/M19.md) |
| M20 | Analytics, attribution & bot protection | all | CONFIRMED (presence) | [M20](../modules/M20.md) |
| M21 | Checkout & payment | — | UNKNOWN (out of scope). Payment logos only | [M21](../modules/M21.md) |
| M22 | Mobile apps | footer | CONFIRMED (links) | [M22](../modules/M22.md) |

---

## M01 Global header & USP bar
- **Observation:**
  - **USP bar** (a clickable button): "Vos marques adorées · Jusqu'à -75 %* chaque jour · Retours gratuits", with an "A-Z" icon.
  - **Header:** logo → `/event`, search combobox, icons for Privé (→ `/event`), Compte and Votre panier, and a skip link "Aller au contenu principal".
- **Evidence:** `a11y/home_desktop.yaml` lines 1–22. `shot/menu_homme.png`.
- **Confidence:** CONFIRMED.
- **Unknown:** What the USP button opens. Where "A-Z" leads (not followed).

## M02 Main navigation & mega-menus
- **Observation:**
  - **Items:** Toutes les ventes · Femme · Homme · Enfant · Habitat · Sports · Premium : Édition Hiver.
  - **Two controls per vertical:** a text link (client-side route to `/event/{id}`) and a chevron button that opens a mega-menu.
  - **Menu layout:** 4–5 columns (Vêtements, Chaussures, Accessoires, Équipement, Beauté/Soins; Habitat uses its own columns). Enfant adds Garçon/Fille toggles.
  - **Opening:** on hover (mouse) or Enter (keyboard).
  - **Sports:** the panel is empty.
  - **Scope:** the nav bar is only on home and event landings, not on campaign, PDP, brand or category pages.
- **Evidence:** `a11y/menu_{femme,homme,enfant,habitat,sports}.yaml`. `shot/menu_homme.png`, `shot/menu_sports.png`. `evidence/nav-routes.json`. Full tree in [sitemap.md §3](sitemap.md).
- **Confidence:** CONFIRMED. The reason for the empty Sports panel: HYPOTHESIS (curated event without a taxonomy menu).
- **Unknown:** Whether menu content is CMS-managed per country or season.

## M03 Campaign (sale event) engine & countdowns
- **Observation:**
  - **Campaign:** a named sale (brand, theme or price-point, e.g. "Looks enfant à moins de 40 €", "Vans", "Envii + JJXX + Noisy May") with an image, an end countdown ("Se termine dans") and a maximum discount ("Économisez jusqu'à* N%").
  - **Homepage on 2026-11-04:** at least 120 tiles (lower bound; the tree is truncated). Discounts 50–85%, countdowns from hours to about 4 days.
  - **Campaign page:** shows the product catalogue scoped to that campaign.
  - **Product scoping:** products are addressed per campaign (`/campaigns/{id}/articles/{sku}`). One model code, `NI112P025`, appears under two campaigns as different variants: `ZZO4RN5/…-Q12` and `ZZO4RMX/…-K11` (`a11y/search_results.yaml` lines 426, 446). No identical full SKU was seen in two campaigns.
- **Evidence:** `a11y/home_desktop.yaml` (e.g. lines 52–58). `a11y/campaign_desktop.yaml`. `a11y/product_desktop.yaml` (PDP countdown "2 jours 7 heures 25 minutes").
- **Confidence:** CONFIRMED (UI and URL structure).
  - **HYPOTHESIS (core flash-sale rule):** price and availability are bound to the campaign. A product is purchasable only while its campaign is live.
  - **HYPOTHESIS:** an editorial or merchandising back-office creates campaigns with start/end, assortment and imagery.
- **Unknown:** The ended-campaign URL behaviour, how the discount headline is calculated and the stock allocation per campaign.

## M04 Upcoming sales & "notify me"
- **Observation:** The "Prochainement" section lists future campaigns with "Démarre dans" (starts in) and a countdown, plus an "Informez moi" button. Seen on `/event/3774` (Sports).
- **Evidence:** `a11y/page_sports.yaml` lines ~283–300.
- **Confidence:** CONFIRMED (UI). HYPOTHESIS: per-member opt-in, with an alert when the campaign opens.
- **Unknown:** The channel (email or push) and whether the section exists on other landings (it was not in the captured trees for home or Femme).

## M05 Event landings (verticals)
- **Observation:** `/event/{id}` pages share one template: H1 "ZALANDO PRIVÉ", a hero banner with "Découvrir", an H2 vertical title and a campaign grid. "Populaire dans votre région" appears on Femme, Sports and Premium. Grid sizes (unique campaign links): Femme 79, Sports 16 (plus the upcoming section), Premium 21.
- **Evidence:** `a11y/page_femme.yaml`, `a11y/page_sports.yaml`. `a11y/page_premium.yaml`. `evidence/nav-routes.json`.
- **Confidence:** CONFIRMED for /2, /3774, /3849. HYPOTHESIS for /1, /3, /4, /58 (routes confirmed, content not captured; /1 likely an alias of `/event`).
- **Unknown:** Whether event IDs are a fixed list or a CMS entity.

## M06 Product listing, filtering & sorting
- **Observation:**
  - **Shared listing component:** breadcrumb, H1, filter pill group "Filtres" (catégories, Taille, Marque, Composition, Couleur, Prix, Tri; category pages add "Longueur"), "Réinitialiser", an active-filter status (e.g. "Vos filtres (5 Articles)") and a product grid.
  - **First load:** 60 tiles.
  - **Tile contents:** brand, name, type and colour, RRP, sale price, two images (hover alternate) and optional badges.
- **Evidence:** `a11y/campaign_desktop.yaml`, `a11y/category_80000687_outline.txt`, `a11y/brand_H1X.yaml`, `a11y/search_results.yaml`. `shot/campaign_desktop.png`.
- **Confidence:** CONFIRMED (controls). Filter options were not opened.
- **Unknown:** Filter values, multi-select behaviour, sort options, pagination vs infinite scroll, and whether filters are reflected in the URL.

## M07 Product detail page (PDP)
- **Observation:**
  - **Navigation and media:** breadcrumb (Accueil › campaign › product), a media gallery (6 image tabs) and a brand link → `/brands/{code}`.
  - **Product and price:** H2 type/colour, sale price with struck RRP, campaign countdown.
  - **Sizes:** size radios (FR sizes, low-stock labels) and a "Plus de tailles" toggle.
  - **Purchase:** "Ajouter au panier".
  - **Information:** accordions "Détails" (attributes + reference), "Taille & Coupe", "Composition", "Voir les informations sur la fabrication"; a Plus block; delivery window; returns (31 days).
  - **Related browsing:** "Voir plus" facet checkboxes (brand, category, sub-category).
- **Evidence:** `a11y/product_desktop.yaml`, `shot/product_desktop.png`.
- **Confidence:** CONFIRMED.
- **Unknown:** Add-to-cart behaviour and limits (not clicked), the purpose of the "Voir plus" checkboxes (HYPOTHESIS: filters for a related listing), and the size-advice source.

## M08 Search with brand suggestions
- **Observation:**
  - **Header combobox:** typing "nike" shows a listbox of brand suggestions (Nike, Nike Sportswear, Nike Performance, Nike SB, Nike Golf), plus "Close search" and clear buttons.
  - **Results:** selecting a suggestion goes to `/search-catalog?query=…&searchAnonId=…` and shows a product grid.
  - **Labels:** two ARIA labels are in German.
- **Evidence:** `a11y/search_suggest.yaml`, `shot/search_suggest.png`, `a11y/search_results.yaml` (anon ID redacted).
- **Confidence:** CONFIRMED.
- **Unknown:** Free-text search, category or campaign suggestions, typo tolerance, and the meaning of `searchAnonId` (HYPOTHESIS: anonymous search-session ID for analytics).

## M09 Brand pages
- **Observation:** `/brands/{code}` (e.g. H1X = HOKA) lists the brand's products across live campaigns, using the M06 listing with the brand pre-filtered. Breadcrumb shows the raw code "H1X". Tiles can show "Réservé".
- **Evidence:** `a11y/brand_H1X.yaml`, `shot/brand_H1X.png`.
- **Confidence:** CONFIRMED.
- **Unknown:** Whether there's a brand directory (A-Z) or a brand-follow feature.

## M10 Personalization & recommendations
- **Observation:** Homepage rails:
  - "Recommandé pour vous" (9 items + "Voir tout" → `/campaigns/top-picks/articles`)
  - "Continuez votre shopping: <category>" (4 tabbed carousels with "Découvrir plus")
  - "Populaire dans votre région" (regional bestsellers)
- **Evidence:** `a11y/home_desktop.yaml` lines 94, 243, 335, 467, 560, 1466. Link parameters `tp4u`, `continue_shopping`, `highligts_carousel`.
- **Confidence:** CONFIRMED (UI). HYPOTHESIS: driven by browsing history and the member's region. Item contents are the account's own history and are deliberately not listed.
- **Unknown:** The algorithms, data sources and the guest fallback.

## M11 Promotional banners & merchandising rails
- **Observation:**
  - **Homepage:** an inline editorial banner "Pièces design & décoration iconique" with CTA "Achetez pour votre habitat".
  - **Event landings:** a hero banner with "Découvrir" (e.g. "BOSS – Homme" on Premium).
  - **Campaign grid:** mixes brand campaigns and thematic campaigns (price-point, occasion, size-range e.g. "Votre taille parfaite : Tall, Petite & Curve", gift ideas).
- **Evidence:** `a11y/home_desktop.yaml` lines 172–175. `a11y/page_femme.yaml` (button "Découvrir"). `a11y/page_premium.yaml`.
- **Confidence:** CONFIRMED.
- **Unknown:** Banner scheduling and targeting.

## M12 Pricing display
- **Observation:** RRP ("Prix de vente conseillé", struck through) vs "Prix de vente". Footnotes say the discount is vs RRP, VAT is included, and packaging/handling fees are extra per CGV §2. An RRP explainer help page is linked.
- **Evidence:** `a11y/campaign_desktop.yaml` (tiles and footer note).
- **Confidence:** CONFIRMED.
- **Unknown:** The fee amount, rounding and any member-only price.

## M13 Stock / scarcity indicators
- **Observation:** "Plus que N disponibles" per size on the PDP, a "Dernières heures" badge in search, and a "Réservé" state on brand tiles.
- **Evidence:** `a11y/product_desktop.yaml`, `a11y/search_results.yaml`, `a11y/brand_H1X.yaml`.
- **Confidence:** CONFIRMED (UI). HYPOTHESIS: items in other members' carts are temporarily reserved ("Réservé"), and "Dernières heures" marks campaigns ending within a threshold.
- **Unknown:** The reservation timeout, the low-stock threshold and the badge rule.

## M14 Cart
- **Observation:** Entry points are the "Votre panier" header button and "Ajouter au panier" on the PDP.
- **Evidence:** `a11y/home_desktop.yaml` line 22. `a11y/product_desktop.yaml`.
- **Confidence:** Entry point CONFIRMED. Internals UNKNOWN (out of scope; not opened).
- **Unknown:** Cart reservation timer, quantity limits and the cart UI.

## M15 Account & returns
- **Observation:** The "Compte" header button. The footer "Rétractation" (withdrawal) links to `/myaccount/return/selection?withdrawal=true`.
- **Evidence:** `a11y/home_desktop.yaml` line 20. `a11y/campaign_desktop.yaml` footer.
- **Confidence:** Entry points CONFIRMED. Internals UNKNOWN (out of scope).
- **Unknown:** Profile, orders, addresses, returns flow and notification preferences.

## M16 Loyalty: Zalando Plus
- **Observation:** A homepage modal promotes Zalando Plus (points, levels, rewards; free, no commitment) with "Peut-être plus tard" and "Activez Plus" buttons and a T&C link. The PDP has an "Activez Plus" block.
- **Evidence:** `shot/home_plus_modal.png` (`ev_700da19d78f53e24…`). `a11y/product_desktop.yaml`.
- **Confidence:** CONFIRMED (upsell). HYPOTHESIS: a programme shared with Zalando.
- **Unknown:** Earn and burn rules, tiers and modal frequency capping.

## M17 Delivery & returns information
- **Observation:** The PDP shows a delivery date window and "Retour gratuit durant 31 jours". The footer lists the delivery partners Colissimo and Colis Privé (→ `/help/envoi-et-livraison/`). The USP bar says "Retours gratuits".
- **Evidence:** `a11y/product_desktop.yaml`. `a11y/brand_H1X.yaml` footer.
- **Confidence:** CONFIRMED (display).
- **Unknown:** Shipping cost, carrier selection and how the window is calculated.

## M18 Footer, legal & help content
- **Observation:** The footer has the groups Privé by Zalando (about, legal notice, privacy, CGV, withdrawal, careers, vulnerability report, product safety), Service client (help, size guide), Groupe Zalando, payment logos, social, delivery partners and apps, plus pricing footnotes.
- **Evidence:** `a11y/brand_H1X.yaml`, `a11y/campaign_desktop.yaml` (contentinfo).
- **Confidence:** CONFIRMED (links). Page contents not visited.
- **Unknown:** Help-centre structure.

## M19 Consent management
- **Observation:** The footer link "Traitement des données" → `#uc-central-modal-show`.
- **Evidence:** `a11y/campaign_desktop.yaml`. `inspect_web_page` DOM URLs (`ev_1de6607a8e1309dd…`).
- **Confidence:** CONFIRMED (link). HYPOTHESIS: a Usercentrics CMP (from the `uc-` anchor naming).
- **Unknown:** Consent categories.

## M20 Analytics, attribution & bot protection
- **Observation:**
  - Google Tag Manager is served first-party.
  - An Akamai-style `/akam/…` script and an obfuscated first-party sensor script are present.
  - Adjust links are used for app install attribution.
  - `navigationSource` / `catalog-type` parameters appear on product links.
  - A service worker is registered.
- **Evidence:** `ev_1de6607a8e1309dd…` (scripts/resources). Sitemap §2 parameter table. The scenario was refused until service workers were allowed.
- **Confidence:** CONFIRMED (presence). HYPOTHESIS: bot protection explains the launch-mode navigation failure.
- **Unknown:** Vendors behind GTM and the service worker's role (offline, push or caching).

## M21 Checkout & payment
- **Observation:** Only payment logos are visible: PayPal, Mastercard, Visa, Amex, Apple Pay, Klarna.
- **Evidence:** `a11y/brand_H1X.yaml` footer ("Mode de paiement").
- **Confidence:** Logos CONFIRMED. Checkout UNKNOWN (out of scope; no purchases per CLAUDE.md rule 4).
- **Unknown:** The checkout steps, vouchers and payment flow.

## M22 Mobile apps
- **Observation:** Google Play and App Store links via Adjust, with a fallback to `/appli-mobile/`.
- **Evidence:** `a11y/campaign_desktop.yaml` footer.
- **Confidence:** CONFIRMED.
- **Unknown:** App feature set. Responsive web layout: UNKNOWN (see platform-overview §3).

---

## Seeds for later phases (HYPOTHESIS, not observed)
Implied back-office capabilities needed to run what was observed:
- Campaign management (schedule, assortment, imagery, max-discount headline, upcoming visibility)
- Event and vertical configuration
- Category taxonomy
- Brand catalogue
- Stock reservation with timeout
- Notification subscriptions
- Recommendation feeds
- CMS banners
