# Software Requirements Specification (SRS) — Phase 04

**Product:** an independent flash-sale e-commerce application (Laravel 13 / PHP 8.3+, React 19, MySQL, REST API, Docker).
**Reference analysed:** zalando-prive.fr, authorized scope only, observed on 2026-11-04 (Phases 01–03).
**Companion documents:**
- [PRD.md](PRD.md): domain, personas and scope
- [business-rules.md](business-rules.md)
- [use-cases.md](use-cases.md)
- [user-stories.md](user-stories.md)
- [acceptance-criteria.md](acceptance-criteria.md)
- [traceability-matrix.md](traceability-matrix.md)
- [open-questions.md](open-questions.md)

> This file was generated with the acceptance criteria, the traceability matrix, the user stories and the use cases from one requirements list. All IDs and references were cross-checked when they were generated. Any hand edit must be made in all of these files, so they stay in sync.

## 1. Introduction

### 1.1 Purpose
This document states **what** the new application must do. It is derived only from documented, authorized observations of the reference. It does not describe how the reference is built internally, and it isn't a design.

### 1.2 Scope
**In scope:**
- shopper-facing browsing of events, campaigns, catalogue, products, search, brands and recommendations
- the reservation cart, up to the checkout button
- the account areas that were observed
- help, legal and consent entry points

**Out of scope:**
- checkout and payment (M21)
- Zalando Plus (M16)
- notify-me behaviour
- the back-office

The last three are recorded as gaps, not specified.

### 1.3 Labels
| Field | Values | Meaning |
|---|---|---|
| **Classification** | CONFIRMED | Observed on the reference or stated in its published text, or derived directly from such an observation (e.g. defect avoidance), or a project constraint set by the user (NFR-TECH-01). Usable as a requirement. |
| | HYPOTHESIS | Inferred from observations. **Needs PO validation before implementation.** |
| | UNKNOWN | Needed, but not observable. Placeholder until the PO decides. |
| | OUT OF SCOPE | Excluded by authorization or by the project scope. |
| **Confidence** | HIGH / MEDIUM / LOW | HIGH = read directly, repeatedly or from published text. MEDIUM = one observation or a small inference. LOW = weak signal. |
| **Priority** | Must / Should / Could / Won't | **Proposed, PO to confirm.** Basis: PRD §8. |

### 1.4 Evidence notation
- `a11y/<stem>` = `evidence/a11y/<stem>.yaml` (or `.txt`)
- `shot/<stem>` = `evidence/screenshots/<stem>.png`
- `har #n` = entry *n* of `evidence/har/zalando`
- BR-IDs refer to [business-rules.md](business-rules.md)
- Q-IDs refer to [open-questions.md](open-questions.md)

## 2. Overall description

### 2.1 Product perspective
The product is a members-only flash-sale shop:
- **Sales:** time-limited **campaigns** (sales) are grouped on **event** landings (verticals).
- **Products:** articles are sold **inside** a campaign at a discount vs the RRP.
- **Stock:** limited stock, reserved for 20 minutes when added to the cart.

See [PRD.md](PRD.md) §2.

### 2.2 Constraints
- **Stack:** Laravel 13 / PHP 8.3+, React 19, MySQL, REST, Docker (NFR-TECH-01). This is a project constraint, not something inferred from the reference.
- **Language and market:** French UI, euro prices incl. VAT, France as the reference market.
- **Data protection:** no personal data from the reference is reused.

### 2.3 Assumptions and dependencies
- **HYPOTHESIS requirements** are not to be implemented until the PO confirms them.
- **Policy values** (return period, fee, carriers, launch slots, legal entity) are **PO decisions**. The reference values are input, not defaults to copy.
- **Back-office:** a back-office for campaigns, events, catalogue and stock is implied but was never observed (Q-50). It needs its own specification.


## 3. Functional requirements

### FR-NAV: Header & navigation

#### FR-NAV-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Every page shows the same global header: logo and "Privé" link to the default event, a search box, an account entry, a cart button and a skip link to the main content.
- **Source module:** M01
- **Evidence:** BR-01-02; `a11y/home_desktop`; `a11y/campaign_desktop`; `a11y/product_desktop`
- **Acceptance criteria:**
  - AC-FR-NAV-01-1: Given any event, campaign, product, brand or search page, then the header shows the logo, search, "Compte" and "Votre panier".
  - AC-FR-NAV-01-2: Given the header, when the member activates the logo, then the default event page (`/event`) opens.
  - AC-FR-NAV-01-3: Given keyboard focus at the top of a page, then a skip link "Aller au contenu principal" moves focus to the main content.

#### FR-NAV-02
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** A USP bar states the value proposition, including a maximum-discount claim that carries the same RRP footnote (*) as campaign headlines.
- **Source module:** M01
- **Evidence:** BR-01-01; `a11y/home_desktop`
- **Acceptance criteria:**
  - AC-FR-NAV-02-1: Given any page, then the USP bar shows the configured claims, and a discount claim carries the "*" footnote defined as "Par rapport au prix de vente conseillé".
- **Note:** Claim texts are content, not hard-coded.

#### FR-NAV-03
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The main navigation lists the event verticals and links each one to its event landing. It appears only on event pages.
- **Source module:** M02
- **Evidence:** BR-02-01; `a11y/menu_femme`; `a11y/campaign_desktop`; `evidence/nav-routes.json`
- **Acceptance criteria:**
  - AC-FR-NAV-03-1: Given an event page, then the main nav shows the configured verticals in order, each linking to `/event/{id}`.
  - AC-FR-NAV-03-2: Given a campaign, product, brand or search page, then the main nav is not shown.

#### FR-NAV-04
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** A vertical can have a mega-menu of category columns linking to category listings. It opens on hover or with the keyboard. A vertical can also have no mega-menu.
- **Source module:** M02
- **Evidence:** BR-02-02; BR-02-03; `a11y/menu_homme`; `a11y/menu_enfant`; `a11y/menu_sports`
- **Acceptance criteria:**
  - AC-FR-NAV-04-1: Given a vertical with a mega-menu, when the member hovers its chevron or presses Enter on it, then the panel opens with category columns linking to `/articles/categories/{id}`.
  - AC-FR-NAV-04-2: Given a vertical configured without a mega-menu (as Premium in the reference), then no chevron is shown.
- **Note:** The reason for the empty Sports panel is HYPOTHESIS (BR-02-03). Don't replicate an empty panel.

#### FR-NAV-05
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The header cart button exposes the item count and the remaining reservation time while items are reserved.
- **Source module:** M01, M14
- **Evidence:** BR-14-02; `a11y/pdp_added`; `a11y/cart_with_item`
- **Acceptance criteria:**
  - AC-FR-NAV-05-1: Given one reserved item, then the cart button's accessible name reads "Votre panier - 1 Article - <mm> minutes et <ss> secondes restantes".
  - AC-FR-NAV-05-2: Given an empty cart, then the cart button shows no count and no timer.

### FR-EVT: Event landings

#### FR-EVT-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Event landings are addressed by numeric ID (`/event/{id}`). `/event` shows the default "all sales" event.
- **Source module:** M05
- **Evidence:** BR-05-01; BR-05-02; `evidence/nav-routes.json`; `a11y/page_all`
- **Acceptance criteria:**
  - AC-FR-EVT-01-1: Given an existing event ID, when `/event/{id}` is requested, then that event's landing is rendered.
  - AC-FR-EVT-01-2: Given `/event`, then the default event is rendered with the same campaign set as the "Toutes les ventes" event.
- **Note:** Whether `/event/1` is an alias is HYPOTHESIS (BR-05-02). The new app may make the default event configurable.

#### FR-EVT-02
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** An event landing shows a grid of its **live** campaigns. A campaign can be assigned to several events, and every campaign on a vertical is also on "all sales".
- **Source module:** M05, M03
- **Evidence:** BR-05-03; BR-05-08; BR-03-13; `a11y/page_femme_scrolled`; `a11y/home_scrolled`
- **Acceptance criteria:**
  - AC-FR-EVT-02-1: Given a campaign assigned to events A and B, when it is live, then it appears in the grids of A, B and "all sales".
  - AC-FR-EVT-02-2: Given an upcoming campaign, then it does not appear in any event grid.

#### FR-EVT-03
**Priority:** Could · **Classification:** HYPOTHESIS · **Confidence:** MEDIUM

- **Description:** Grid order is set by merchandising: neither alphabetical nor by end time.
- **Source module:** M05
- **Evidence:** BR-05-09; `a11y/home_scrolled`
- **Acceptance criteria:**
  - AC-FR-EVT-03-1: Given an ordering set for an event, then the grid renders campaigns in that order.
- **Open questions:** Q-50
- **Note:** The ranking logic of the reference is unobserved.

#### FR-EVT-04
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Each vertical event shows one hero banner. The default event shows none.
- **Source module:** M11
- **Evidence:** BR-11-01; BR-11-02; `a11y/page_femme`; `a11y/home_desktop`
- **Acceptance criteria:**
  - AC-FR-EVT-04-1: Given a vertical event with a hero configured, then exactly one hero is shown above the grid.
  - AC-FR-EVT-04-2: Given `/event`, then no hero is shown.
- **Open questions:** Q-13
- **Note:** The hero link target is HYPOTHESIS (BR-11-02).

#### FR-EVT-05
**Priority:** Could · **Classification:** HYPOTHESIS · **Confidence:** LOW

- **Description:** A seasonal or curated event can use a template without the "Prochainement" section and the footer.
- **Source module:** M05
- **Evidence:** BR-05-05; `a11y/page_premium_scrolled`
- **Acceptance criteria:**
  - AC-FR-EVT-05-1: Given an event using the curated template, then neither the upcoming section nor the footer is rendered.
- **Open questions:** Q-16

### FR-CMP: Campaign lifecycle

#### FR-CMP-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** A campaign has a name, an image, a start, an end and a headline. Its tile shows the name, image, headline and an end countdown "Se termine dans", and links to `/campaigns/{id}`.
- **Source module:** M03
- **Evidence:** BR-03-02; BR-03-04; `a11y/home_desktop`; `shot/home_desktop`
- **Acceptance criteria:**
  - AC-FR-CMP-01-1: Given a live campaign, then its tile shows name, image, headline and "Se termine dans <countdown>".
  - AC-FR-CMP-01-2: When the member activates the tile heading, then `/campaigns/{id}` opens.

#### FR-CMP-02
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The end countdown shows days, hours and minutes when at least one day remains, and hours, minutes and seconds when less than one day remains.
- **Source module:** M03
- **Evidence:** BR-03-03; `a11y/home_desktop`; `a11y/product_desktop`
- **Acceptance criteria:**
  - AC-FR-CMP-02-1: Given ≥ 1 day remaining, then the countdown reads like "2 jours 7 heures 25 minutes".
  - AC-FR-CMP-02-2: Given < 1 day remaining, then the countdown shows hours, minutes and seconds.

#### FR-CMP-03
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The campaign headline reads "Économisez jusqu'à* N%", where N is the maximum discount and * is defined as "Par rapport au prix de vente conseillé".
- **Source module:** M03, M12
- **Evidence:** BR-03-04; BR-12-01; BR-12-05; `a11y/home_desktop`
- **Acceptance criteria:**
  - AC-FR-CMP-03-1: Given a campaign with a percentage headline, then the tile shows "Économisez jusqu'à* N%" and the page footnote defines "*" vs the RRP.
- **Open questions:** Q-23
- **Note:** Whether N is computed from the SKUs or entered manually is UNKNOWN.

#### FR-CMP-04
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** A campaign can show "Special" instead of a percentage headline.
- **Source module:** M03
- **Evidence:** BR-03-05; `a11y/home_scrolled`
- **Acceptance criteria:**
  - AC-FR-CMP-04-1: Given a campaign flagged "Special", then its tile shows "Special" and no percentage.
- **Open questions:** Q-24
- **Note:** The label is CONFIRMED. The rule for when it is used is HYPOTHESIS (price-point or selection sales).

#### FR-CMP-05
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** A campaign moves from upcoming to live at its scheduled start without manual action, and leaves the grids when it ends.
- **Source module:** M03, M04, M05
- **Evidence:** BR-03-09; BR-05-08; BR-03-16; `a11y/page_sports`; `a11y/home_scrolled`
- **Acceptance criteria:**
  - AC-FR-CMP-05-1: Given an upcoming campaign whose start time passes, then it is removed from "Prochainement" and appears in its event grids.
  - AC-FR-CMP-05-2: Given a live campaign whose end time passes, then it no longer appears in any grid.
- **Open questions:** Q-17
- **Note:** Matched by name in the reference (BR-03-09). The behaviour of an ended campaign URL is UNKNOWN.

#### FR-CMP-06
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Products are addressed inside their campaign: `/campaigns/{campaignId}/articles/{sku}`.
- **Source module:** M03, M07
- **Evidence:** BR-03-01; `a11y/product_desktop`; `a11y/search_results`
- **Acceptance criteria:**
  - AC-FR-CMP-06-1: Given a product tile in any listing (campaign, category, brand, search, rail), then its link has the form `/campaigns/{campaignId}/articles/{sku}`.

#### FR-CMP-07
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** The same model can be sold in several campaigns, each time as a distinct article (SKU).
- **Source module:** M03
- **Evidence:** BR-03-14; `a11y/search_results`
- **Acceptance criteria:**
  - AC-FR-CMP-07-1: Given model M assigned to campaigns C1 and C2, then two distinct article URLs exist, one under each campaign.
- **Open questions:** Q-21
- **Note:** Seen once (one model, two campaigns).

#### FR-CMP-08
**Priority:** Could · **Classification:** HYPOTHESIS · **Confidence:** MEDIUM

- **Description:** Price and availability are bound to the campaign. An article can't be added to the cart outside its campaign's live window.
- **Source module:** M03
- **Evidence:** BR-03-15
- **Acceptance criteria:**
  - AC-FR-CMP-08-1: Given an article whose campaign has ended, when the member tries to add it, then the add is refused.
- **Open questions:** Q-17; Q-21
- **Note:** Core flash-sale rule but not observed. PO to confirm; likely promoted to Must.

#### FR-CMP-09
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** Campaign scheduling supports the reference launch pattern: about ten new sales a day, at 07:00 on weekdays and 08:00 at weekends (Paris), plus other slots (an 18:00 Paris cohort was measured), with a typical duration of 3–5 days.
- **Source module:** M03, M04
- **Evidence:** BR-03-16; BR-03-18; BR-03-10; BR-04-05; `a11y/public_help_ventes`
- **Acceptance criteria:**
  - AC-FR-CMP-09-1: Given a campaign with start S and end E, then it is upcoming before S, live from S to E, and ended after E.
  - AC-FR-CMP-09-2: Given the launch slots configured by the PO, then a campaign can be scheduled at any of them.
- **Open questions:** Q-20; Q-33
- **Note:** The published rule is CONFIRMED for the reference. Whether the new app enforces fixed slots is a PO decision.

#### FR-CMP-10
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** A campaign can be thematic (price-point, occasion, size range, gift ideas) or span several brands, not only a single brand.
- **Source module:** M11, M03
- **Evidence:** BR-11-03; BR-03-05; `a11y/home_desktop`
- **Acceptance criteria:**
  - AC-FR-CMP-10-1: Given a campaign named "Looks enfant à moins de 40 €" with articles of several brands, then it renders like any brand campaign.

### FR-UPC: Upcoming sales

#### FR-UPC-01
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** A "Prochainement" section lists upcoming campaigns with name, image and start time. Upcoming tiles have no link and expose no campaign ID.
- **Source module:** M04
- **Evidence:** BR-04-02; BR-03-11; `a11y/page_sports`; `a11y/home_scrolled`
- **Acceptance criteria:**
  - AC-FR-UPC-01-1: Given an upcoming campaign, then it appears in "Prochainement" with name, image and start time.
  - AC-FR-UPC-01-2: Given an upcoming tile, then it contains no link to the campaign page.
- **Note:** Server-side enforcement (no access before start) is HYPOTHESIS (BR-03-11).

#### FR-UPC-02
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** The start time is relative near the start ("Démarre dans 1 heure 21 minutes") and absolute further out ("Démarre le jeu. 5 novembre 06:00").
- **Source module:** M04
- **Evidence:** BR-03-12; BR-04-03; `a11y/page_sports`; `a11y/home_scrolled`
- **Acceptance criteria:**
  - AC-FR-UPC-02-1: Given a start within the threshold, then the tile reads "Démarre dans …".
  - AC-FR-UPC-02-2: Given a start beyond the threshold, then the tile reads "Démarre le <day> <date> <hh:mm>".
- **Open questions:** Q-10; Q-35
- **Note:** The switching threshold is UNKNOWN (between 1.4 h and 13 h, or same day vs later day). The display timezone is UNKNOWN (Q-10).

#### FR-UPC-03
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The upcoming list is global: the same list in the same order on every event landing that shows it.
- **Source module:** M04, M05
- **Evidence:** BR-05-04; BR-04-01; `a11y/page_all`; `a11y/page_habitat`
- **Acceptance criteria:**
  - AC-FR-UPC-03-1: Given two event landings with the section, then both show the same upcoming entries in the same order.
- **Open questions:** Q-27

#### FR-UPC-04
**Priority:** Could · **Classification:** HYPOTHESIS · **Confidence:** LOW

- **Description:** An "Informez moi" button lets a member ask to be notified when an upcoming sale starts.
- **Source module:** M04
- **Evidence:** BR-04-06; `a11y/page_sports`
- **Acceptance criteria:**
  - AC-FR-UPC-04-1: Given an upcoming tile, then an "Informez moi" button is shown.
- **Open questions:** Q-01; Q-47
- **Note:** The button is CONFIRMED. Its behaviour (channel, data stored, confirmation) is OUT OF SCOPE: it was never clicked.

### FR-CAT: Product catalogue: listing, search filters & sort

#### FR-CAT-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** One product-listing component serves campaign, category, brand, search and recommendation ("top picks") contexts.
- **Source module:** M06
- **Evidence:** `a11y/campaign_desktop`; `a11y/category_80000687_outline`; `a11y/brand_H1X`; `a11y/search_results`
- **Acceptance criteria:**
  - AC-FR-CAT-01-1: Given each context (`/campaigns/{id}`, `/articles/categories/{id}`, `/brands/{code}`, `/search-catalog`, `/campaigns/top-picks/articles`), then the same tile, filter and sort controls are rendered.

#### FR-CAT-02
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** A product tile shows brand, model, type and colour as one link, the RRP labelled "Prix de vente conseillé", the sale price labelled "Prix de vente", footnote markers and two images.
- **Source module:** M06, M12
- **Evidence:** BR-12-01; `a11y/campaign_desktop`; `shot/campaign_desktop`
- **Acceptance criteria:**
  - AC-FR-CAT-02-1: Given any listing, then each tile shows the RRP and the sale price with their labels, and links to the article URL (FR-CMP-06).

#### FR-CAT-03
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** A "Tri" dialog offers five single-choice sort options: Le plus récent, Préférés (default), Prix croissant, Prix décroissant, Réduction en %.
- **Source module:** M06
- **Evidence:** BR-06-01; BR-06-02; `a11y/campaign_sort_open`
- **Acceptance criteria:**
  - AC-FR-CAT-03-1: Given a fresh listing, then "Préférés" is selected.
  - AC-FR-CAT-03-2: When the member opens "Tri", then exactly the five options are shown as radios, with Retirer, Fermer and Appliquer.
- **Open questions:** Q-43
- **Note:** The meaning of "Préférés" is UNKNOWN. The result of applying a sort wasn't exercised.

#### FR-CAT-04
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Filters are catégories, Taille, Marque, Composition, Couleur and Prix. The set depends on the context: category listings add "Longueur".
- **Source module:** M06
- **Evidence:** BR-06-08; `a11y/campaign_desktop`; `a11y/category_80000687_outline`
- **Acceptance criteria:**
  - AC-FR-CAT-04-1: Given a campaign listing, then the filter group shows catégories, Taille, Marque, Composition, Couleur, Prix and Tri.
  - AC-FR-CAT-04-2: Given a category listing, then "Longueur" is also shown.
- **Open questions:** Q-14
- **Note:** The contents of Marque, Couleur, Composition and catégories are UNKNOWN.

#### FR-CAT-05
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Filter dialogs apply on explicit confirmation. The confirm button shows the resulting count ("Afficher N résultats").
- **Source module:** M06
- **Evidence:** BR-06-03; `a11y/campaign_size_open`; `a11y/campaign_price_open`
- **Acceptance criteria:**
  - AC-FR-CAT-05-1: Given an open filter dialog, then the confirm button reads "Afficher <N> résultats" and the listing doesn't change until it is pressed.
- **Note:** A live count update while options change is HYPOTHESIS.

#### FR-CAT-06
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** Size values are grouped in tabs by size system (e.g. "Chaussures") and are multi-select.
- **Source module:** M06
- **Evidence:** BR-06-04; `a11y/campaign_size_open`
- **Acceptance criteria:**
  - AC-FR-CAT-06-1: Given a shoe campaign, when "Taille" opens, then a "Chaussures" tab lists size checkboxes, including half sizes.
- **Note:** Only one tab was seen; more tabs in clothing contexts are HYPOTHESIS.

#### FR-CAT-07
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** The price filter has minimum and maximum inputs in euros plus two sliders. They default to the context's lowest and highest prices.
- **Source module:** M06
- **Evidence:** BR-06-05; `a11y/campaign_price_open`
- **Acceptance criteria:**
  - AC-FR-CAT-07-1: Given a listing whose prices span 8–299 €, when "Prix" opens, then Prix minimum shows 8 and Prix maximum shows 299.
- **Note:** Computing the bounds from the current result set is HYPOTHESIS (BR-06-05). Use "Max", not "Maks" (NFR-I18N-01).

#### FR-CAT-08
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** "Retirer" clears one filter and is disabled when nothing is selected. A filter status line ("Vos filtres (N Articles)") offers "Réinitialiser".
- **Source module:** M06
- **Evidence:** BR-06-06; `a11y/campaign_sort_open`; `a11y/brand_H1X`; `a11y/category_80000687_outline`
- **Acceptance criteria:**
  - AC-FR-CAT-08-1: Given a dialog with no selection, then "Retirer" is disabled.
  - AC-FR-CAT-08-2: Given active filters, then a status line shows "Vos filtres" with the article count (reference: "Vos filtres(4 Articles):"), plus "Réinitialiser".

#### FR-CAT-09
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** A listing shows 60 products initially.
- **Source module:** M06
- **Evidence:** BR-06-07; `a11y/campaign_scrolled`
- **Acceptance criteria:**
  - AC-FR-CAT-09-1: Given a context with more than 60 products, then 60 tiles are rendered on first load.
- **Open questions:** Q-11; Q-60
- **Note:** How further products are reached (infinite scroll or a control) is UNKNOWN.

### FR-PDP: Product detail page

#### FR-PDP-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The product page shows an image gallery (tabs), the brand as a heading linking to the brand page, type and colour, the sale price with the RRP struck through, the campaign countdown, and accordions Détails (with "Référence"), Taille & Coupe, Composition and manufacturing information.
- **Source module:** M07
- **Evidence:** BR-07-01; `a11y/product_desktop`; `shot/product_desktop`
- **Acceptance criteria:**
  - AC-FR-PDP-01-1: Given an article URL, then all listed elements are present.
  - AC-FR-PDP-01-2: When the member activates the brand heading, then `/brands/{code}` opens.

#### FR-PDP-02
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** A size must be selected before adding to the cart.
- **Source module:** M07
- **Evidence:** BR-07-06; `a11y/pdp_add_no_size`
- **Acceptance criteria:**
  - AC-FR-PDP-02-1: Given no size selected, when the member clicks "Ajouter au panier", then the alert "Choisissez une taille" is shown and nothing is added.

#### FR-PDP-03
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Adding with a size selected reserves the unit, shows a success message and updates the cart button with the count and a 20-minute countdown.
- **Source module:** M07, M14
- **Evidence:** BR-07-07; BR-14-02; `a11y/product_desktop`; `a11y/pdp_added`
- **Acceptance criteria:**
  - AC-FR-PDP-03-1: Given size 39 1/3 selected, when the member clicks "Ajouter au panier", then a success alert is shown and the cart button reads "Votre panier - 1 Article - 20 minutes et 0 secondes restantes".
- **Open questions:** Q-51
- **Note:** The reference copy is "Le produit a été mis à jour avec succès." ("updated"). The PO picks the wording.

#### FR-PDP-04
**Priority:** Could · **Classification:** HYPOTHESIS · **Confidence:** MEDIUM

- **Description:** After adding, the PDP no longer offers "Ajouter au panier" for that article.
- **Source module:** M07
- **Evidence:** BR-07-08; `a11y/pdp_added`
- **Acceptance criteria:**
  - AC-FR-PDP-04-1: Given the article was just added, then "Ajouter au panier" is not offered again for it.
- **Open questions:** Q-46
- **Note:** Disappearance is CONFIRMED. The rule (one unit per SKU per member, or while reserved) is HYPOTHESIS.

#### FR-PDP-05
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Each size can show a live low-stock label "Plus que N disponibles".
- **Source module:** M07, M13
- **Evidence:** BR-07-02; BR-13-01; `a11y/product_desktop`; `a11y/pdp_added`
- **Acceptance criteria:**
  - AC-FR-PDP-05-1: Given a size whose stock is at or below the threshold, then its label reads "Plus que <N> disponible(s)" with N the current available units.
  - AC-FR-PDP-05-2: Given stock changes, then N reflects the new value on the next render.
- **Open questions:** Q-26
- **Note:** The threshold is UNKNOWN (N = 3 and 1 seen). Correct pluralisation required (NFR-I18N-02).

#### FR-PDP-06
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** The PDP shows a delivery date window ("Délai De Livraison").
- **Source module:** M07, M17
- **Evidence:** BR-07-04; BR-17-02; `a11y/product_desktop`
- **Acceptance criteria:**
  - AC-FR-PDP-06-1: Given an article, then a delivery window "<date> - <date>" is shown.
- **Open questions:** Q-49
- **Note:** One observation: 4 days wide, starting 2 days after the visit. Help says each product has its own delivery time.

#### FR-PDP-07
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The PDP shows a free-returns statement with the return period.
- **Source module:** M07, M17
- **Evidence:** BR-07-03; BR-17-01; BR-17-04; `a11y/product_desktop`
- **Acceptance criteria:**
  - AC-FR-PDP-07-1: Given an article, then the returns statement shows the single configured return period, identical to the help and terms pages.
- **Open questions:** Q-31
- **Note:** The reference publishes 30 / 31 / 14 days. The value is blocked on Q-31 and must be one source of truth.

#### FR-PDP-08
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Sizes are shown as radios in the size system of the article (French sizes, including thirds) with a "Plus de tailles" option.
- **Source module:** M07
- **Evidence:** `a11y/product_desktop`
- **Acceptance criteria:**
  - AC-FR-PDP-08-1: Given a shoe article, then sizes such as "38 2/3" and "39 1/3" are selectable radios.

### FR-SRC: Search

#### FR-SRC-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The header search is a combobox that suggests brands as the member types.
- **Source module:** M08
- **Evidence:** BR-08-01; `a11y/search_suggest`; `shot/search_suggest`
- **Acceptance criteria:**
  - AC-FR-SRC-01-1: Given the member types "nike", then a listbox of brand suggestions (e.g. Nike, Nike Sportswear, Nike SB) is shown.
- **Open questions:** Q-44
- **Note:** Free-text, category and campaign suggestions, typo tolerance and minimum length are UNKNOWN.

#### FR-SRC-02
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Selecting a suggestion opens a search listing (`/search-catalog?query=…`) that spans campaigns.
- **Source module:** M08, M06
- **Evidence:** BR-08-02; `a11y/search_results`
- **Acceptance criteria:**
  - AC-FR-SRC-02-1: When the member selects "Nike Sportswear", then a listing with products from several campaigns opens, using the FR-CAT-01 component.
- **Open questions:** Q-44; Q-62

### FR-BRD: Brand management (display)

#### FR-BRD-01
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** A brand page `/brands/{code}` lists the brand's products across campaigns, and is reachable from the PDP brand heading.
- **Source module:** M09
- **Evidence:** BR-09-01; `a11y/brand_H1X`; `shot/brand_H1X`
- **Acceptance criteria:**
  - AC-FR-BRD-01-1: Given brand code B, then `/brands/B` lists products of B from several campaigns.

#### FR-BRD-02
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The brand page breadcrumb shows the brand name, not the internal code.
- **Source module:** M09
- **Evidence:** BR-09-02; `a11y/brand_H1X`
- **Acceptance criteria:**
  - AC-FR-BRD-02-1: Given `/brands/H1X`, then the breadcrumb shows the brand's display name.
- **Note:** Defect avoidance: the reference shows "H1X".

### FR-PRC: Product pricing

#### FR-PRC-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Prices are shown in euros, VAT included, as a sale price against the RRP. Discounts are expressed vs the RRP.
- **Source module:** M12
- **Evidence:** BR-12-01; BR-12-02; `a11y/product_desktop`; `a11y/public_terms`
- **Acceptance criteria:**
  - AC-FR-PRC-01-1: Given an article, then the sale price and the RRP are both shown, in euros with French formatting (e.g. "115,00 €").

#### FR-PRC-02
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The packaging and handling fee is disclosed: the cart total says it excludes it, and the help content states the amount per order.
- **Source module:** M12, M14
- **Evidence:** BR-12-03; BR-12-06; BR-14-05; `a11y/cart_with_item`; `a11y/public_help_livraison`
- **Acceptance criteria:**
  - AC-FR-PRC-02-1: Given a non-empty cart, then the summary says "Taxe sur la valeur ajoutée incluse, hors emballage & manutention".
- **Note:** Charging the fee belongs to checkout (M21, OUT OF SCOPE). The reference amount is 3,90 € per order.

### FR-STK: Stock indicators

#### FR-STK-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** A reserved unit is unavailable to other shoppers. Listing tiles can show a "Réservé" state.
- **Source module:** M13, M14
- **Evidence:** BR-13-02; BR-14-03; `a11y/brand_H1X`; `a11y/cart_remove_confirm`
- **Acceptance criteria:**
  - AC-FR-STK-01-1: Given member A reserved the last unit of a size, then member B can't reserve that unit.
  - AC-FR-STK-01-2: Given the reserved state applies to a tile, then the tile shows "Réservé".
- **Open questions:** Q-45
- **Note:** When exactly a tile shows "Réservé" (all units reserved?) is UNKNOWN.

#### FR-STK-02
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Quantities are limited and ended or sold-out items are not restocked.
- **Source module:** M13
- **Evidence:** BR-13-04; BR-03-16; `a11y/public_help_ventes`
- **Acceptance criteria:**
  - AC-FR-STK-02-1: Given an article sold out, then its stock is not replenished within the campaign.

#### FR-STK-03
**Priority:** Could · **Classification:** HYPOTHESIS · **Confidence:** LOW

- **Description:** A "Dernières heures" badge marks products whose campaign ends soon.
- **Source module:** M13
- **Evidence:** BR-13-03; `a11y/search_results`
- **Acceptance criteria:**
  - AC-FR-STK-03-1: Given a campaign ending within the threshold, then its product tiles show "Dernières heures".
- **Open questions:** Q-36
- **Note:** The badge is CONFIRMED. Its trigger and threshold are HYPOTHESIS/UNKNOWN.

### FR-CRT: Cart (authorized scope)

#### FR-CRT-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The cart is a drawer dialog opened from the header, without its own URL.
- **Source module:** M14
- **Evidence:** `a11y/cart_empty`; `a11y/cart_with_item`
- **Acceptance criteria:**
  - AC-FR-CRT-01-1: When the member activates "Votre panier", then a dialog opens and the page URL doesn't change.
  - AC-FR-CRT-01-2: Given the dialog, then close buttons dismiss it.

#### FR-CRT-02
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Adding an item reserves it for 20 minutes. The countdown is shown in the header and in the cart title.
- **Source module:** M14
- **Evidence:** BR-14-01; BR-14-02; `a11y/cart_with_item`; `a11y/pdp_added`
- **Acceptance criteria:**
  - AC-FR-CRT-02-1: Given an item added at T, then the cart title reads "Votre panier - 1 Article - <remaining> restantes" with remaining = 20 min - (now - T).
- **Open questions:** Q-32
- **Note:** The timer rule with several items (per item or shared, reset on add) is UNKNOWN.

#### FR-CRT-03
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Cart lines are grouped under "Réservé". Each shows a brand link to the PDP, type, size, RRP, discount %, price, image and a remove button.
- **Source module:** M14
- **Evidence:** BR-14-01; BR-14-04; `a11y/cart_with_item`
- **Acceptance criteria:**
  - AC-FR-CRT-03-1: Given one reserved item, then its line shows all listed fields under the "Réservé" label.

#### FR-CRT-04
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Removing an item needs confirmation and releases the unit to other shoppers.
- **Source module:** M14
- **Evidence:** BR-14-03; `a11y/cart_remove_confirm`; `a11y/cart_after_remove`
- **Acceptance criteria:**
  - AC-FR-CRT-04-1: When the member clicks remove, then the alertdialog "Retirer l’article du panier" explains the unit becomes available to others, with Supprimer and Annuler.
  - AC-FR-CRT-04-2: When the member confirms "Supprimer", then the item leaves the cart and the unit is available again.
  - AC-FR-CRT-04-3: When the member chooses "Annuler", then the cart is unchanged.

#### FR-CRT-05
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The cart summary shows the total saving vs RRP and the total incl. VAT, excluding packaging and handling.
- **Source module:** M14, M12
- **Evidence:** BR-14-04; BR-14-05; `a11y/cart_with_item`
- **Acceptance criteria:**
  - AC-FR-CRT-05-1: Given one line at 115,00 € with RRP 250,00 €, then the summary shows "Vous économisez 135,00 €", "Somme totale 115,00 €" and the line shows "-54%".

#### FR-CRT-06
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** An empty cart shows "Vous n’avez aucun article réservé dans votre panier."
- **Source module:** M14
- **Evidence:** `a11y/cart_empty`; `a11y/cart_after_remove`
- **Acceptance criteria:**
  - AC-FR-CRT-06-1: Given no reserved items, when the cart opens, then the empty-state text is shown.

#### FR-CRT-07
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** A "Commander (N Article)" button hands the cart over to checkout.
- **Source module:** M14, M21
- **Evidence:** BR-21-01; `a11y/cart_with_item`
- **Acceptance criteria:**
  - AC-FR-CRT-07-1: Given a non-empty cart, then "Commander (<N> Article)" is shown and leads to the checkout entry point.
- **Open questions:** Q-04
- **Note:** The button is CONFIRMED. Everything after it is OUT OF SCOPE (M21) and needs a separate specification.

#### FR-CRT-08
**Priority:** Could · **Classification:** HYPOTHESIS · **Confidence:** MEDIUM

- **Description:** When the reservation timer reaches zero the unit is released and the item leaves the cart.
- **Source module:** M14
- **Evidence:** BR-14-06
- **Acceptance criteria:**
  - AC-FR-CRT-08-1: Given a reservation older than 20 minutes, then the item is no longer in the cart and the unit is available to others.
- **Open questions:** Q-32; Q-37
- **Note:** Not observed. PO to confirm; likely promoted to Must, since the reservation has no meaning without it.

### FR-ACC: Customer account (authorized scope)

#### FR-ACC-01
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The account area has a navigation with Commandes, Retours (Rétractation), Données personnelles, Adresses, Paramètres de la newsletter, Mes filtres and Aide et FAQ.
- **Source module:** M15
- **Evidence:** `a11y/account_menu`; `a11y/account_overview`
- **Acceptance criteria:**
  - AC-FR-ACC-01-1: Given a logged-in member, then the "Votre compte" navigation lists the sections, each with its own `/myaccount/…` URL.
- **Open questions:** Q-41
- **Note:** The personal-details and address forms weren't opened (PII), so their fields are UNKNOWN. The external rewards link is OUT OF SCOPE.

#### FR-ACC-02
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The account overview shows a greeting, a recent-updates list with an empty state, and a call to action to the sales.
- **Source module:** M15
- **Evidence:** `a11y/account_overview`
- **Acceptance criteria:**
  - AC-FR-ACC-02-1: Given no updates, then "Vous n'avez pas d'actualisations" and "Accédez à nos ventes" are shown.

#### FR-ACC-03
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** The orders page lists the member's orders, with an empty state.
- **Source module:** M15
- **Evidence:** `a11y/account_orders`
- **Acceptance criteria:**
  - AC-FR-ACC-03-1: Given no orders, then "Une fois vos commandes passées, vous pouvez les voir et les gérer ici." and "Achetez dès maintenant" are shown.
- **Note:** The order list and detail layout is UNKNOWN (no orders on the account).

#### FR-ACC-04
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** Returns are self-service from the account. A separate withdrawal entry point exists.
- **Source module:** M15, M17
- **Evidence:** BR-15-06; `a11y/account_returns`
- **Acceptance criteria:**
  - AC-FR-ACC-04-1: Given the returns page, then "commencez un retour" leads to `/myaccount/return/selection`, and the empty state reads "Pas de retours récents".
  - AC-FR-ACC-04-2: Given the footer "Rétractation" link, then it leads to the return selection in withdrawal mode.
- **Open questions:** Q-31; Q-38
- **Note:** The return flow steps are UNKNOWN (nothing to return).

#### FR-ACC-05
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Newsletter settings offer daily and/or weekly sales emails and a separate personalisation/tracking opt-in. "Enregistrer" is disabled until something changes.
- **Source module:** M15
- **Evidence:** BR-15-03; BR-15-07; BR-04-06; `a11y/account_newsletter`
- **Acceptance criteria:**
  - AC-FR-ACC-05-1: Given the newsletter page, then "Ventes du jour", "Ventes de la semaine" and "Personnalisez vos newsletters" are independent checkboxes.
  - AC-FR-ACC-05-2: Given no change, then "Enregistrer" is disabled.

#### FR-ACC-06
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Unsubscribing requires a reason.
- **Source module:** M15
- **Evidence:** BR-15-04; `a11y/account_newsletter`
- **Acceptance criteria:**
  - AC-FR-ACC-06-1: Given no reason selected, then "Se désabonner" is disabled; once a reason is selected it is enabled.

#### FR-ACC-07
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Members can save size preferences per gender and garment group, in EU or US size systems.
- **Source module:** M15
- **Evidence:** BR-15-05; BR-15-07; `a11y/account_filter`
- **Acceptance criteria:**
  - AC-FR-ACC-07-1: Given "Mes filtres", then tabs Femmes/Hommes show groups (Chaussures, Hauts / Tops, Pantalons, Robes et jupes, Lingerie et maillot de bain) with size-system radios and size checkboxes.
  - AC-FR-ACC-07-2: Given no change, then "Enregistrer" is disabled.
- **Open questions:** Q-39
- **Note:** Whether saved sizes pre-filter listings is HYPOTHESIS.

#### FR-ACC-08
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Membership is free, limited to one account per customer, and members must be 18 or older.
- **Source module:** M15
- **Evidence:** BR-15-01; BR-15-02; `a11y/public_terms`; `a11y/public_help_membre`
- **Acceptance criteria:**
  - AC-FR-ACC-08-1: Given a registration attempt by a customer who already has an account, then it is refused.
  - AC-FR-ACC-08-2: Given registration, then the member confirms they are 18 or older.
- **Open questions:** Q-03; Q-40; Q-41
- **Note:** The rule comes from the CGV. The registration UI and login methods were not observed.

### FR-REC: Recommendations

#### FR-REC-01
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The default event shows personal rails "Recommandé pour vous" (with "Voir tout" to a top-picks listing) and "Continuez votre shopping" (tabbed carousels). Vertical events don't.
- **Source module:** M10
- **Evidence:** BR-05-06; BR-10-01; BR-10-03; `a11y/home_scrolled`; `a11y/page_femme_scrolled`
- **Acceptance criteria:**
  - AC-FR-REC-01-1: Given `/event`, then both rails are shown, and "Voir tout" opens `/campaigns/top-picks/articles`.
  - AC-FR-REC-01-2: Given a vertical event, then neither rail is shown.
- **Open questions:** Q-29
- **Note:** The recommendation logic is UNKNOWN. That "Continuez" uses browsing history is HYPOTHESIS.

#### FR-REC-02
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** Every event shows "Populaire dans votre région" with 12 products. The set varies by event and over time.
- **Source module:** M10
- **Evidence:** BR-05-07; BR-10-02; `a11y/home_scrolled`; `a11y/page_homme_scrolled`
- **Acceptance criteria:**
  - AC-FR-REC-02-1: Given any event landing, then the rail shows 12 product tiles linking to article URLs.
- **Open questions:** Q-29
- **Note:** Regional bestseller ranking with an audience filter is HYPOTHESIS.

### FR-DLV: Delivery & returns information

#### FR-DLV-01
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Help content describes delivery: carriers, delivery days, delivery area, home or relay-point delivery, express for some articles, no split or merged orders, no address change after validation, and lead time in working days from payment.
- **Source module:** M17, M18
- **Evidence:** BR-17-03; BR-17-05; BR-17-06; BR-17-07; BR-17-08; `a11y/public_help_livraison`
- **Acceptance criteria:**
  - AC-FR-DLV-01-1: Given the delivery help category, then each listed topic has an article.
- **Open questions:** Q-49
- **Note:** The values are the reference's policy. The new app's own carriers, areas and options are PO decisions.

#### FR-DLV-02
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Returns are free, and one return period is published consistently on the PDP, the help pages and the terms.
- **Source module:** M17
- **Evidence:** BR-17-01; BR-17-04; `a11y/public_help_retour_delai`; `a11y/public_terms`
- **Acceptance criteria:**
  - AC-FR-DLV-02-1: Given the PDP, the help article and the terms, then all three state the same period.
- **Open questions:** Q-31
- **Note:** Defect avoidance: the reference shows 30, 31 and 14 days in different places.

### FR-CNT: Footer, legal, help & consent

#### FR-CNT-01
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** A footer gives legal, help, payment, carrier and app links, and defines the footnote markers (* ¹ ³) used site-wide.
- **Source module:** M18
- **Evidence:** BR-18-01; `a11y/home_scrolled`
- **Acceptance criteria:**
  - AC-FR-CNT-01-1: Given a page with footnote markers, then the footer defines every marker used.

#### FR-CNT-02
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Help articles are organised by category at `/help/<category>/<article-slug>`.
- **Source module:** M18
- **Evidence:** BR-18-02; `a11y/public_help_home`
- **Acceptance criteria:**
  - AC-FR-CNT-02-1: Given the help home, then categories list articles at `/help/<category>/<slug>`.

#### FR-CNT-03
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Terms of sale (CGV) are published. They name the contracting party and the language of contract, and give the consumer mediator.
- **Source module:** M18
- **Evidence:** BR-18-03; BR-18-04; `a11y/public_terms`
- **Acceptance criteria:**
  - AC-FR-CNT-03-1: Given the terms page, then the contracting entity, the contract language and the mediator are stated.
- **Open questions:** Q-48
- **Note:** The new app's legal entity and mediator are PO/legal decisions. The reference values must not be copied.

#### FR-CNT-04
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** A consent-management entry point is reachable from the footer.
- **Source module:** M19
- **Evidence:** `a11y/home_scrolled`; `har #7`; `har #29`
- **Acceptance criteria:**
  - AC-FR-CNT-04-1: Given the footer, then a link opens the consent settings.
- **Note:** The CMP behaviour is UNKNOWN (blocked in the capture).

#### FR-CNT-05
**Priority:** Could · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** The footer links to the mobile apps.
- **Source module:** M22
- **Evidence:** BR-22-01; `a11y/home_scrolled`
- **Acceptance criteria:**
  - AC-FR-CNT-05-1: Given the footer, then the app-store badges are shown when apps exist.
- **Note:** There are no apps for the new product yet. Kept as Could.

### FR-ANL: Attribution

#### FR-ANL-01
**Priority:** Could · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Product links record the surface they came from (e.g. `navigationSource=catalog&catalog-type=campaign`) for attribution.
- **Source module:** M20
- **Evidence:** BR-20-01; `a11y/campaign_desktop`
- **Acceptance criteria:**
  - AC-FR-ANL-01-1: Given a product link in a campaign listing, then it carries the source surface as parameters.
- **Note:** Don't copy the `highligts_carousel` typo.

### FR-OOS: Out of scope (recorded gaps)

#### FR-OOS-01
**Priority:** Won't (this spec) · **Classification:** OUT OF SCOPE · **Confidence:** HIGH

- **Description:** Checkout and payment: addresses at checkout, payment methods, fee application, vouchers, order confirmation, invoices.
- **Source module:** M21
- **Evidence:** BR-21-01; BR-21-02; BR-21-03; BR-21-04; BR-21-05
- **Acceptance criteria:**
  - AC-FR-OOS-01-1: Not specified here. A separate checkout specification is required (Q-04).
- **Open questions:** Q-04
- **Note:** The BR-21-* rules from the CGV are reference input only.

#### FR-OOS-02
**Priority:** Won't (this spec) · **Classification:** OUT OF SCOPE · **Confidence:** HIGH

- **Description:** Zalando Plus loyalty activation and the rewards area.
- **Source module:** M16
- **Evidence:** BR-16-01; BR-16-02; `shot/home_plus_modal`
- **Acceptance criteria:**
  - AC-FR-OOS-02-1: Not specified. "Activez Plus" was not authorized, and the program belongs to the Zalando group.
- **Open questions:** Q-01

## 4. Non-functional requirements

### NFR-TECH: Technical constraints

#### NFR-TECH-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Stack: Laravel 13 on PHP 8.3+, React 19, MySQL, REST API, Docker.
- **Source module:** Project
- **Evidence:** CLAUDE.md.txt (Target Technology)
- **Acceptance criteria:**
  - AC-NFR-TECH-01-1: The solution architecture uses only the listed stack, unless the PO approves a change.
- **Note:** A project constraint set by the user. It is **not** inferred from the reference, whose backend is UNKNOWN (network-analysis N-08).

### NFR-I18N: Localisation

#### NFR-I18N-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** All UI text, including ARIA labels and placeholders, is in French.
- **Source module:** M06, M08
- **Evidence:** `a11y/search_suggest`; `a11y/campaign_price_open`
- **Acceptance criteria:**
  - AC-NFR-I18N-01-1: Given any page, then no label is in another language (e.g. no German ARIA labels, "Max" not "Maks").
- **Note:** Defect avoidance (open-questions §D).

#### NFR-I18N-02
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Count-dependent text uses correct singular and plural forms.
- **Source module:** M07, M14
- **Evidence:** BR-07-02; `a11y/product_desktop`
- **Acceptance criteria:**
  - AC-NFR-I18N-02-1: Given N = 1, then the label reads "Plus que 1 disponible"; given N > 1, "Plus que N disponibles".
- **Note:** Defect avoidance: the reference shows "1 disponibles".

#### NFR-I18N-03
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Prices use euros with French formatting; dates and times use French formats.
- **Source module:** M12, M03
- **Evidence:** `a11y/cart_with_item`; `a11y/home_scrolled`
- **Acceptance criteria:**
  - AC-NFR-I18N-03-1: Given a price of 115 euros, then it renders as "115,00 €". Given a start date, then it renders like "jeu. 5 novembre 06:00".

### NFR-TIME: Time

#### NFR-TIME-01
**Priority:** Must · **Classification:** UNKNOWN · **Confidence:** LOW

- **Description:** Campaign times are stored unambiguously and displayed in one defined timezone.
- **Source module:** M03, M04
- **Evidence:** BR-03-17; BR-04-04
- **Acceptance criteria:**
  - AC-NFR-TIME-01-1: Given a campaign starting at 07:00 Paris, then every member sees the start time in the defined timezone.
- **Open questions:** Q-10
- **Note:** The reference display timezone is UNKNOWN; it may follow the browser.

### NFR-A11Y: Accessibility

#### NFR-A11Y-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Keyboard and screen-reader access: skip link, dialogs with role dialog or alertdialog and close buttons, alerts announced via a live region, mega-menus operable with Enter.
- **Source module:** M01, M02, M07, M14
- **Evidence:** `a11y/home_desktop`; `a11y/menu_homme`; `a11y/pdp_add_no_size`; `a11y/cart_remove_confirm`
- **Acceptance criteria:**
  - AC-NFR-A11Y-01-1: Given keyboard-only use, then J1 (browse → PDP → add → cart → remove) can be completed.
  - AC-NFR-A11Y-01-2: Given an alert (e.g. "Choisissez une taille"), then it is announced.
- **Note:** The reference structure was observed in ARIA trees. Full WCAG conformance wasn't audited.

### NFR-SEC: Security

#### NFR-SEC-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Authenticated API calls use a server-side session (HTTP-only cookie) and identify the member from the session, never from a user ID in the URL (`/me`-style resources).
- **Source module:** Phase 03
- **Evidence:** docs/technical/authentication-analysis.md A-01; docs/technical/api-observations.md; `har #34`; `har #35`
- **Acceptance criteria:**
  - AC-NFR-SEC-01-1: Given an API request for member data, then no member identifier appears in the URL and the response depends only on the session.
- **Note:** The reference pattern is CONFIRMED for `/clients/me`. That a cookie carries the session is HYPOTHESIS (cookies were stripped from the HAR).

#### NFR-SEC-02
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** State-changing requests are protected against CSRF with a token header.
- **Source module:** Phase 03
- **Evidence:** docs/technical/authentication-analysis.md A-02; `har #36`; `har #44`
- **Acceptance criteria:**
  - AC-NFR-SEC-02-1: Given a state-changing request without a valid CSRF token, then it is rejected.
- **Note:** In the reference the header is CONFIRMED; enforcement wasn't tested.

#### NFR-SEC-03
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Personal and cart responses are not cacheable (`no-store`). HTML pages for members are not cached publicly.
- **Source module:** Phase 03
- **Evidence:** docs/technical/authentication-analysis.md A-04; `har #0`; `har #42`
- **Acceptance criteria:**
  - AC-NFR-SEC-03-1: Given a cart or account response, then it carries `Cache-Control: no-store`.

#### NFR-SEC-04
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Transport and browser hardening: HTTPS with HSTS, frame protection, a Content-Security-Policy, `nosniff`; APIs same-origin.
- **Source module:** Phase 03
- **Evidence:** docs/technical/authentication-analysis.md A-03; docs/technical/network-analysis.md N-07; `har #0`; `har #42`
- **Acceptance criteria:**
  - AC-NFR-SEC-04-1: Given any response, then HSTS is present; given HTML, then framing is restricted and a CSP is sent.

#### NFR-SEC-05
**Priority:** Could · **Classification:** HYPOTHESIS · **Confidence:** MEDIUM

- **Description:** Bot protection on high-contention actions (add to cart, launch-time traffic).
- **Source module:** M20, Phase 03
- **Evidence:** BR-20-02; docs/technical/authentication-analysis.md A-06
- **Acceptance criteria:**
  - AC-NFR-SEC-05-1: Given automated bulk add-to-cart attempts, then they are throttled or challenged.
- **Open questions:** Q-42
- **Note:** The reference runs a bot sensor (CONFIRMED presence). The need in the new app is a PO/security decision.

### NFR-PRV: Privacy

#### NFR-PRV-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Personal data sent client-side to analytics or third parties is minimised and gated by consent.
- **Source module:** M19, M20, Phase 03
- **Evidence:** docs/technical/authentication-analysis.md A-07; `har #34`; `har #36`
- **Acceptance criteria:**
  - AC-NFR-PRV-01-1: Given a member who refused analytics consent, then no personal attribute (hashed email, name, IDs) is sent to analytics or CRM tools.
- **Note:** The reference sends hashed email, first name and IDs client-side; consent gating was not observable.

### NFR-CON: Consistency & concurrency

#### NFR-CON-01
**Priority:** Must · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Stock reservation is consistent under concurrent adds: a unit is never reserved by two members, and stock never goes negative.
- **Source module:** M13, M14
- **Evidence:** BR-13-02; BR-13-04; BR-14-03
- **Acceptance criteria:**
  - AC-NFR-CON-01-1: Given two members adding the last unit at the same moment, then exactly one reservation succeeds and the other member is told the size is unavailable.
- **Note:** Derived from CONFIRMED rules. The error copy for the losing member is UNKNOWN (not observed).

### NFR-CACHE: Caching

#### NFR-CACHE-01
**Priority:** Should · **Classification:** CONFIRMED · **Confidence:** HIGH

- **Description:** Static assets are content-versioned and cached long-term (immutable). HTML is not cached.
- **Source module:** Phase 03
- **Evidence:** docs/technical/frontend-analysis.md F-09; `har #4`; `har #26`
- **Acceptance criteria:**
  - AC-NFR-CACHE-01-1: Given a built asset, then its file name contains a content hash and it is served with a one-year immutable cache.

### NFR-OBS: Observability

#### NFR-OBS-01
**Priority:** Could · **Classification:** CONFIRMED · **Confidence:** MEDIUM

- **Description:** Front-end errors and performance are monitored, with request IDs that can be traced end to end.
- **Source module:** Phase 03
- **Evidence:** docs/technical/frontend-analysis.md F-10; docs/technical/network-analysis.md N-08
- **Acceptance criteria:**
  - AC-NFR-OBS-01-1: Given a failed API call, then it can be correlated with a server log entry by request ID.
- **Note:** The reference has RUM, OTLP and trace headers. The tooling choice is open.

### NFR-PERF: Performance

#### NFR-PERF-01
**Priority:** Must · **Classification:** UNKNOWN · **Confidence:** LOW

- **Description:** Performance and launch-time load targets.
- **Source module:** Phase 03
- **Evidence:** docs/technical/network-analysis.md N-05
- **Acceptance criteria:**
  - AC-NFR-PERF-01-1: Targets to be set by the PO before design (Q-42).
- **Open questions:** Q-42
- **Note:** The reference sample (≈ 3 s server wait, one warm-cache load) can't set a target.

### NFR-RESP: Responsive

#### NFR-RESP-01
**Priority:** Must · **Classification:** UNKNOWN · **Confidence:** LOW

- **Description:** Responsive and mobile layout.
- **Source module:** All
- **Evidence:** `a11y/viewport390_connect_no_reflow`
- **Acceptance criteria:**
  - AC-NFR-RESP-01-1: Breakpoints and mobile behaviour to be specified (Q-15).
- **Open questions:** Q-15
- **Note:** Only desktop 1280×720 was observed.

## 5. Requirement counts

| Classification | Count |
|---|---|
| CONFIRMED | 82 |
| HYPOTHESIS | 8 |
| OUT OF SCOPE | 2 |
| UNKNOWN | 3 |

| Priority (proposed) | Count |
|---|---|
| Could | 11 |
| Must | 50 |
| Should | 32 |
| Won't (this spec) | 2 |
