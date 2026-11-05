# Acceptance criteria — Phase 04

Every functional and non-functional requirement in [SRS.md](SRS.md) has at least one criterion here, with the same ID scheme: `AC-<requirement ID>-<n>`.

**Rules used:**
- Criteria use the **observed French UI copy** where it was captured, so they can be checked against the evidence.
- No criteria were written for behaviour that wasn't observed: error states, zero results, timer expiry, the ended-campaign URL. Where a requirement is HYPOTHESIS, its criterion describes the behaviour **to validate with the PO**, and is marked so.
- `<…>` marks a value that varies at runtime.

## FR-NAV: Header & navigation

**FR-NAV-01**
- AC-FR-NAV-01-1: Given any event, campaign, product, brand or search page, then the header shows the logo, search, "Compte" and "Votre panier".
- AC-FR-NAV-01-2: Given the header, when the member activates the logo, then the default event page (`/event`) opens.
- AC-FR-NAV-01-3: Given keyboard focus at the top of a page, then a skip link "Aller au contenu principal" moves focus to the main content.

**FR-NAV-02**
- AC-FR-NAV-02-1: Given any page, then the USP bar shows the configured claims, and a discount claim carries the "*" footnote defined as "Par rapport au prix de vente conseillé".

**FR-NAV-03**
- AC-FR-NAV-03-1: Given an event page, then the main nav shows the configured verticals in order, each linking to `/event/{id}`.
- AC-FR-NAV-03-2: Given a campaign, product, brand or search page, then the main nav is not shown.

**FR-NAV-04**
- AC-FR-NAV-04-1: Given a vertical with a mega-menu, when the member hovers its chevron or presses Enter on it, then the panel opens with category columns linking to `/articles/categories/{id}`.
- AC-FR-NAV-04-2: Given a vertical configured without a mega-menu (as Premium in the reference), then no chevron is shown.

**FR-NAV-05**
- AC-FR-NAV-05-1: Given one reserved item, then the cart button's accessible name reads "Votre panier - 1 Article - <mm> minutes et <ss> secondes restantes".
- AC-FR-NAV-05-2: Given an empty cart, then the cart button shows no count and no timer.

## FR-EVT: Event landings

**FR-EVT-01**
- AC-FR-EVT-01-1: Given an existing event ID, when `/event/{id}` is requested, then that event's landing is rendered.
- AC-FR-EVT-01-2: Given `/event`, then the default event is rendered with the same campaign set as the "Toutes les ventes" event.

**FR-EVT-02**
- AC-FR-EVT-02-1: Given a campaign assigned to events A and B, when it is live, then it appears in the grids of A, B and "all sales".
- AC-FR-EVT-02-2: Given an upcoming campaign, then it does not appear in any event grid.

**FR-EVT-03** — *HYPOTHESIS: validate before use*
- AC-FR-EVT-03-1: Given an ordering set for an event, then the grid renders campaigns in that order.

**FR-EVT-04**
- AC-FR-EVT-04-1: Given a vertical event with a hero configured, then exactly one hero is shown above the grid.
- AC-FR-EVT-04-2: Given `/event`, then no hero is shown.

**FR-EVT-05** — *HYPOTHESIS: validate before use*
- AC-FR-EVT-05-1: Given an event using the curated template, then neither the upcoming section nor the footer is rendered.

## FR-CMP: Campaign lifecycle

**FR-CMP-01**
- AC-FR-CMP-01-1: Given a live campaign, then its tile shows name, image, headline and "Se termine dans <countdown>".
- AC-FR-CMP-01-2: When the member activates the tile heading, then `/campaigns/{id}` opens.

**FR-CMP-02**
- AC-FR-CMP-02-1: Given ≥ 1 day remaining, then the countdown reads like "2 jours 7 heures 25 minutes".
- AC-FR-CMP-02-2: Given < 1 day remaining, then the countdown shows hours, minutes and seconds.

**FR-CMP-03**
- AC-FR-CMP-03-1: Given a campaign with a percentage headline, then the tile shows "Économisez jusqu'à* N%" and the page footnote defines "*" vs the RRP.

**FR-CMP-04**
- AC-FR-CMP-04-1: Given a campaign flagged "Special", then its tile shows "Special" and no percentage.

**FR-CMP-05**
- AC-FR-CMP-05-1: Given an upcoming campaign whose start time passes, then it is removed from "Prochainement" and appears in its event grids.
- AC-FR-CMP-05-2: Given a live campaign whose end time passes, then it no longer appears in any grid.

**FR-CMP-06**
- AC-FR-CMP-06-1: Given a product tile in any listing (campaign, category, brand, search, rail), then its link has the form `/campaigns/{campaignId}/articles/{sku}`.

**FR-CMP-07**
- AC-FR-CMP-07-1: Given model M assigned to campaigns C1 and C2, then two distinct article URLs exist, one under each campaign.

**FR-CMP-08** — *HYPOTHESIS: validate before use*
- AC-FR-CMP-08-1: Given an article whose campaign has ended, when the member tries to add it, then the add is refused.

**FR-CMP-09**
- AC-FR-CMP-09-1: Given a campaign with start S and end E, then it is upcoming before S, live from S to E, and ended after E.
- AC-FR-CMP-09-2: Given the launch slots configured by the PO, then a campaign can be scheduled at any of them.

**FR-CMP-10**
- AC-FR-CMP-10-1: Given a campaign named "Looks enfant à moins de 40 €" with articles of several brands, then it renders like any brand campaign.

## FR-UPC: Upcoming sales

**FR-UPC-01**
- AC-FR-UPC-01-1: Given an upcoming campaign, then it appears in "Prochainement" with name, image and start time.
- AC-FR-UPC-01-2: Given an upcoming tile, then it contains no link to the campaign page.

**FR-UPC-02**
- AC-FR-UPC-02-1: Given a start within the threshold, then the tile reads "Démarre dans …".
- AC-FR-UPC-02-2: Given a start beyond the threshold, then the tile reads "Démarre le <day> <date> <hh:mm>".

**FR-UPC-03**
- AC-FR-UPC-03-1: Given two event landings with the section, then both show the same upcoming entries in the same order.

**FR-UPC-04** — *HYPOTHESIS: validate before use*
- AC-FR-UPC-04-1: Given an upcoming tile, then an "Informez moi" button is shown.

## FR-CAT: Product catalogue: listing, search filters & sort

**FR-CAT-01**
- AC-FR-CAT-01-1: Given each context (`/campaigns/{id}`, `/articles/categories/{id}`, `/brands/{code}`, `/search-catalog`, `/campaigns/top-picks/articles`), then the same tile, filter and sort controls are rendered.

**FR-CAT-02**
- AC-FR-CAT-02-1: Given any listing, then each tile shows the RRP and the sale price with their labels, and links to the article URL (FR-CMP-06).

**FR-CAT-03**
- AC-FR-CAT-03-1: Given a fresh listing, then "Préférés" is selected.
- AC-FR-CAT-03-2: When the member opens "Tri", then exactly the five options are shown as radios, with Retirer, Fermer and Appliquer.

**FR-CAT-04**
- AC-FR-CAT-04-1: Given a campaign listing, then the filter group shows catégories, Taille, Marque, Composition, Couleur, Prix and Tri.
- AC-FR-CAT-04-2: Given a category listing, then "Longueur" is also shown.

**FR-CAT-05**
- AC-FR-CAT-05-1: Given an open filter dialog, then the confirm button reads "Afficher <N> résultats" and the listing doesn't change until it is pressed.

**FR-CAT-06**
- AC-FR-CAT-06-1: Given a shoe campaign, when "Taille" opens, then a "Chaussures" tab lists size checkboxes, including half sizes.

**FR-CAT-07**
- AC-FR-CAT-07-1: Given a listing whose prices span 8–299 €, when "Prix" opens, then Prix minimum shows 8 and Prix maximum shows 299.

**FR-CAT-08**
- AC-FR-CAT-08-1: Given a dialog with no selection, then "Retirer" is disabled.
- AC-FR-CAT-08-2: Given active filters, then a status line shows "Vos filtres" with the article count (reference: "Vos filtres(4 Articles):"), plus "Réinitialiser".

**FR-CAT-09**
- AC-FR-CAT-09-1: Given a context with more than 60 products, then 60 tiles are rendered on first load.

## FR-PDP: Product detail page

**FR-PDP-01**
- AC-FR-PDP-01-1: Given an article URL, then all listed elements are present.
- AC-FR-PDP-01-2: When the member activates the brand heading, then `/brands/{code}` opens.

**FR-PDP-02**
- AC-FR-PDP-02-1: Given no size selected, when the member clicks "Ajouter au panier", then the alert "Choisissez une taille" is shown and nothing is added.

**FR-PDP-03**
- AC-FR-PDP-03-1: Given size 39 1/3 selected, when the member clicks "Ajouter au panier", then a success alert is shown and the cart button reads "Votre panier - 1 Article - 20 minutes et 0 secondes restantes".

**FR-PDP-04** — *HYPOTHESIS: validate before use*
- AC-FR-PDP-04-1: Given the article was just added, then "Ajouter au panier" is not offered again for it.

**FR-PDP-05**
- AC-FR-PDP-05-1: Given a size whose stock is at or below the threshold, then its label reads "Plus que <N> disponible(s)" with N the current available units.
- AC-FR-PDP-05-2: Given stock changes, then N reflects the new value on the next render.

**FR-PDP-06**
- AC-FR-PDP-06-1: Given an article, then a delivery window "<date> - <date>" is shown.

**FR-PDP-07**
- AC-FR-PDP-07-1: Given an article, then the returns statement shows the single configured return period, identical to the help and terms pages.

**FR-PDP-08**
- AC-FR-PDP-08-1: Given a shoe article, then sizes such as "38 2/3" and "39 1/3" are selectable radios.

## FR-SRC: Search

**FR-SRC-01**
- AC-FR-SRC-01-1: Given the member types "nike", then a listbox of brand suggestions (e.g. Nike, Nike Sportswear, Nike SB) is shown.

**FR-SRC-02**
- AC-FR-SRC-02-1: When the member selects "Nike Sportswear", then a listing with products from several campaigns opens, using the FR-CAT-01 component.

## FR-BRD: Brand management (display)

**FR-BRD-01**
- AC-FR-BRD-01-1: Given brand code B, then `/brands/B` lists products of B from several campaigns.

**FR-BRD-02**
- AC-FR-BRD-02-1: Given `/brands/H1X`, then the breadcrumb shows the brand's display name.

## FR-PRC: Product pricing

**FR-PRC-01**
- AC-FR-PRC-01-1: Given an article, then the sale price and the RRP are both shown, in euros with French formatting (e.g. "115,00 €").

**FR-PRC-02**
- AC-FR-PRC-02-1: Given a non-empty cart, then the summary says "Taxe sur la valeur ajoutée incluse, hors emballage & manutention".

## FR-STK: Stock indicators

**FR-STK-01**
- AC-FR-STK-01-1: Given member A reserved the last unit of a size, then member B can't reserve that unit.
- AC-FR-STK-01-2: Given the reserved state applies to a tile, then the tile shows "Réservé".

**FR-STK-02**
- AC-FR-STK-02-1: Given an article sold out, then its stock is not replenished within the campaign.

**FR-STK-03** — *HYPOTHESIS: validate before use*
- AC-FR-STK-03-1: Given a campaign ending within the threshold, then its product tiles show "Dernières heures".

## FR-CRT: Cart (authorized scope)

**FR-CRT-01**
- AC-FR-CRT-01-1: When the member activates "Votre panier", then a dialog opens and the page URL doesn't change.
- AC-FR-CRT-01-2: Given the dialog, then close buttons dismiss it.

**FR-CRT-02**
- AC-FR-CRT-02-1: Given an item added at T, then the cart title reads "Votre panier - 1 Article - <remaining> restantes" with remaining = 20 min - (now - T).

**FR-CRT-03**
- AC-FR-CRT-03-1: Given one reserved item, then its line shows all listed fields under the "Réservé" label.

**FR-CRT-04**
- AC-FR-CRT-04-1: When the member clicks remove, then the alertdialog "Retirer l’article du panier" explains the unit becomes available to others, with Supprimer and Annuler.
- AC-FR-CRT-04-2: When the member confirms "Supprimer", then the item leaves the cart and the unit is available again.
- AC-FR-CRT-04-3: When the member chooses "Annuler", then the cart is unchanged.

**FR-CRT-05**
- AC-FR-CRT-05-1: Given one line at 115,00 € with RRP 250,00 €, then the summary shows "Vous économisez 135,00 €", "Somme totale 115,00 €" and the line shows "-54%".

**FR-CRT-06**
- AC-FR-CRT-06-1: Given no reserved items, when the cart opens, then the empty-state text is shown.

**FR-CRT-07**
- AC-FR-CRT-07-1: Given a non-empty cart, then "Commander (<N> Article)" is shown and leads to the checkout entry point.

**FR-CRT-08** — *HYPOTHESIS: validate before use*
- AC-FR-CRT-08-1: Given a reservation older than 20 minutes, then the item is no longer in the cart and the unit is available to others.

## FR-ACC: Customer account (authorized scope)

**FR-ACC-01**
- AC-FR-ACC-01-1: Given a logged-in member, then the "Votre compte" navigation lists the sections, each with its own `/myaccount/…` URL.

**FR-ACC-02**
- AC-FR-ACC-02-1: Given no updates, then "Vous n'avez pas d'actualisations" and "Accédez à nos ventes" are shown.

**FR-ACC-03**
- AC-FR-ACC-03-1: Given no orders, then "Une fois vos commandes passées, vous pouvez les voir et les gérer ici." and "Achetez dès maintenant" are shown.

**FR-ACC-04**
- AC-FR-ACC-04-1: Given the returns page, then "commencez un retour" leads to `/myaccount/return/selection`, and the empty state reads "Pas de retours récents".
- AC-FR-ACC-04-2: Given the footer "Rétractation" link, then it leads to the return selection in withdrawal mode.

**FR-ACC-05**
- AC-FR-ACC-05-1: Given the newsletter page, then "Ventes du jour", "Ventes de la semaine" and "Personnalisez vos newsletters" are independent checkboxes.
- AC-FR-ACC-05-2: Given no change, then "Enregistrer" is disabled.

**FR-ACC-06**
- AC-FR-ACC-06-1: Given no reason selected, then "Se désabonner" is disabled; once a reason is selected it is enabled.

**FR-ACC-07**
- AC-FR-ACC-07-1: Given "Mes filtres", then tabs Femmes/Hommes show groups (Chaussures, Hauts / Tops, Pantalons, Robes et jupes, Lingerie et maillot de bain) with size-system radios and size checkboxes.
- AC-FR-ACC-07-2: Given no change, then "Enregistrer" is disabled.

**FR-ACC-08**
- AC-FR-ACC-08-1: Given a registration attempt by a customer who already has an account, then it is refused.
- AC-FR-ACC-08-2: Given registration, then the member confirms they are 18 or older.

## FR-REC: Recommendations

**FR-REC-01**
- AC-FR-REC-01-1: Given `/event`, then both rails are shown, and "Voir tout" opens `/campaigns/top-picks/articles`.
- AC-FR-REC-01-2: Given a vertical event, then neither rail is shown.

**FR-REC-02**
- AC-FR-REC-02-1: Given any event landing, then the rail shows 12 product tiles linking to article URLs.

## FR-DLV: Delivery & returns information

**FR-DLV-01**
- AC-FR-DLV-01-1: Given the delivery help category, then each listed topic has an article.

**FR-DLV-02**
- AC-FR-DLV-02-1: Given the PDP, the help article and the terms, then all three state the same period.

## FR-CNT: Footer, legal, help & consent

**FR-CNT-01**
- AC-FR-CNT-01-1: Given a page with footnote markers, then the footer defines every marker used.

**FR-CNT-02**
- AC-FR-CNT-02-1: Given the help home, then categories list articles at `/help/<category>/<slug>`.

**FR-CNT-03**
- AC-FR-CNT-03-1: Given the terms page, then the contracting entity, the contract language and the mediator are stated.

**FR-CNT-04**
- AC-FR-CNT-04-1: Given the footer, then a link opens the consent settings.

**FR-CNT-05**
- AC-FR-CNT-05-1: Given the footer, then the app-store badges are shown when apps exist.

## FR-ANL: Attribution

**FR-ANL-01**
- AC-FR-ANL-01-1: Given a product link in a campaign listing, then it carries the source surface as parameters.

## FR-OOS: Out of scope (recorded gaps)

**FR-OOS-01** — *OUT OF SCOPE: validate before use*
- AC-FR-OOS-01-1: Not specified here. A separate checkout specification is required (Q-04).

**FR-OOS-02** — *OUT OF SCOPE: validate before use*
- AC-FR-OOS-02-1: Not specified. "Activez Plus" was not authorized, and the program belongs to the Zalando group.

## NFR-TECH: Technical constraints

**NFR-TECH-01**
- AC-NFR-TECH-01-1: The solution architecture uses only the listed stack, unless the PO approves a change.

## NFR-I18N: Localisation

**NFR-I18N-01**
- AC-NFR-I18N-01-1: Given any page, then no label is in another language (e.g. no German ARIA labels, "Max" not "Maks").

**NFR-I18N-02**
- AC-NFR-I18N-02-1: Given N = 1, then the label reads "Plus que 1 disponible"; given N > 1, "Plus que N disponibles".

**NFR-I18N-03**
- AC-NFR-I18N-03-1: Given a price of 115 euros, then it renders as "115,00 €". Given a start date, then it renders like "jeu. 5 novembre 06:00".

## NFR-TIME: Time

**NFR-TIME-01** — *UNKNOWN: validate before use*
- AC-NFR-TIME-01-1: Given a campaign starting at 07:00 Paris, then every member sees the start time in the defined timezone.

## NFR-A11Y: Accessibility

**NFR-A11Y-01**
- AC-NFR-A11Y-01-1: Given keyboard-only use, then J1 (browse → PDP → add → cart → remove) can be completed.
- AC-NFR-A11Y-01-2: Given an alert (e.g. "Choisissez une taille"), then it is announced.

## NFR-SEC: Security

**NFR-SEC-01**
- AC-NFR-SEC-01-1: Given an API request for member data, then no member identifier appears in the URL and the response depends only on the session.

**NFR-SEC-02**
- AC-NFR-SEC-02-1: Given a state-changing request without a valid CSRF token, then it is rejected.

**NFR-SEC-03**
- AC-NFR-SEC-03-1: Given a cart or account response, then it carries `Cache-Control: no-store`.

**NFR-SEC-04**
- AC-NFR-SEC-04-1: Given any response, then HSTS is present; given HTML, then framing is restricted and a CSP is sent.

**NFR-SEC-05** — *HYPOTHESIS: validate before use*
- AC-NFR-SEC-05-1: Given automated bulk add-to-cart attempts, then they are throttled or challenged.

## NFR-PRV: Privacy

**NFR-PRV-01**
- AC-NFR-PRV-01-1: Given a member who refused analytics consent, then no personal attribute (hashed email, name, IDs) is sent to analytics or CRM tools.

## NFR-CON: Consistency & concurrency

**NFR-CON-01**
- AC-NFR-CON-01-1: Given two members adding the last unit at the same moment, then exactly one reservation succeeds and the other member is told the size is unavailable.

## NFR-CACHE: Caching

**NFR-CACHE-01**
- AC-NFR-CACHE-01-1: Given a built asset, then its file name contains a content hash and it is served with a one-year immutable cache.

## NFR-OBS: Observability

**NFR-OBS-01**
- AC-NFR-OBS-01-1: Given a failed API call, then it can be correlated with a server log entry by request ID.

## NFR-PERF: Performance

**NFR-PERF-01** — *UNKNOWN: validate before use*
- AC-NFR-PERF-01-1: Targets to be set by the PO before design (Q-42).

## NFR-RESP: Responsive

**NFR-RESP-01** — *UNKNOWN: validate before use*
- AC-NFR-RESP-01-1: Breakpoints and mobile behaviour to be specified (Q-15).
