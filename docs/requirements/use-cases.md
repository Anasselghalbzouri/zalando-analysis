# Use cases — Phase 04

Use cases are reconstructed from the observed journeys ([user-journeys.md](../discovery/user-journeys.md)). Steps beyond what was observed are marked UNKNOWN or OUT OF SCOPE rather than invented.

| ID | Name | Primary actor | Journey | Classification | Requirements |
|---|---|---|---|---|---|
| UC-01 | Browse live sales | Member | J1, J2 | CONFIRMED | FR-NAV-01, FR-NAV-03, FR-EVT-01, FR-EVT-02, FR-EVT-04, FR-CMP-01, FR-CMP-02, FR-CMP-03 |
| UC-02 | Browse by category | Member | J3 | CONFIRMED | FR-NAV-04, FR-CAT-01, FR-CAT-04 |
| UC-03 | Sort and filter a listing | Member | J1 step 3 | CONFIRMED (controls) / UNKNOWN (result behaviour) | FR-CAT-02, FR-CAT-03, FR-CAT-04, FR-CAT-05, FR-CAT-06, FR-CAT-07, FR-CAT-08, FR-CAT-09 |
| UC-04 | Search by brand | Member | J4 | CONFIRMED | FR-SRC-01, FR-SRC-02, FR-CAT-01 |
| UC-05 | View a product | Member | J1 steps 4–5 | CONFIRMED | FR-PDP-01, FR-PDP-05, FR-PDP-06, FR-PDP-07, FR-PDP-08, FR-PRC-01, FR-CMP-06 |
| UC-06 | Reserve a product (add to cart) | Member | J1 steps 6–7 | CONFIRMED | FR-PDP-02, FR-PDP-03, FR-PDP-04, FR-CRT-02, FR-NAV-05, FR-STK-01, NFR-CON-01 |
| UC-07 | Review and edit the cart | Member | J1 steps 8–9 | CONFIRMED (to the checkout button) | FR-CRT-01, FR-CRT-03, FR-CRT-04, FR-CRT-05, FR-CRT-06, FR-CRT-07, FR-PRC-02 |
| UC-08 | Discover upcoming sales | Member | J6 | CONFIRMED (display) / OUT OF SCOPE (notify) | FR-UPC-01, FR-UPC-02, FR-UPC-03, FR-UPC-04 |
| UC-09 | Browse a brand | Member | J5 | CONFIRMED | FR-BRD-01, FR-BRD-02, FR-CAT-01 |
| UC-10 | Use recommendations | Member | J7 | CONFIRMED (display) / UNKNOWN (logic) | FR-REC-01, FR-REC-02 |
| UC-11 | Run the campaign lifecycle | System (scheduler) | J6 step 3 | CONFIRMED (observed transition) / HYPOTHESIS (mechanism) | FR-CMP-05, FR-CMP-09, FR-UPC-01, FR-CMP-08 |
| UC-12 | Release an expired reservation | System | — | HYPOTHESIS | FR-CRT-08, FR-STK-01 |
| UC-13 | Manage newsletter preferences | Member | J11 | CONFIRMED (UI; nothing saved) | FR-ACC-05, FR-ACC-06 |
| UC-14 | Save size preferences | Member | J11 | CONFIRMED (UI; nothing saved) | FR-ACC-07 |
| UC-15 | Start a return or withdrawal | Member | J9 | CONFIRMED (entry) / UNKNOWN (flow) | FR-ACC-04, FR-DLV-02 |
| UC-16 | Read help and legal content | Member (Guest UNKNOWN) | — | CONFIRMED | FR-CNT-01, FR-CNT-02, FR-CNT-03, FR-DLV-01, FR-PRC-02 |
| UC-X1 | Checkout and pay | Member | J10 | OUT OF SCOPE | FR-OOS-01 |
| UC-X2 | Activate Zalando Plus | Member | J8 | OUT OF SCOPE | FR-OOS-02 |

## UC-01 — Browse live sales
- **Actor:** Member · **Classification:** CONFIRMED · **Journey:** J1, J2
- **Requirements:** FR-NAV-01, FR-NAV-03, FR-EVT-01, FR-EVT-02, FR-EVT-04, FR-CMP-01, FR-CMP-02, FR-CMP-03
- **Precondition:** The member is logged in.
- **Main flow:**
  1. The member opens `/event` or a vertical from the main nav.
  2. The system shows the hero (verticals only), the personal rails (default event only) and the grid of live campaigns with headline and countdown.
  3. The member selects a campaign tile.
  4. The system opens `/campaigns/{id}` with the campaign listing.
- **Alternative flows:**
  - 2a. A campaign is flagged "Special": its tile shows "Special" instead of a percentage (FR-CMP-04).
- **Postcondition:** The campaign listing is shown.

## UC-02 — Browse by category
- **Actor:** Member · **Classification:** CONFIRMED · **Journey:** J3
- **Requirements:** FR-NAV-04, FR-CAT-01, FR-CAT-04
- **Precondition:** An event page is open.
- **Main flow:**
  1. The member opens a vertical's mega-menu by hover or Enter.
  2. The member selects a category.
  3. The system opens `/articles/categories/{id}`, a cross-campaign listing with the extra "Longueur" filter.
- **Alternative flows:**
  - 1a. The vertical has no mega-menu: the member uses the text link instead (UC-01).
- **Postcondition:** The category listing is shown.

## UC-03 — Sort and filter a listing
- **Actor:** Member · **Classification:** CONFIRMED (controls) / UNKNOWN (result behaviour) · **Journey:** J1 step 3
- **Requirements:** FR-CAT-02, FR-CAT-03, FR-CAT-04, FR-CAT-05, FR-CAT-06, FR-CAT-07, FR-CAT-08, FR-CAT-09
- **Precondition:** A listing is open; 60 products are shown.
- **Main flow:**
  1. The member opens "Tri" or a filter dialog.
  2. The member chooses a sort option, size checkboxes or a price range.
  3. The dialog shows "Afficher N résultats".
  4. The member confirms.
  5. The system updates the listing and the status "Vos filtres (N Articles)".
- **Alternative flows:**
  - 2a. Nothing is selected: "Retirer" stays disabled.
  - 4a. The member closes the dialog: the listing is unchanged.
- **Postcondition:** The listing reflects the chosen sort and filters. Steps 4–5 were not exercised on the reference (UNKNOWN).

## UC-04 — Search by brand
- **Actor:** Member · **Classification:** CONFIRMED · **Journey:** J4
- **Requirements:** FR-SRC-01, FR-SRC-02, FR-CAT-01
- **Precondition:** Any page with the header.
- **Main flow:**
  1. The member types in the search combobox.
  2. The system suggests matching brands.
  3. The member selects a suggestion.
  4. The system opens `/search-catalog?query=…` with products across campaigns.
- **Alternative flows:**
  - 3a. The member presses Enter on free text: UNKNOWN (Q-44).
- **Postcondition:** Search results are shown.

## UC-05 — View a product
- **Actor:** Member · **Classification:** CONFIRMED · **Journey:** J1 steps 4–5
- **Requirements:** FR-PDP-01, FR-PDP-05, FR-PDP-06, FR-PDP-07, FR-PDP-08, FR-PRC-01, FR-CMP-06
- **Precondition:** A product tile is visible in any listing or rail.
- **Main flow:**
  1. The member selects the tile.
  2. The system opens `/campaigns/{campaignId}/articles/{sku}` with gallery, brand, price vs RRP, countdown, sizes with low-stock labels, delivery window and returns statement.
- **Alternative flows:**
  - 2a. The member activates the brand heading: UC-09.
- **Postcondition:** The PDP is shown.

## UC-06 — Reserve a product (add to cart)
- **Actor:** Member · **Classification:** CONFIRMED · **Journey:** J1 steps 6–7
- **Requirements:** FR-PDP-02, FR-PDP-03, FR-PDP-04, FR-CRT-02, FR-NAV-05, FR-STK-01, NFR-CON-01
- **Precondition:** A PDP is open for a live campaign.
- **Main flow:**
  1. The member selects a size.
  2. The member clicks "Ajouter au panier".
  3. The system reserves one unit for 20 minutes.
  4. The system shows a success alert and updates the cart button with the count and countdown.
- **Alternative flows:**
  - 1a. No size selected: the system shows "Choisissez une taille" and adds nothing.
  - 3a. The last unit was just reserved by someone else: the add fails (copy UNKNOWN, NFR-CON-01).
- **Postcondition:** The unit is reserved for the member and unavailable to others.

## UC-07 — Review and edit the cart
- **Actor:** Member · **Classification:** CONFIRMED (to the checkout button) · **Journey:** J1 steps 8–9
- **Requirements:** FR-CRT-01, FR-CRT-03, FR-CRT-04, FR-CRT-05, FR-CRT-06, FR-CRT-07, FR-PRC-02
- **Precondition:** The header is visible.
- **Main flow:**
  1. The member opens "Votre panier".
  2. The system shows reserved lines, the saving vs RRP, the total excl. packaging & handling, the timer and "Commander (N Article)".
  3. The member clicks remove on a line.
  4. The system asks for confirmation ("Retirer l’article du panier").
  5. The member confirms "Supprimer".
  6. The system removes the line and releases the unit.
- **Alternative flows:**
  - 2a. The cart is empty: the empty-state text is shown.
  - 5a. The member chooses "Annuler": nothing changes.
  - 3b. The member clicks "Commander": hand-off to checkout (OUT OF SCOPE).
- **Postcondition:** The cart reflects the member's choices.

## UC-08 — Discover upcoming sales
- **Actor:** Member · **Classification:** CONFIRMED (display) / OUT OF SCOPE (notify) · **Journey:** J6
- **Requirements:** FR-UPC-01, FR-UPC-02, FR-UPC-03, FR-UPC-04
- **Precondition:** An event landing with the section.
- **Main flow:**
  1. The member scrolls to "Prochainement".
  2. The system shows the global upcoming list with relative or absolute start times.
  3. At the start time, the campaign appears live in the grids (UC-11).
- **Alternative flows:**
  - 2a. The member clicks "Informez moi": behaviour OUT OF SCOPE (Q-47).
- **Postcondition:** None (read only).

## UC-09 — Browse a brand
- **Actor:** Member · **Classification:** CONFIRMED · **Journey:** J5
- **Requirements:** FR-BRD-01, FR-BRD-02, FR-CAT-01
- **Precondition:** A PDP is open.
- **Main flow:**
  1. The member activates the brand heading.
  2. The system opens `/brands/{code}` listing the brand's products across campaigns, with the brand name in the breadcrumb.
- **Postcondition:** The brand listing is shown.

## UC-10 — Use recommendations
- **Actor:** Member · **Classification:** CONFIRMED (display) / UNKNOWN (logic) · **Journey:** J7
- **Requirements:** FR-REC-01, FR-REC-02
- **Precondition:** The default event or any event is open.
- **Main flow:**
  1. The member selects a product in "Recommandé pour vous", "Continuez votre shopping" or "Populaire dans votre région", or "Voir tout".
  2. The system opens the PDP or the top-picks listing.
- **Postcondition:** The PDP or listing is shown.

## UC-11 — Run the campaign lifecycle
- **Actor:** System (scheduler) · **Classification:** CONFIRMED (observed transition) / HYPOTHESIS (mechanism) · **Journey:** J6 step 3
- **Requirements:** FR-CMP-05, FR-CMP-09, FR-UPC-01, FR-CMP-08
- **Precondition:** A campaign with start and end times exists.
- **Main flow:**
  1. Before the start, the campaign is listed in "Prochainement" only.
  2. At the start, it leaves "Prochainement" and appears in its event grids.
  3. At the end, it leaves the grids.
- **Alternative flows:**
  - 3a. A member opens the URL of an ended campaign: UNKNOWN (Q-17).
- **Postcondition:** The campaign state matches the clock.

## UC-12 — Release an expired reservation
- **Actor:** System · **Classification:** HYPOTHESIS · **Journey:** —
- **Requirements:** FR-CRT-08, FR-STK-01
- **Precondition:** A reservation exists.
- **Main flow:**
  1. The 20-minute timer reaches zero.
  2. The system removes the item from the cart and releases the unit.
- **Postcondition:** The unit is available to others. Not observed (BR-14-06).

## UC-13 — Manage newsletter preferences
- **Actor:** Member · **Classification:** CONFIRMED (UI; nothing saved) · **Journey:** J11
- **Requirements:** FR-ACC-05, FR-ACC-06
- **Precondition:** The member is in "Paramètres de la newsletter".
- **Main flow:**
  1. The member ticks or unticks daily and weekly sales and the personalisation opt-in.
  2. "Enregistrer" becomes enabled.
  3. The member saves.
- **Alternative flows:**
  - 1a. The member wants to unsubscribe: they choose a reason, then "Se désabonner" becomes enabled.
- **Postcondition:** Preferences are stored. Saving was not exercised on the reference.

## UC-14 — Save size preferences
- **Actor:** Member · **Classification:** CONFIRMED (UI; nothing saved) · **Journey:** J11
- **Requirements:** FR-ACC-07
- **Precondition:** The member is in "Mes filtres".
- **Main flow:**
  1. The member picks a gender tab, a garment group, a size system and sizes.
  2. The member saves.
- **Postcondition:** Preferences are stored. Their use in listings is HYPOTHESIS.

## UC-15 — Start a return or withdrawal
- **Actor:** Member · **Classification:** CONFIRMED (entry) / UNKNOWN (flow) · **Journey:** J9
- **Requirements:** FR-ACC-04, FR-DLV-02
- **Precondition:** The member has a delivered order.
- **Main flow:**
  1. The member opens "Retours (Rétractation)" and starts a return.
  2. The system opens the return selection.
  3. Further steps: UNKNOWN (Q-38).
- **Alternative flows:**
  - 1a. From the footer "Rétractation": the selection opens in withdrawal mode.
- **Postcondition:** UNKNOWN.

## UC-16 — Read help and legal content
- **Actor:** Member (Guest UNKNOWN) · **Classification:** CONFIRMED · **Journey:** —
- **Requirements:** FR-CNT-01, FR-CNT-02, FR-CNT-03, FR-DLV-01, FR-PRC-02
- **Precondition:** Any page with the footer.
- **Main flow:**
  1. The member opens a help or legal link.
  2. The system shows the article at `/help/<category>/<slug>` or the terms.
- **Postcondition:** Content shown.

## UC-X1 — Checkout and pay
- **Actor:** Member · **Classification:** OUT OF SCOPE · **Journey:** J10
- **Requirements:** FR-OOS-01
- **Precondition:** —
- **Main flow:**
  1. Not specified (M21). Requires its own specification (Q-04).
- **Postcondition:** —

## UC-X2 — Activate Zalando Plus
- **Actor:** Member · **Classification:** OUT OF SCOPE · **Journey:** J8
- **Requirements:** FR-OOS-02
- **Precondition:** —
- **Main flow:**
  1. Not specified (not authorized).
- **Postcondition:** —
