# User journeys — Phase 02

These journeys were reconstructed from observed navigation. Each step names the module, the URL pattern and the classification. Steps past an OUT OF SCOPE boundary aren't described.

## J1 — Browse today's sales → product → cart (CONFIRMED up to the cart; checkout OUT OF SCOPE)
1. Land on `/event` (M05). See the personalised rails (M10) and 126 live campaign tiles (M03).
2. Pick a campaign tile → `/campaigns/{id}` (M03/M06). There's no countdown on this page (Q-12).
3. Optionally open Tri or a filter dialog (M06). Confirm with "Appliquer" or "Afficher N résultats". *Applying wasn't exercised.*
4. Click a product → `/campaigns/{id}/articles/{sku}?navigationSource=catalog&catalog-type=campaign` (M07).
5. See the price vs RRP (M12), the countdown, sizes with low-stock hints (M13), and delivery and returns (M17).
6. "Ajouter au panier" without a size → alert "Choisissez une taille" (CONFIRMED).
7. Choose a size → "Ajouter au panier" → alert "Le produit a été mis à jour avec succès." The header shows "Votre panier - 1 Article - 20 minutes … restantes" (CONFIRMED, M14).
8. Open the cart drawer: reserved items, saving vs RRP, total excl. the 3,90 € fee, "Commander (N Article)" (CONFIRMED).
9. Either remove the item (confirmation dialog, the unit is released to other shoppers; CONFIRMED) or click "Commander" → **OUT OF SCOPE (M21)**.

## J2 — Browse a vertical (CONFIRMED)
1. Main nav "Femme" → `/event/2` (M02 → M05). Hero + campaign grid (82) + Populaire + Prochainement.
2. Continue as J1 step 2.

## J3 — Browse by category (CONFIRMED to the listing)
1. Hover the "Femme" chevron → mega-menu (M02).
2. Pick a category → `/articles/categories/{id}?category={id}` (M06). The listing spans campaigns and has an extra "Longueur" filter.
3. Continue as J1 step 4.

## J4 — Search by brand (CONFIRMED)
1. Type in the header combobox (M08) → brand suggestions.
2. Select one → `/search-catalog?query=…&searchAnonId=…` (M06 listing across campaigns, including "Dernières heures" badges).
3. Continue as J1 step 4.

## J5 — From a product to its brand (CONFIRMED)
1. On the PDP, click the brand H1 → `/brands/{code}` (M09). Tiles may show "Réservé" (M13).

## J6 — Discover an upcoming sale (CONFIRMED UI; subscription OUT OF SCOPE)
1. Scroll to "Prochainement" on any landing (M04). The tile has a name, image and start time, and **no link**.
2. "Informez moi" → **OUT OF SCOPE (Q-01)**.
3. When the start time arrives, the sale appears live in the grids (BR-03-09, observed for 5 sales).

## J7 — Recommendations (CONFIRMED)
1. `/event`: "Recommandé pour vous" → "Voir tout" → `/campaigns/top-picks/articles` (M10, M06).
2. Or "Continuez votre shopping" tabs, or "Populaire dans votre région" → PDP.

## J8 — Loyalty upsell (CONFIRMED to the dismissal)
1. Home modal "Zalando Plus" → "Peut-être plus tard" (dismissed in Phase 01) or "Activez Plus" → **OUT OF SCOPE**.

## J9 — Returns / withdrawal (CONFIRMED to the entry page)
1. Header "Compte" → "Votre compte" → `/myaccount/overview` → "Retours (Rétractation)" `/myaccount/returns`.
2. "Commencer un retour" → `/myaccount/return/selection`. Not followed: there was no order to return.
3. Alternatively: footer "Rétractation" → `/myaccount/return/selection?withdrawal=true`.

## J11 — Manage newsletter & saved sizes (CONFIRMED UI, nothing saved)
1. `/myaccount/newsletter`: daily/weekly checkboxes, a personalisation opt-in, and unsubscribe (reason required).
2. `/myaccount/filter`: per-gender size preferences by garment group (EU/US).

## J10 — Checkout (OUT OF SCOPE, M21)
Not analysed. Excluded by authorization.

## Actors per journey
| Journey | Member | Guest |
|---|---|---|
| J1–J7, J9, J11 | CONFIRMED | UNKNOWN (Q-03). The help page says membership is required and free (M15 BR-15-01) |
| J8–J10 | entry points CONFIRMED | UNKNOWN |
