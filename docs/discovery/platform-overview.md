# Phase 01 — Platform Overview

**Reference platform:** Privé by Zalando (France), `https://www.zalando-prive.fr`
**Capture date:** 2026-11-04
**Method:** Scoped black-box observation with REA (CDP) on the user's active, logged-in Brave tab. Passive inspection plus read-only navigation, menu opening and search typing. No purchases. No account, cart, checkout or order pages were opened. No form was submitted.
**Approved scope (user decision, 2026-11-04):** the open tab plus pages reachable through visible navigation (homepage, menus, categories, campaign listings, product pages, search). Account, cart, checkout and order pages are out of scope.
**Evidence index:** [`evidence/INDEX.md`](../../evidence/INDEX.md). Evidence references below use `a11y/<file>` = `evidence/a11y/<file>` and `shot/<file>` = `evidence/screenshots/<file>`.

Confidence vocabulary (CLAUDE.md rule 7): **CONFIRMED** = directly observed in captured evidence. **HYPOTHESIS** = inferred from evidence, not proven. **UNKNOWN** = not observable within scope.

---

## 1. Platform summary

Privé by Zalando is a members-oriented **flash-sale ("ventes privées") fashion and home e-commerce site**. Its core unit is the **campaign**: a time-limited sale event with a countdown and an "up to X% off" headline. Products are always reached *inside* a campaign (`/campaigns/{id}/articles/{sku}`), even when you arrive from categories, brands or search.

---

## 2. Findings

### F-01 Flash-sale business model built on time-boxed campaigns
- **Observation:** Every sale tile shows a title, a "Se termine dans" countdown (ends in X days/hours) and "Économisez jusqu'à* N%". On 2026-11-04 the captured homepage tree held at least 120 campaign tiles (120 unique `/campaigns/{id}` links, each with its own countdown). That's a lower bound, because the tree is truncated. Across those 120 tiles, discounts ranged from 50% to 85%, and 75% was the most common value (55 tiles). Countdowns ranged from hours to about 4 days.
- **Evidence:** `a11y/home_desktop.yaml` (e.g. lines 52–58, "Chaussures & accessoires pour enfant", `/campaigns/ZZO4RM8`, 76%). `shot/home_desktop.png`.
- **Confidence:** CONFIRMED (UI). HYPOTHESIS: campaign duration is configured per campaign, with typical lengths of 1–4 days.
- **Unknown:** How campaign start/end and the "up to %" value are calculated (max discount across items or curated). What happens to a campaign URL after it ends.

### F-02 Upcoming sales with "notify me"
- **Phase 02 correction:** "Prochainement" isn't specific to Sports. It loads lazily on scroll and is the same global list on `/event`, `/1`, `/2`, `/3`, `/4`, `/58` and `/3774` (absent on Premium). See [M04](../modules/M04.md) and [M05](../modules/M05.md).
- **Observation (Phase 01):** The Sports landing page has a "Prochainement" (upcoming) section. Its tiles show "Démarre dans" (starts in), a countdown and an "Informez moi" (notify me) button. 33 such buttons were present.
- **Evidence:** `a11y/page_sports.yaml` lines ~285–295 ("Berlinger Haus", "Démarre dans 1 heure 21 minutes", button "Informez moi").
- **Confidence:** CONFIRMED (UI). HYPOTHESIS: "notify me" subscribes the member to an email or push alert when the campaign opens.
- **Unknown:** Notification channel. Whether the section also appears on the homepage (it was not in the captured homepage tree, which appears truncated; see §4). The button was **not** clicked, because that would change account state.

### F-03 Scarcity and urgency signals
- **Observation:**
  - Product sizes can show "Plus que 3 disponibles" (only 3 left).
  - Search result items can carry a "Dernières heures" (last hours) badge.
  - Brand listing items can show a "Réservé" (reserved) button state.
  - Product pages repeat the campaign countdown.
- **Evidence:**
  - `a11y/product_desktop.yaml` (radio "43 1/3 Plus que 3 disponibles", "Se termine dans" + time).
  - `a11y/search_results.yaml` (14 × "Dernières heures").
  - `a11y/brand_H1X.yaml` (button "Réservé").
- **Confidence:** CONFIRMED (UI labels). HYPOTHESIS: "Réservé" means all remaining stock is held in other shoppers' carts (cart-based stock reservation). This is common in flash-sale platforms but not proven here.
- **Unknown:** Reservation duration, the low-stock threshold and whether "Réservé" items become available again.

### F-04 Pricing presentation: "prix de vente conseillé" (RRP) vs sale price
- **Observation:** Listings and product pages show the RRP (struck through) and the sale price. The footer note says discounts are calculated against the RRP, that prices include VAT, and that packaging and handling fees are not included (per §2 of the CGV, the terms of sale). An explainer page is linked.
- **Evidence:** `a11y/campaign_desktop.yaml` (e.g. "Prix de vente conseillé 59,95 €" / "Prix de vente 25,90 €", footer note). Link `/help/comment-fonctionne-zalando-prive/que-signifie-pvc`.
- **Confidence:** CONFIRMED.
- **Unknown:** The fee amount and its rules (in the CGV, not read in this phase).

### F-05 Logged-in member experience with personalization
- **Observation:** The header shows "Compte" and "Votre panier". The homepage contains personalized rails: "Recommandé pour vous", "Continuez votre shopping: <category>" (×4) and "Populaire dans votre région".
- **Evidence:** `a11y/home_desktop.yaml` lines 94 (Recommandé pour vous), 243/335/467/560 (tabpanels "Continuez votre shopping"), 1466 ("Populaire dans votre région").
- **Confidence:** CONFIRMED in Phase 02: `/myaccount` loaded the member overview (`a11y/account_overview.yaml`, name redacted). Phase 01 had marked this HYPOTHESIS.
- **Unknown:** What a guest sees. A fresh-profile launch failed before first capture, so the guest experience was not observed (see §4).

### F-06 Zalando Plus loyalty upsell
- **Observation:** On homepage load a modal offers "Zalando Plus": collect points, reach levels, get rewards. It says Plus is "100% gratuit et sans engagement" (free, no commitment) and has "Peut-être plus tard" (maybe later) and "Activez Plus" buttons. The product page repeats an "Activez Plus" block.
- **Evidence:** `shot/home_plus_modal.png` (evidence `ev_700da19d78f53e24…`). `a11y/product_desktop.yaml` ("Rejoignez Plus…", button "Activez Plus").
- **Confidence:** CONFIRMED that the upsell exists. HYPOTHESIS: this account is not enrolled in Plus.
- **Unknown:** Tier rules, the points rate and its relation to Privé pricing. The modal was dismissed with Escape. Neither "Activez Plus" nor "Peut-être plus tard" was clicked.

### F-07 Part of the Zalando group ecosystem
- **Observation:** The footer links to Zalando and ABOUT YOU ("Groupe Zalando"), Zalando corporate (vulnerability report) and a Zalando.fr product-safety FAQ. Product-safety and Plus content is shared with Zalando.
- **Evidence:** `a11y/campaign_desktop.yaml` / `a11y/brand_H1X.yaml` contentinfo section.
- **Confidence:** CONFIRMED.
- **Unknown:** Whether account and identity are shared with zalando.fr (not tested).

### F-08 Payment, delivery and returns signals (display only)
- **Observation:**
  - **Payment logos in the footer:** PayPal, Mastercard, Visa, Amex, Apple Pay, Klarna.
  - **Delivery partners:** Colissimo, Colis Privé.
  - **Product page:** shows a delivery window ("6 novembre – 9 novembre") and "Retour gratuit durant 31 jours" (free returns for 31 days from delivery).
  - **Top banner:** "Vos marques adorées · Jusqu'à -75 %* chaque jour · Retours gratuits".
- **Evidence:** `a11y/brand_H1X.yaml` (footer "Mode de paiement" imgs). `a11y/product_desktop.yaml` ("Délai De Livraison", "Retours"). `a11y/home_desktop.yaml` lines 1–6.
- **Confidence:** CONFIRMED (displayed). Accepted payment methods at checkout: UNKNOWN (checkout is out of scope).
- **Unknown:** Shipping fees, the minimum order and how the delivery window is calculated.

### F-09 Native mobile apps exist
- **Observation:** The footer links to Google Play and the App Store through an Adjust tracking link with a fallback to `/appli-mobile/`.
- **Evidence:** `a11y/campaign_desktop.yaml` footer ("Applications mobiles").
- **Confidence:** CONFIRMED.
- **Unknown:** Feature parity between the apps and the web.

### F-10 Localization inconsistency in search
- **Observation:** The search UI is French, but two accessibility labels are German: "Suche löschen" (clear search) and "Suchvorschläge" (search suggestions).
- **Evidence:** `a11y/search_suggest.yaml` (banner section).
- **Confidence:** CONFIRMED observed. The REA `en-US` locale setting affects only Intl formatting, so it does not explain these labels. HYPOTHESIS: a shared multi-country component with missing FR translations.
- **Unknown:** n/a.

### F-11 Third-party and technical signals (surface only; detail deferred to the technical phase)
- **Observation:**
  - Google Tag Manager is served first-party (`/gtm.js?id=GTM-…`).
  - An Akamai bot-manager style script (`/akam/…`) and an obfuscated first-party sensor script are present.
  - A service worker is registered.
  - Usercentrics-style consent link (`#uc-central-modal-show`, "Traitement des données").
  - Client-side navigation: main-nav items are `link` elements without `href`, and clicking them changes the URL.
- **Evidence:** `inspect_web_page` evidence `ev_1de6607a8e1309dd…` (scripts/resources, `outside_policy` URL entries). The REA scenario was refused until `service_workers: allow` was set (service_worker_blocked error). `a11y/home_desktop.yaml` lines 23–43 (nav links without `/url`).
- **Confidence:** CONFIRMED (presence). HYPOTHESIS: a SPA front-end with history-API routing.
- **Unknown:** Front-end framework, API endpoints and data shapes (network bodies were deliberately not captured).

---

## 3. Layouts

### Desktop (1280×720)
- **Observation:**
  - **Header:** USP bar (lime) · logo · search combobox ("Quelles sont les offres du jour ?") · Privé / Compte / Votre panier icons.
  - **Main navigation bar:** Toutes les ventes · Femme · Homme · Enfant · Habitat · Sports · Premium : Édition Hiver. Each of Femme, Homme, Enfant, Habitat and Sports has a chevron button that opens a 5-column mega-menu. The menu opens on hover or keyboard.
  - Event pages use a 2-column-then-mixed campaign tile grid with large imagery. Listing pages use a 4-column product grid with a filter pill bar.
  - **Variation:** campaign, product, brand and category pages show only the header, **without** the main navigation bar.
- **Evidence:** `shot/menu_homme.png`, `shot/campaign_desktop.png`, `shot/product_desktop.png`.
- **Confidence:** CONFIRMED.
- **Unknown:** Breakpoints between 390 and 1280 px, how hover-driven mega-menus behave on touch devices, and homepage content below the fold or lazy-loaded.

### Mobile — UNKNOWN
- **Observation:** Two attempts did not produce a real mobile layout.
  1. Viewport emulation at 390×844 on the attached tab: the page did not reflow. The desktop header was simply clipped, and the accessibility tree was identical to desktop.
  2. A separately launched Brave at 390×844 failed to navigate before first capture. This is HYPOTHESIS: bot protection or a fresh-profile restriction.
- **Evidence:** `shot/viewport390_connect_no_reflow.png`, `a11y/viewport390_connect_no_reflow.yaml`. Launch failure is noted in INDEX.md.
- **Confidence:** UNKNOWN (no valid mobile evidence).
- **Follow-up proposal:** in the same Brave tab, enable DevTools device toolbar → phone preset, then re-run a passive `inspect_web_page` and screenshot.

---

## 4. User roles visible in the authorized experience

| Role | Observation | Evidence | Confidence | Unknown |
|---|---|---|---|---|
| Authenticated member (shopper) | Compte + Votre panier controls; personalized rails | `a11y/home_desktop.yaml`, `a11y/account_overview.yaml` | CONFIRMED (Phase 02) | See [M15](../modules/M15.md) |
| Zalando Plus member / non-member | Plus enrolment upsell modal and PDP block | `shot/home_plus_modal.png`, `a11y/product_desktop.yaml` | CONFIRMED (upsell). This account's tier: HYPOTHESIS (not enrolled) | Tier levels and benefits |
| Guest / visitor | Not observed | launch attempt failed | UNKNOWN | Whether browsing requires login |
| Staff / admin, brand/seller | No visible trace in the shopper UI | — | UNKNOWN | Back-office (outside any shopper-side scope) |

---

## 5. Limitations of this phase
- Captured on **2026-11-04**. Campaign IDs (`ZZO4…`), countdowns and event content expire. Many URLs here will 404 or redirect later.
- The homepage accessibility tree appears truncated (no footer; lazy-loaded or infinite content). Content after the first ~120 campaign tiles was not captured, so homepage counts are lower bounds. *Phase 02:* a scrolled capture (`a11y/home_scrolled.yaml`) reached the footer and counted 126 campaigns + 33 upcoming. `/event/1`, `/3`, `/4` and `/58` are now captured too (see [M05](../modules/M05.md)).
- The navigation, search typing ("nike" → "Nike Sportswear") and page views were read-only, but they **may have fed the account's personalization** ("Continuez votre shopping", recommendations). They are not zero-side-effect.
- The Premium event page (`/event/3849`) has accessibility evidence (`a11y/page_premium.yaml`) but no screenshot. Screenshot attempts on that page stalled twice and were stopped.
- Some homepage rails show the account's own browsing history. Their item contents are not reproduced here; only the module types are.
- Phase 01 didn't visit the account (`Compte`), cart (`Votre panier`), `/myaccount/return/selection`, checkout, orders, "Informez moi" or "Activez Plus". *Phase 02 update:* the cart and account pages were analysed after authorization (see [M14](../modules/M14.md), [M15](../modules/M15.md)). Checkout, "Informez moi" and "Activez Plus" remain unvisited.
