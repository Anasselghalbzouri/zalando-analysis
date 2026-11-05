# User stories — Phase 06

These stories refine the 27 Phase 04 stories ([user-stories.md](../requirements/user-stories.md)) into implementation-sized units, mapped to the Phase 05 design.

## How to read a story
- **IDs:** Phase 06 IDs have three digits (`US-001`). Phase 04 IDs have two (`US-01`), and each story names its Phase 04 parent, or "new" for requirements that had no Phase 04 story (FR-NAV-01, FR-CMP-06, FR-CAT-09, FR-PDP-04, FR-CNT-05, FR-ANL-01 and most NFRs).
- **Sources:** "Source requirements" are exact SRS IDs, with their Phase 04 class. Enabler stories (infrastructure, spike, seed data) also cite the FR or NFR they enable.
- **Status:** see [03-product-backlog.md §2](03-product-backlog.md). The open questions blocking a story are listed next to its status.
- **Acceptance criteria:**
  - They reuse the Phase 04 criteria (`AC-…` IDs and observed French copy) and the Phase 05 contract behaviour (HTTP codes, error codes).
  - A criterion that implements a Phase 05 **PROPOSED** rule says so.
  - A **Pending** criterion names what can't be accepted yet and why. It is a placeholder, not a requirement.
- **Labels:** no story turns a HYPOTHESIS or UNKNOWN item into confirmed behaviour. HYPOTHESIS stories say "pending PO validation" in their note.
- **Generated:** from `tools/phase06/backlog.txt` by `tools/phase06/gen.py`.



## E01 — Authentication & Identity

### US-001 — Register as an adult member
- **ID:** US-001 · **Epic:** E01 · **Phase 04 story:** US-26

> As a prospective member,  
> I want to create an account once, confirming that I am 18 or older and accepting the terms,  
> so that I can access the sales.

- **Priority:** Must
- **Status:** Needs Clarification (Q-40, Q-41)
- **MVP class:** MVP · **Sprint:** S1
- **Dependencies:** US-059, US-060
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-ACC-08 (CONFIRMED)
- **API:** `GET /sanctum/csrf-cookie`; `POST /api/v1/auth/register`
- **Tables:** `users`

**Acceptance criteria**
1. *Functional.* Given a visitor without an account, when they submit first name, e-mail, password, confirmation, the adult confirmation and the terms acceptance, then the API answers 201 with the User and a session starts (AC-FR-ACC-08-2).
2. *Validation.* Given an e-mail that already has an account, when they register, then the API answers 422 `validation_failed` with field code `taken` and no second account is created (AC-FR-ACC-08-1).
3. *Validation.* Given `adult_confirmed` or `terms_accepted` not accepted, then 422 with field code `must_be_accepted` on that field (BR-15-02).
4. *Validation.* Given a password under 12 characters or not confirmed, then 422 on `password` (PROPOSED rule, api-contracts §2).
5. *Error.* Given no valid CSRF token, then 419 `csrf_token_mismatch`; given too many attempts, then 429 with `Retry-After`.
6. *Authorization.* Given a visitor who already has a session, then `/register` is not offered (guest-only route) and the endpoint refuses the request.
7. *Pending.* Social login and any further registration fields are not built until Q-40 and Q-41 are answered.

**Tasks:** T-001-B1, T-001-B2, T-001-BT1, T-001-F1, T-001-F2, T-001-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-001))

### US-002 — Log in and log out with a protected session
- **ID:** US-002 · **Epic:** E01 · **Phase 04 story:** new (part of US-26)

> As a member,  
> I want to log in with my credentials and log out again,  
> so that my account and cart are reachable only through my own session.

- **Priority:** Must
- **Status:** Needs Clarification (Q-40, Q-63)
- **MVP class:** MVP · **Sprint:** S1
- **Dependencies:** US-001
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-ACC-08 (CONFIRMED), NFR-SEC-01 (CONFIRMED), NFR-SEC-02 (CONFIRMED)
- **API:** `GET /sanctum/csrf-cookie`; `POST /api/v1/auth/login`; `POST /api/v1/auth/logout`; `GET /api/v1/me`
- **Tables:** `users`, `sessions`

**Acceptance criteria**
1. *Functional.* Given valid credentials, when the member logs in, then 200 with the User, the session ID is regenerated and `GET /me` returns the member.
2. *Error.* Given an unknown e-mail or a wrong password, then 422 `invalid_credentials` with the same message in both cases (no account enumeration).
3. *Error.* Given repeated failures for the same e-mail and IP, then 429 `rate_limited` with `Retry-After`.
4. *Functional.* Given a logged-in member, when they log out, then 204, the session is invalidated and the CSRF token rotated; a later `GET /me` answers 401.
5. *Security.* Given any state-changing request without a valid `X-XSRF-TOKEN`, then it is rejected with 419 (AC-NFR-SEC-02-1).
6. *Security.* Given any API request for member data, then no member identifier appears in the URL and the response depends only on the session (AC-NFR-SEC-01-1).
7. *Functional.* Given a 401 on a member call, then the SPA clears its auth state, sends the member to `/login?redirect=` and returns them there after login.

**Tasks:** T-002-B1, T-002-B2, T-002-BT1, T-002-F1, T-002-F2, T-002-FT1, T-002-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-002))

### US-003 — Reset a forgotten password
- **ID:** US-003 · **Epic:** E01 · **Phase 04 story:** new

> As a member,  
> I want to receive a reset link and choose a new password,  
> so that I can get back into my account on my own.

- **Priority:** Must
- **Status:** Needs Clarification (Q-40)
- **MVP class:** MVP · **Sprint:** S1
- **Dependencies:** US-002
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-ACC-08 (CONFIRMED)
- **API:** `POST /api/v1/auth/forgot-password`; `POST /api/v1/auth/reset-password`
- **Tables:** `users`, `password_reset_tokens`
- **Note:** Derived from the Phase 05 PROPOSED security baseline (authentication-design §3.2), not from observed behaviour. Needed only if e-mail and password login is chosen (Q-40). PO to confirm.

**Acceptance criteria**
1. *Functional.* Given any e-mail, when a reset is requested, then the API answers 202 with the same response whether or not an account exists.
2. *Functional.* Given a valid token, when the member sets a new password that meets the registration rules, then 204 and the member's other sessions are invalidated.
3. *Error.* Given an invalid or expired token, then 422 `invalid_token`.
4. *Error.* Given repeated requests, then 429 with `Retry-After`.

**Tasks:** T-003-B1, T-003-BT1, T-003-F1, T-003-FT1, T-003-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-003))

### US-004 — Verify my e-mail address
- **ID:** US-004 · **Epic:** E01 · **Phase 04 story:** new

> As a member,  
> I want to confirm my e-mail address from a signed link,  
> so that the store knows the address belongs to me.

- **Priority:** Should (Lowered from the Must of FR-ACC-08 because verification is a Phase 05 PROPOSED flow, not a Phase 04 requirement. PO to confirm.)
- **Status:** Needs Clarification (Q-40)
- **MVP class:** Post-MVP · **Sprint:** S6
- **Dependencies:** US-001
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-ACC-08 (CONFIRMED)
- **API:** `POST /api/v1/auth/email/verification-notification`; `GET /api/v1/auth/email/verify/{id}/{hash}`
- **Tables:** `users`

**Acceptance criteria**
1. *Functional.* Given a new member, then a verification e-mail with a signed link is queued.
2. *Functional.* Given a valid, unexpired signed link whose subject is the session user, when it is opened, then 204 and `email_verified` becomes true.
3. *Error.* Given an expired or tampered link, then 403 `invalid_signature` and the SPA offers to resend.
4. *Pending.* No feature is gated on a verified e-mail until the PO decides which ones are.

**Tasks:** T-004-B1, T-004-BT1, T-004-F1, T-004-FT1, T-004-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-004))

### US-005 — Apply one catalogue access mode
- **ID:** US-005 · **Epic:** E01 · **Phase 04 story:** new

> As a product owner,  
> I want every catalogue route to follow one access setting, members only or public,  
> so that the membership rule can be decided without code changes.

- **Priority:** Must
- **Status:** Needs Clarification (Q-71, Q-28, Q-03)
- **MVP class:** MVP · **Sprint:** S1
- **Dependencies:** US-002, US-059
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-ACC-08 (CONFIRMED)
- **API:** `GET /api/v1/storefront/settings`; `GET /api/v1/storefront/navigation`
- **Tables:** none
- **Note:** Implements contradiction C-05. Neither mode is confirmed; the help text suggests membership is required.

**Acceptance criteria**
1. *Functional.* Given `catalog_access = members`, when a request without a session reaches a C route, then 401 `unauthenticated` and the SPA redirects to `/login?redirect=`.
2. *Functional.* Given `catalog_access = public`, when the same request is made, then the catalogue response is returned, while S routes (cart, account) still answer 401.
3. *Functional.* Given either mode, then help and legal pages (P routes) are reachable without a session.
4. *Error.* Given production with no value for `catalog_access`, then the settings endpoint answers 503 `settings_incomplete` (fail closed).

**Tasks:** T-005-B1, T-005-BT1, T-005-F1, T-005-FT1, T-005-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-005))


## E02 — User Account

### US-006 — Account navigation and overview
- **ID:** US-006 · **Epic:** E02 · **Phase 04 story:** US-19

> As a member,  
> I want to reach every account section from one navigation and see my overview,  
> so that I can manage my account in one place.

- **Priority:** Should
- **Status:** Ready
- **MVP class:** Post-MVP · **Sprint:** S6
- **Dependencies:** US-002, US-043
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-ACC-01 (CONFIRMED), FR-ACC-02 (CONFIRMED)
- **API:** `GET /api/v1/account/overview`
- **Tables:** `users`

**Acceptance criteria**
1. *Functional.* Given a logged-in member, then the "Votre compte" navigation lists the sections, each with its own `/myaccount/…` URL (AC-FR-ACC-01-1).
2. *Functional.* Given no updates, then "Vous n'avez pas d'actualisations" and "Accédez à nos ventes" are shown (AC-FR-ACC-02-1); `updates` is an empty list in v1.
3. *Authorization.* Given no session, then the endpoint answers 401 and the page redirects to login; the response carries `Cache-Control: no-store`.
4. *Scope.* "Adresses" isn't routed (C-02) and the external rewards link isn't built (FR-OOS-02).

**Tasks:** T-006-B1, T-006-BT1, T-006-F1, T-006-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-006))

### US-007 — Edit my personal details
- **ID:** US-007 · **Epic:** E02 · **Phase 04 story:** US-19

> As a member,  
> I want to change the first name the store greets me with,  
> so that my details stay correct.

- **Priority:** Should
- **Status:** Needs Clarification (Q-41)
- **MVP class:** Post-MVP · **Sprint:** S6
- **Dependencies:** US-006
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-ACC-01 (CONFIRMED)
- **API:** `GET /api/v1/account/profile`; `PATCH /api/v1/account/profile`
- **Tables:** `users`

**Acceptance criteria**
1. *Functional.* Given a member, when they save a first name of 1–100 characters, then 200 with the profile and the greeting uses the new name.
2. *Validation.* Given an empty or over-long first name, then 422 `validation_failed` on `first_name`.
3. *Error.* Given no CSRF token, then 419.
4. *Pending.* E-mail change and further fields are not built until Q-41 is answered.

**Tasks:** T-007-B1, T-007-BT1, T-007-F1, T-007-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-007))

### US-008 — Choose which sales e-mails I receive
- **ID:** US-008 · **Epic:** E02 · **Phase 04 story:** US-17

> As a member,  
> I want to switch daily and weekly sales e-mails and personalisation on or off independently,  
> so that I control what I receive.

- **Priority:** Should
- **Status:** Ready
- **MVP class:** Post-MVP · **Sprint:** S6
- **Dependencies:** US-006
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-ACC-05 (CONFIRMED)
- **API:** `GET /api/v1/account/newsletter`; `PUT /api/v1/account/newsletter`
- **Tables:** `newsletter_preferences`
- **Note:** Syncing preferences to a marketing tool is UNKNOWN (no tool chosen) and is not part of this story.

**Acceptance criteria**
1. *Functional.* Given the newsletter page, then "Ventes du jour", "Ventes de la semaine" and "Personnalisez vos newsletters" are independent checkboxes (AC-FR-ACC-05-1).
2. *Functional.* Given no change, then "Enregistrer" is disabled (AC-FR-ACC-05-2); after a change and save, the API answers 200 and the saved state is shown.
3. *Validation.* Given a missing or non-boolean field, then 422.
4. *Functional.* Given a member who had unsubscribed, when they save any option as true, then `unsubscribed_at` is cleared (PROPOSED rule, api-contracts §8).

**Tasks:** T-008-B1, T-008-BT1, T-008-F1, T-008-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-008))

### US-009 — Unsubscribe with a reason
- **ID:** US-009 · **Epic:** E02 · **Phase 04 story:** US-17

> As a member,  
> I want to unsubscribe from sales e-mails by giving a reason,  
> so that I stop receiving them.

- **Priority:** Should
- **Status:** Needs Clarification (Q-83)
- **MVP class:** Post-MVP · **Sprint:** S6
- **Dependencies:** US-008
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-ACC-06 (CONFIRMED)
- **API:** `GET /api/v1/account/newsletter/unsubscribe-reasons`; `POST /api/v1/account/newsletter/unsubscribe`
- **Tables:** `newsletter_preferences`, `unsubscribe_reasons`

**Acceptance criteria**
1. *Functional.* Given no reason selected, then "Se désabonner" is disabled; once a reason is selected it is enabled (AC-FR-ACC-06-1).
2. *Functional.* Given a selected active reason, when the member unsubscribes, then 200 and the preferences show the unsubscribed state.
3. *Validation.* Given no `reason_id`, then 422 with field code `reason_required`; given an unknown or inactive reason, then 422.
4. *Pending.* The reason options are PO content (Q-83); seeders use visibly labelled placeholders.

**Tasks:** T-009-B1, T-009-BT1, T-009-F1, T-009-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-009))

### US-010 — Save my sizes
- **ID:** US-010 · **Epic:** E02 · **Phase 04 story:** US-18

> As a member,  
> I want to save my sizes per gender and garment group,  
> so that I don't have to re-enter them.

- **Priority:** Should
- **Status:** Ready
- **MVP class:** Post-MVP · **Sprint:** S6
- **Dependencies:** US-006, US-012
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-ACC-07 (CONFIRMED)
- **API:** `GET /api/v1/account/saved-sizes`; `PUT /api/v1/account/saved-sizes`
- **Tables:** `saved_size_profiles`, `saved_size_values`, `garment_groups`, `size_systems`, `size_values`
- **Note:** Whether saved sizes pre-filter listings (Q-39) is not part of this story.

**Acceptance criteria**
1. *Functional.* Given "Mes filtres", then tabs Femmes/Hommes show the garment groups with size-system radios and size checkboxes (AC-FR-ACC-07-1).
2. *Functional.* Given no change, then "Enregistrer" is disabled (AC-FR-ACC-07-2).
3. *Validation.* Given a size that doesn't belong to the profile's size system, then 422 with field code `size_not_in_system` on `profiles.<n>.size_value_ids`.
4. *Validation.* Given two profiles for the same gender and garment group, then 422.
5. *Functional.* Given a profile saved with no sizes, then that profile is deleted.

**Tasks:** T-010-B1, T-010-BT1, T-010-F1, T-010-FT1, T-010-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-010))

### US-011 — Orders and returns entry pages
- **ID:** US-011 · **Epic:** E02 · **Phase 04 story:** US-19

> As a member,  
> I want to find the orders and returns sections,  
> so that I know where after-sales will happen.

- **Priority:** Should
- **Status:** Ready
- **MVP class:** Post-MVP · **Sprint:** S6
- **Dependencies:** US-006
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-ACC-03 (CONFIRMED), FR-ACC-04 (CONFIRMED)
- **API:** none
- **Tables:** none
- **Note:** Empty states and entry pages only. Order data and the return and withdrawal flows belong to checkout (C-04, Q-04, Q-38) and are not built.

**Acceptance criteria**
1. *Functional.* Given a member with no orders, then "Une fois vos commandes passées, vous pouvez les voir et les gérer ici." and "Achetez dès maintenant" are shown (AC-FR-ACC-03-1).
2. *Functional.* Given the returns page, then "commencez un retour" leads to `/myaccount/return/selection` and the empty state reads "Pas de retours récents" (AC-FR-ACC-04-1).
3. *Functional.* Given the footer "Rétractation" link, then it leads to the return selection in withdrawal mode, `?withdrawal=true` (AC-FR-ACC-04-2).
4. *Scope.* No order, return or withdrawal endpoint or table is created.

**Tasks:** T-011-F1, T-011-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-011))


## E03 — Catalog Management

### US-012 — Catalogue data model
- **ID:** US-012 · **Epic:** E03 · **Phase 04 story:** new

> As a development team,  
> I want the catalogue tables, models and factories built as designed,  
> so that brands, categories, sizes, products, images and attributes can be stored and queried.

- **Priority:** Must
- **Status:** Blocked (Q-70, Q-84)
- **MVP class:** MVP · **Sprint:** S1
- **Dependencies:** US-061
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** no
- **Source requirements:** FR-PDP-01 (CONFIRMED), FR-PDP-08 (CONFIRMED), FR-CAT-04 (CONFIRMED), FR-CAT-06 (CONFIRMED), FR-BRD-01 (CONFIRMED)
- **API:** none
- **Tables:** `brands`, `categories`, `size_systems`, `size_values`, `products`, `product_images`, `product_variants`, `attributes`, `attribute_values`, `product_attribute_values`
- **Note:** Q-70 decides whether a product is a model or a model + colour; the migrations can't be frozen before it. The root-slug erratum (R-07) is fixed here once Q-84 picks the method.

**Acceptance criteria**
1. *Data.* Given the migrations, then every Catalog table, column, key and index in schema.md and indexing-strategy.md exists as documented, or the difference is an approved erratum.
2. *Data.* Given two root categories (no parent) with the same slug, when the second is saved, then it is refused (fix for R-07, method per Q-84).
3. *Data.* Given a product image, then its alt text is mandatory (input for NFR-A11Y-01).
4. *Data.* Given a category, then its materialised `path` is maintained when it is created or moved.
5. *Architecture.* Given the module dependency test, then Catalog depends on no other domain module.

**Tasks:** T-012-B1, T-012-B2, T-012-B3, T-012-BT1 ([05-technical-tasks.md](05-technical-tasks.md#us-012))


## E04 — Categories & Brands

### US-013 — Browse a category listing
- **ID:** US-013 · **Epic:** E04 · **Phase 04 story:** US-03

> As a member,  
> I want to see a product type's live articles across sales,  
> so that I can shop by category.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S3
- **Dependencies:** US-023, US-012
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-CAT-01 (CONFIRMED), FR-CAT-04 (CONFIRMED)
- **API:** `GET /api/v1/categories/{id}`; `GET /api/v1/articles`; `GET /api/v1/articles/facets`
- **Tables:** `categories`, `campaign_articles`

**Acceptance criteria**
1. *Functional.* Given `/articles/categories/{id}`, then the category header and breadcrumb are shown, with the same tile, filter and sort controls as every listing context (AC-FR-CAT-01-1).
2. *Functional.* Given a category listing, then live articles of that category and its descendants are listed.
3. *Functional.* Given a category listing whose products carry "Longueur", then the "Longueur" filter is also shown (AC-FR-CAT-04-2); facets without values are omitted.
4. *Error.* Given an unknown category ID, then 404 and the not-found page.
5. *Authorization.* Given `catalog_access = members` and no session, then 401 and a redirect to login.

**Tasks:** T-013-B1, T-013-BT1, T-013-F1, T-013-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-013))

### US-014 — Browse a brand page
- **ID:** US-014 · **Epic:** E04 · **Phase 04 story:** US-07

> As a member,  
> I want to open a brand's page from a product,  
> so that I see everything on sale from that brand.

- **Priority:** Should
- **Status:** Ready
- **MVP class:** MVP (pulled in) · **Sprint:** S4
- **Dependencies:** US-023
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-BRD-01 (CONFIRMED), FR-BRD-02 (CONFIRMED)
- **API:** `GET /api/v1/brands/{code}`; `GET /api/v1/articles`
- **Tables:** `brands`
- **Note:** Pulled into the MVP sprints: the PDP brand heading must open `/brands/{code}` (AC-FR-PDP-01-2, a Must).

**Acceptance criteria**
1. *Functional.* Given brand code B, then `/brands/B` lists live articles of B from several campaigns (AC-FR-BRD-01-1).
2. *Functional.* Given `/brands/{code}`, then the breadcrumb shows the brand's display name, never the internal code (AC-FR-BRD-02-1; reference defect M09 not copied).
3. *Validation.* Given a code that doesn't match `^[A-Za-z0-9-]{1,16}$`, then 422; given an unknown code, then 404.

**Tasks:** T-014-B1, T-014-BT1, T-014-F1, T-014-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-014))


## E05 — Campaign Management

### US-015 — Campaigns and landings with a derived lifecycle
- **ID:** US-015 · **Epic:** E05 · **Phase 04 story:** US-23, US-24 (reframed: the merchandiser persona is a HYPOTHESIS)

> As a member,  
> I want sales to start and end exactly on time without anyone switching them,  
> so that what I see is always the current set of live sales.

- **Priority:** Must
- **Status:** Blocked (Q-70, Q-77, Q-20, Q-33)
- **MVP class:** MVP · **Sprint:** S2
- **Dependencies:** US-012, US-061
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-CMP-05 (CONFIRMED), FR-CMP-06 (CONFIRMED), FR-CMP-07 (CONFIRMED), FR-CMP-09 (CONFIRMED), FR-CMP-10 (CONFIRMED), FR-EVT-02 (CONFIRMED)
- **API:** none
- **Tables:** `landings`, `landing_campaigns`, `campaigns`, `campaign_articles`
- **Note:** Written from the member's view because the merchandiser persona is a HYPOTHESIS and no back-office is designed (Q-50). Data comes from seeders (US-064).

**Acceptance criteria**
1. *Functional.* Given a campaign with start S and end E, then it is upcoming before S, live from S to E and ended after E, computed from the request time with no stored state (AC-FR-CMP-09-1).
2. *Functional.* Given an upcoming campaign whose start passes, then it appears in its landing grids with no job involved (AC-FR-CMP-05-1); given a live campaign whose end passes, then it leaves every grid (AC-FR-CMP-05-2).
3. *Functional.* Given a campaign assigned to landings A and B, when it is live, then it appears in A, B and the default landing (AC-FR-EVT-02-1).
4. *Functional.* Given model M sold in campaigns C1 and C2, then two distinct article URLs exist, one under each campaign (AC-FR-CMP-07-1).
5. *Functional.* Given a theme or price-point campaign with articles of several brands, then it is stored and rendered like a brand campaign (AC-FR-CMP-10-1).
6. *Data.* Given a campaign article, then 0 < sale price ≤ RRP and `discount_percent` is generated with the single documented rule (PROPOSED floor, Q-77).
7. *Pending.* Launch slots (AC-FR-CMP-09-2) wait for Q-20 and Q-33; until then any datetime is accepted.

**Tasks:** T-015-B1, T-015-B2, T-015-B3, T-015-BT1, T-015-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-015))

### US-016 — Event landing pages
- **ID:** US-016 · **Epic:** E05 · **Phase 04 story:** US-01, US-02

> As a member,  
> I want to open the default landing or a vertical and see its live sales,  
> so that I only see the sales relevant to me.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S2
- **Dependencies:** US-015, US-043, US-064
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-EVT-01 (CONFIRMED), FR-EVT-02 (CONFIRMED), FR-EVT-04 (CONFIRMED)
- **API:** `GET /api/v1/landings/{landing}`
- **Tables:** `landings`, `landing_campaigns`
- **Note:** The hero's link target is a HYPOTHESIS (Q-13) and is not part of this story; the hero is rendered without a decided target.

**Acceptance criteria**
1. *Functional.* Given an existing landing ID, when `/event/{id}` is requested, then that landing is rendered (AC-FR-EVT-01-1).
2. *Functional.* Given `/event`, then the default landing is rendered with the same campaign set as the "all sales" landing (AC-FR-EVT-01-2); `/` redirects to `/event`.
3. *Functional.* Given an upcoming campaign, then it appears in no landing grid (AC-FR-EVT-02-2).
4. *Functional.* Given a vertical landing with a hero, then exactly one hero is shown above the grid; given `/event`, then no hero is shown (AC-FR-EVT-04-1, AC-FR-EVT-04-2).
5. *Validation.* Given a landing parameter that is neither digits nor `default`, then 422; given an unknown ID, then 404 and the not-found page.

**Tasks:** T-016-B1, T-016-BT1, T-016-F1, T-016-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-016))

### US-017 — Campaign tile with headline and countdown
- **ID:** US-017 · **Epic:** E05 · **Phase 04 story:** US-01

> As a member,  
> I want each live sale to show its discount and the time left,  
> so that I can decide quickly what to look at before it ends.

- **Priority:** Must
- **Status:** Needs Clarification (Q-23, Q-24, Q-77)
- **MVP class:** MVP · **Sprint:** S2
- **Dependencies:** US-016, US-046
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-CMP-01 (CONFIRMED), FR-CMP-02 (CONFIRMED), FR-CMP-03 (CONFIRMED), FR-CMP-04 (CONFIRMED)
- **API:** `GET /api/v1/landings/{landing}`; `GET /api/v1/campaigns/{campaign}`
- **Tables:** `campaigns`, `campaign_articles`

**Acceptance criteria**
1. *Functional.* Given a live campaign, then its tile shows name, image, headline and "Se termine dans <countdown>", and activating the tile heading opens `/campaigns/{id}` (AC-FR-CMP-01-1, AC-FR-CMP-01-2).
2. *Functional.* Given ≥ 1 day remaining, then the countdown reads like "2 jours 7 heures 25 minutes"; given < 1 day, then it shows hours, minutes and seconds (AC-FR-CMP-02-1, AC-FR-CMP-02-2).
3. *Functional.* Given a percentage headline, then the tile shows "Économisez jusqu'à* N%" and the footer defines "*" against the RRP (AC-FR-CMP-03-1).
4. *Functional.* Given a campaign flagged "Special", then the tile shows "Special" and no percentage (AC-FR-CMP-04-1).
5. *Functional.* Given a wrong client clock, then the countdown still matches server time, computed from `meta.server_time`.
6. *Pending.* How N is computed (Q-23), when "Special" applies (Q-24) and the rounding rule (Q-77) need PO answers before acceptance.

**Tasks:** T-017-B1, T-017-BT1, T-017-F1, T-017-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-017))

### US-018 — Upcoming sales
- **ID:** US-018 · **Epic:** E05 · **Phase 04 story:** US-14

> As a member,  
> I want to see upcoming sales and when they start,  
> so that I can come back at the right time.

- **Priority:** Should
- **Status:** Needs Clarification (Q-35, Q-74, Q-27)
- **MVP class:** Post-MVP · **Sprint:** S6
- **Dependencies:** US-016, US-017
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-UPC-01 (CONFIRMED), FR-UPC-02 (CONFIRMED), FR-UPC-03 (CONFIRMED)
- **API:** `GET /api/v1/campaigns/upcoming`
- **Tables:** `campaigns`

**Acceptance criteria**
1. *Functional.* Given an upcoming campaign within the horizon, then it appears in "Prochainement" with name, image and start time, and its tile has no link (AC-FR-UPC-01-1, AC-FR-UPC-01-2).
2. *Functional.* Given a start within `upcoming_relative_threshold_minutes`, then the tile reads "Démarre dans …"; beyond it, "Démarre le <day> <date> <hh:mm>" (AC-FR-UPC-02-1, AC-FR-UPC-02-2).
3. *Functional.* Given two landings that show the section, then both show the same entries in the same order (AC-FR-UPC-03-1).
4. *Authorization.* Given a direct request for an upcoming campaign, then 404 (PROPOSED server-side rule).
5. *Pending.* The threshold (Q-35), the horizon (Q-74) and global vs per-landing (Q-27) are PO values.

**Tasks:** T-018-B1, T-018-BT1, T-018-F1, T-018-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-018))

### US-019 — Merchandised order in a landing grid
- **ID:** US-019 · **Epic:** E05 · **Phase 04 story:** US-23

> As a member,  
> I want the landing grid to follow the order the store sets,  
> so that featured sales are shown first.

- **Priority:** Could
- **Status:** Needs Clarification (Q-50)
- **MVP class:** Blocked · **Sprint:** S7
- **Dependencies:** US-016
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-EVT-03 (HYPOTHESIS)
- **API:** none
- **Tables:** `landing_campaigns`
- **Note:** HYPOTHESIS, pending PO validation.

**Acceptance criteria**
1. *Functional.* Given an order set for a landing, then the grid renders campaigns in that order (AC-FR-EVT-03-1); campaigns without a position follow by `starts_at` descending (PROPOSED).

**Tasks:** T-019-B1, T-019-BT1 ([05-technical-tasks.md](05-technical-tasks.md#us-019))

### US-020 — Curated landing template
- **ID:** US-020 · **Epic:** E05 · **Phase 04 story:** US-23

> As a member,  
> I want curated landings to show only their own content,  
> so that the page stays focused.

- **Priority:** Could
- **Status:** Needs Clarification (Q-16)
- **MVP class:** Blocked · **Sprint:** S7
- **Dependencies:** US-016, US-018, US-051
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-EVT-05 (HYPOTHESIS)
- **API:** `GET /api/v1/landings/{landing}`
- **Tables:** none
- **Note:** HYPOTHESIS, pending PO validation.

**Acceptance criteria**
1. *Functional.* Given a landing of the curated type, then neither the upcoming section nor the footer is rendered (AC-FR-EVT-05-1), driven by `sections.upcoming` and `sections.footer` from the API.

**Tasks:** T-020-B1, T-020-F1, T-020-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-020))

### US-021 — Refuse reservations outside a campaign's live window
- **ID:** US-021 · **Epic:** E05 · **Phase 04 story:** US-23

> As a member,  
> I want ended sales to stop accepting reservations,  
> so that I don't reserve something that can no longer be sold.

- **Priority:** Could
- **Status:** Needs Clarification (Q-17, Q-21)
- **MVP class:** Blocked · **Sprint:** S7
- **Dependencies:** US-037
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-CMP-08 (HYPOTHESIS)
- **API:** `POST /api/v1/cart/items`; `GET /api/v1/campaigns/{campaign}`; `GET /api/v1/campaigns/{campaign}/articles/{sku}`
- **Tables:** none
- **Note:** HYPOTHESIS, built behind the `enforce_live_window_on_add` switch. The 410 `campaign_ended` reads are PROPOSED (Q-17).

**Acceptance criteria**
1. *Functional.* Given the switch on and an article whose campaign has ended, when the member tries to add it, then 409 `campaign_not_live` and nothing is reserved (AC-FR-CMP-08-1).
2. *Functional.* Given an ended campaign or article URL, then the API answers 410 `campaign_ended` and the SPA shows a message with a link to `/event` (PROPOSED).
3. *Functional.* Given the switch off, then the add guard is skipped.

**Tasks:** T-021-B1, T-021-BT1, T-021-F1, T-021-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-021))

### US-022 — Ask to be notified when a sale starts
- **ID:** US-022 · **Epic:** E05 · **Phase 04 story:** US-15

> As a member,  
> I want to be notified when an upcoming sale starts,  
> so that I don't miss it.

- **Priority:** Could
- **Status:** Blocked (Q-47, Q-01)
- **MVP class:** Blocked · **Sprint:** Backlog (unscheduled)
- **Dependencies:** US-018
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-UPC-04 (HYPOTHESIS)
- **API:** none
- **Tables:** none
- **Note:** HYPOTHESIS and not designed in Phase 05 (api-contracts §11). No tasks until Q-47 gives the channel, stored data, confirmation and unsubscribe rules.

**Acceptance criteria**
1. *Pending.* Given an upcoming tile, then an "Informez moi" button is shown (AC-FR-UPC-04-1). The behaviour behind it is not specified and must not be invented.

**Tasks:** none until the open questions are answered ([05-technical-tasks.md](05-technical-tasks.md#us-022))


## E06 — Product Listing

### US-023 — Campaign listing with tiles and pages
- **ID:** US-023 · **Epic:** E06 · **Phase 04 story:** US-01, US-08

> As a member,  
> I want to open a sale and browse its articles with RRP and sale price,  
> so that I can find what I want inside the sale.

- **Priority:** Must
- **Status:** Needs Clarification (Q-11, Q-12)
- **MVP class:** MVP · **Sprint:** S3
- **Dependencies:** US-015, US-017, US-064
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-CAT-01 (CONFIRMED), FR-CAT-02 (CONFIRMED), FR-CAT-09 (CONFIRMED), FR-CMP-06 (CONFIRMED)
- **API:** `GET /api/v1/campaigns/{campaign}`; `GET /api/v1/articles`
- **Tables:** `campaign_articles`, `products`, `product_images`, `brands`

**Acceptance criteria**
1. *Functional.* Given `/campaigns/{id}` for a live campaign, then the header shows its name and the listing shows the shared tile, filter and sort controls (AC-FR-CAT-01-1).
2. *Functional.* Given any listing, then each tile shows the RRP and the sale price with their labels and links to `/campaigns/{campaignId}/articles/{sku}` (AC-FR-CAT-02-1, AC-FR-CMP-06-1).
3. *Functional.* Given a context with more than 60 products, then 60 tiles are rendered on first load and `meta.page.total` gives the full count (AC-FR-CAT-09-1).
4. *Validation.* Given `page[size]` above `listing_page_size` or an unknown query key, then 422 `validation_failed`.
5. *Error.* Given an unknown, draft or not-yet-started campaign, then 404; given an ended campaign, then 410 `campaign_ended` (PROPOSED, Q-17).
6. *Pending.* Getting past the first page (control or infinite scroll, Q-11) and the countdown placement on the campaign page (Q-12) are open; the API is page-based either way.

**Tasks:** T-023-B1, T-023-BT1, T-023-F1, T-023-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-023))

### US-024 — Sort a listing
- **ID:** US-024 · **Epic:** E06 · **Phase 04 story:** US-04

> As a member,  
> I want to sort a listing,  
> so that I see the cheapest, newest or most discounted articles first.

- **Priority:** Must
- **Status:** Needs Clarification (Q-43)
- **MVP class:** MVP · **Sprint:** S3
- **Dependencies:** US-023
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-CAT-03 (CONFIRMED)
- **API:** `GET /api/v1/articles`
- **Tables:** none

**Acceptance criteria**
1. *Functional.* Given a fresh listing, then "Préférés" is selected (AC-FR-CAT-03-1).
2. *Functional.* When the member opens "Tri", then exactly the five options are shown as radios, with Retirer, Fermer and Appliquer (AC-FR-CAT-03-2); applying one reloads the listing in that order.
3. *Validation.* Given a `sort` value outside the five, then 422.
4. *Pending.* What "Préférés" ranks by is UNKNOWN (Q-43); the placeholder is merchandising position, then newest.

**Tasks:** T-024-B1, T-024-BT1, T-024-F1, T-024-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-024))

### US-025 — Filter a listing
- **ID:** US-025 · **Epic:** E06 · **Phase 04 story:** US-05, US-03

> As a member,  
> I want to filter a listing by category, size, brand, composition, colour and price,  
> so that I only see articles I can buy.

- **Priority:** Must
- **Status:** Needs Clarification (Q-14)
- **MVP class:** MVP · **Sprint:** S3
- **Dependencies:** US-023
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-CAT-04 (CONFIRMED)
- **API:** `GET /api/v1/articles`; `GET /api/v1/articles/facets`
- **Tables:** `attributes`, `attribute_values`, `product_attribute_values`, `product_variants`

**Acceptance criteria**
1. *Functional.* Given a campaign listing, then the filter group shows catégories, Taille, Marque, Composition, Couleur, Prix and Tri (AC-FR-CAT-04-1).
2. *Functional.* Given a filter applied, then the listing reloads with only matching live articles and the filter state is in the URL (PROPOSED, frontend-architecture), so the page can be reloaded or shared.
3. *Validation.* Given `filter[price_min]` above `filter[price_max]`, then 422 with field code `min_greater_than_max`; given an unknown size, brand or attribute value, then 422.
4. *Functional.* Given facet counts, then each facet's counts ignore that facet's own filter.
5. *Pending.* The content of the Marque, Couleur, Composition and catégories dialogs wasn't observed (Q-14); the facets return what the data holds.

**Tasks:** T-025-B1, T-025-BT1, T-025-F1, T-025-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-025))

### US-026 — Filter dialog refinements
- **ID:** US-026 · **Epic:** E06 · **Phase 04 story:** US-05

> As a member,  
> I want to see the result count before applying, pick sizes by system, set a price range and reset filters,  
> so that filtering is quick and predictable.

- **Priority:** Should
- **Status:** Ready
- **MVP class:** Post-MVP · **Sprint:** S6
- **Dependencies:** US-025
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-CAT-05 (CONFIRMED), FR-CAT-06 (CONFIRMED), FR-CAT-07 (CONFIRMED), FR-CAT-08 (CONFIRMED)
- **API:** `GET /api/v1/articles/facets`
- **Tables:** none

**Acceptance criteria**
1. *Functional.* Given an open filter dialog, then the confirm button reads "Afficher <N> résultats" from the facet total, and the listing doesn't change until it is pressed (AC-FR-CAT-05-1).
2. *Functional.* Given a shoe campaign, when "Taille" opens, then a "Chaussures" tab lists size checkboxes, including half sizes (AC-FR-CAT-06-1).
3. *Functional.* Given prices spanning 8–299 €, when "Prix" opens, then Prix minimum shows 8 and Prix maximum shows 299 (AC-FR-CAT-07-1), labelled "Max", never "Maks".
4. *Functional.* Given a dialog with no selection, then "Retirer" is disabled; given active filters, then "Vos filtres" shows the article count with "Réinitialiser" (AC-FR-CAT-08-1, AC-FR-CAT-08-2).

**Tasks:** T-026-F1, T-026-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-026))

### US-027 — "Réservé" state on tiles
- **ID:** US-027 · **Epic:** E06 · **Phase 04 story:** US-22

> As a member,  
> I want to see when an article's remaining units are all reserved,  
> so that I know whether it's worth waiting.

- **Priority:** Must
- **Status:** Needs Clarification (Q-45)
- **MVP class:** MVP · **Sprint:** S3
- **Dependencies:** US-023, US-036
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-STK-01 (CONFIRMED)
- **API:** `GET /api/v1/articles`
- **Tables:** `inventory_items`

**Acceptance criteria**
1. *Functional.* Given an article with no available unit but at least one reserved unit, then its tile shows "Réservé" (AC-FR-STK-01-2, PROPOSED `availability` rule).
2. *Functional.* Given an article with no available and no reserved unit, then its tile shows the sold-out state (copy UNKNOWN).
3. *Pending.* Exactly when "Réservé" applies (all units, some units, or the member's own) is Q-45.

**Tasks:** T-027-B1, T-027-BT1, T-027-F1, T-027-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-027))

### US-028 — "Dernières heures" badge
- **ID:** US-028 · **Epic:** E06 · **Phase 04 story:** US-22

> As a member,  
> I want tiles to warn me when a sale is about to end,  
> so that I act in time.

- **Priority:** Could
- **Status:** Needs Clarification (Q-36)
- **MVP class:** Blocked · **Sprint:** S7
- **Dependencies:** US-023, US-017
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-STK-03 (HYPOTHESIS)
- **API:** none
- **Tables:** none
- **Note:** HYPOTHESIS, behind the `last_hours_badge` switch.

**Acceptance criteria**
1. *Functional.* Given the switch on and a campaign ending within `last_hours_badge_threshold_hours`, then its tiles show "Dernières heures" (AC-FR-STK-03-1), computed from `campaign_ends_at` and server time.
2. *Functional.* Given the switch off, then no badge is shown.

**Tasks:** T-028-B1, T-028-F1, T-028-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-028))

### US-029 — Link-source attribution
- **ID:** US-029 · **Epic:** E06 · **Phase 04 story:** new

> As a product owner,  
> I want product links to carry the surface they were clicked from,  
> so that consent-gated analytics can attribute traffic.

- **Priority:** Could
- **Status:** Ready
- **MVP class:** Post-MVP · **Sprint:** S7
- **Dependencies:** US-023
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-ANL-01 (CONFIRMED)
- **API:** none
- **Tables:** none

**Acceptance criteria**
1. *Functional.* Given a product link in a campaign listing, then it carries the source surface as a neutral `src=` parameter (AC-FR-ANL-01-1, frontend-architecture §15.1).
2. *Functional.* Given the parameter, then the API ignores it, so responses and cache keys don't change.
3. *Privacy.* Given no analytics consent, then the parameter is not passed to any analytics tool (NFR-PRV-01).

**Tasks:** T-029-F1, T-029-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-029))


## E07 — Product Details

### US-030 — Product detail page
- **ID:** US-030 · **Epic:** E07 · **Phase 04 story:** US-08

> As a member,  
> I want to see an article's prices, sizes, details, delivery window and returns,  
> so that I can decide whether to buy.

- **Priority:** Must
- **Status:** Needs Clarification (Q-31, Q-49)
- **MVP class:** MVP · **Sprint:** S4
- **Dependencies:** US-023, US-036
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-PDP-01 (CONFIRMED), FR-PDP-06 (CONFIRMED), FR-PDP-07 (CONFIRMED), FR-PDP-08 (CONFIRMED), FR-PRC-01 (CONFIRMED)
- **API:** `GET /api/v1/campaigns/{campaign}/articles/{sku}`
- **Tables:** `campaign_articles`, `products`, `product_variants`, `product_images`, `inventory_items`

**Acceptance criteria**
1. *Functional.* Given an article URL, then every element listed in FR-PDP-01 is present (AC-FR-PDP-01-1), and activating the brand heading opens `/brands/{code}` (AC-FR-PDP-01-2).
2. *Functional.* Given an article, then the sale price and the RRP are both shown in French formatting, e.g. "115,00 €" (AC-FR-PRC-01-1).
3. *Functional.* Given a shoe article, then sizes such as "38 2/3" and "39 1/3" are selectable radios (AC-FR-PDP-08-1).
4. *Functional.* Given an article with a lead time, then a delivery window "<date> - <date>" is shown; without one, no window is shown (AC-FR-PDP-06-1; computation Q-49).
5. *Functional.* Given an article, then the returns statement shows the single `return_period_days`, identical to the help and terms pages (AC-FR-PDP-07-1).
6. *Error.* Given an SKU not in this campaign or a not-yet-started campaign, then 404; given an ended campaign, then 410 (PROPOSED).
7. *Caching.* Given the response, then it carries `Cache-Control: no-store` because it contains availability.

**Tasks:** T-030-B1, T-030-BT1, T-030-F1, T-030-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-030))

### US-031 — Low-stock indicator
- **ID:** US-031 · **Epic:** E07 · **Phase 04 story:** US-08, US-22

> As a member,  
> I want to see when only a few units of a size are left,  
> so that I know when to act.

- **Priority:** Must
- **Status:** Needs Clarification (Q-26)
- **MVP class:** MVP · **Sprint:** S4
- **Dependencies:** US-030
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-PDP-05 (CONFIRMED), NFR-I18N-02 (CONFIRMED)
- **API:** `GET /api/v1/campaigns/{campaign}/articles/{sku}`
- **Tables:** `inventory_items`

**Acceptance criteria**
1. *Functional.* Given a size with 0 < available ≤ `low_stock_threshold`, then its label reads "Plus que <N> disponible(s)" with N the current available units (AC-FR-PDP-05-1).
2. *Functional.* Given stock changes, then N reflects the new value on the next render (AC-FR-PDP-05-2); the PDP response is never cached.
3. *Localisation.* Given N = 1, then "Plus que 1 disponible"; given N > 1, then "Plus que N disponibles" (AC-NFR-I18N-02-1).
4. *Pending.* The threshold is a PO setting (Q-26).

**Tasks:** T-031-B1, T-031-BT1, T-031-F1, T-031-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-031))

### US-032 — Size required before adding
- **ID:** US-032 · **Epic:** E07 · **Phase 04 story:** US-09

> As a member,  
> I want to be told to pick a size if I forget,  
> so that I don't reserve the wrong item.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S4
- **Dependencies:** US-030
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-PDP-02 (CONFIRMED)
- **API:** `POST /api/v1/cart/items`
- **Tables:** none

**Acceptance criteria**
1. *Functional.* Given no size selected, when the member clicks "Ajouter au panier", then the alert "Choisissez une taille" is shown and announced to screen readers, and nothing is added (AC-FR-PDP-02-1, AC-NFR-A11Y-01-2).
2. *Validation.* Given a request without `variant_id`, then 422 with field code `size_required` and the message "Choisissez une taille", so the rule holds even if the UI is bypassed.
3. *Validation.* Given a `variant_id` that doesn't belong to the SKU's product, then 422.

**Tasks:** T-032-B1, T-032-BT1, T-032-F1, T-032-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-032))

### US-033 — Button after adding an article
- **ID:** US-033 · **Epic:** E07 · **Phase 04 story:** new

> As a member,  
> I want the add button to reflect that the article is already in my cart,  
> so that I don't add it again by mistake.

- **Priority:** Could
- **Status:** Needs Clarification (Q-46)
- **MVP class:** Blocked · **Sprint:** S7
- **Dependencies:** US-037
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-PDP-04 (HYPOTHESIS)
- **API:** `GET /api/v1/campaigns/{campaign}/articles/{sku}`
- **Tables:** none
- **Note:** HYPOTHESIS, behind the `hide_add_button_after_add` switch. The per-member limit itself is enforced in US-037.

**Acceptance criteria**
1. *Functional.* Given the switch on and the article just added, then "Ajouter au panier" is not offered again for it (AC-FR-PDP-04-1), using `in_cart`.
2. *Pending.* What replaces the button after adding is Q-46.

**Tasks:** T-033-B1, T-033-F1, T-033-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-033))


## E08 — Search

### US-034 — Brand suggestions while typing
- **ID:** US-034 · **Epic:** E08 · **Phase 04 story:** US-06

> As a member,  
> I want brand suggestions as I type in the search box,  
> so that I quickly find a brand's articles in every live sale.

- **Priority:** Must
- **Status:** Needs Clarification (Q-44)
- **MVP class:** MVP · **Sprint:** S5
- **Dependencies:** US-035, US-043
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-SRC-01 (CONFIRMED)
- **API:** `GET /api/v1/search/suggestions`
- **Tables:** `brands`

**Acceptance criteria**
1. *Functional.* Given the member types "nike", then a listbox of brand suggestions such as Nike, Nike Sportswear and Nike SB is shown (AC-FR-SRC-01-1), with at most 10 brands that have live articles.
2. *Functional.* Given the suggestions, when the member selects one, then the search results listing opens (AC-FR-SRC-02-1).
3. *Accessibility.* Given keyboard use, then the input follows the ARIA combobox pattern, with French labels only (NFR-I18N-01; reference defect M08 not copied).
4. *Validation.* Given `q` empty or over 100 characters, then 422.
5. *Pending.* Minimum length, Enter behaviour, other suggestion types and the no-results state are Q-44.

**Tasks:** T-034-B1, T-034-BT1, T-034-F1, T-034-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-034))

### US-035 — Search results across campaigns
- **ID:** US-035 · **Epic:** E08 · **Phase 04 story:** US-06

> As a member,  
> I want a listing of a brand's or query's live articles from every sale,  
> so that I see everything relevant in one place.

- **Priority:** Must
- **Status:** Needs Clarification (Q-44)
- **MVP class:** MVP · **Sprint:** S5
- **Dependencies:** US-023
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-SRC-02 (CONFIRMED)
- **API:** `GET /api/v1/articles`
- **Tables:** `products`

**Acceptance criteria**
1. *Functional.* When the member selects "Nike Sportswear", then `/search-catalog?query=` lists live articles from several campaigns with the shared listing component (AC-FR-SRC-02-1).
2. *Functional.* Given a product or brand change, then `products.search_text` is rebuilt so results stay current (ADR-008).
3. *Validation.* Given `q` longer than 100 characters, then 422.
4. *Pending.* The no-results state and the Enter behaviour are Q-44.

**Tasks:** T-035-B1, T-035-BT1, T-035-F1, T-035-FT1, T-035-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-035))


## E09 — Inventory Management

### US-036 — Atomic stock reservation and release
- **ID:** US-036 · **Epic:** E09 · **Phase 04 story:** new

> As a member,  
> I want a unit I reserve to be truly mine, with stock fair for everyone,  
> so that two members can never hold the same last unit.

- **Priority:** Must
- **Status:** Needs Clarification (Q-70)
- **MVP class:** MVP · **Sprint:** S3
- **Dependencies:** US-015
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** NFR-CON-01 (CONFIRMED), FR-STK-01 (CONFIRMED), FR-STK-02 (CONFIRMED)
- **API:** none
- **Tables:** `inventory_items`
- **Note:** The stock scope, campaign article × size, is PROPOSED (C-07, ADR-005) and depends on Q-70.

**Acceptance criteria**
1. *Concurrency.* Given two members adding the last unit at the same moment, then exactly one reservation succeeds and the other gets 409 `out_of_stock` (AC-NFR-CON-01-1, AC-FR-STK-01-1).
2. *Data.* Given any sequence of reserves and releases, then `quantity_reserved + quantity_sold ≤ quantity_allocated` always holds and `quantity_available` is never negative.
3. *Functional.* Given an article sold out, then its stock isn't replenished within the campaign (AC-FR-STK-02-1): no API endpoint or job raises `quantity_allocated`.
4. *Functional.* Given a release, then `quantity_reserved` decreases by exactly the released quantity, once.

**Tasks:** T-036-B1, T-036-BT1, T-036-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-036))


## E10 — Cart & Reservations

### US-037 — Reserve an article in my cart
- **ID:** US-037 · **Epic:** E10 · **Phase 04 story:** US-10

> As a member,  
> I want to reserve a size in my cart and see the time left,  
> so that nobody else takes it while I decide.

- **Priority:** Must
- **Status:** Needs Clarification (Q-25, Q-32, Q-46, Q-51)
- **MVP class:** MVP · **Sprint:** S4
- **Dependencies:** US-032, US-036, US-002
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-PDP-03 (CONFIRMED), FR-CRT-02 (CONFIRMED), FR-NAV-05 (CONFIRMED)
- **API:** `POST /api/v1/cart/items`; `GET /api/v1/cart`
- **Tables:** `cart_reservations`, `inventory_items`

**Acceptance criteria**
1. *Functional.* Given a size with an available unit, when the member clicks "Ajouter au panier", then 201 with the Cart, a success alert is shown and the cart button reads "Votre panier - 1 Article - <mm> minutes et <ss> secondes restantes" (AC-FR-PDP-03-1, AC-FR-NAV-05-1).
2. *Functional.* Given an item added at T, then the cart title shows remaining = `reservation_minutes` − (now − T), from server time (AC-FR-CRT-02-1; the reference used 20 minutes).
3. *Functional.* Given an empty cart, then the cart button shows no count and no timer (AC-FR-NAV-05-2).
4. *Error.* Given no unit left, then 409 `out_of_stock`, a toast, and the article is refetched.
5. *Error.* Given the member would exceed `max_quantity_per_article`, then 409 `reservation_limit_reached`.
6. *Authorization.* Given no session, then 401 and login with a return to the article; the body never carries a user ID; CSRF is required (419 otherwise).
7. *Functional.* Given an expired, not yet swept reservation for the same SKU and size, when the member adds it again, then the stale row is released first in the same transaction, so stock doesn't leak and the limit isn't hit (ADR-010).
8. *Pending.* Duration (Q-25), per-item or shared timer (Q-32), the limit (Q-46) and the success copy (Q-51) are PO values.

**Tasks:** T-037-B1, T-037-BT1, T-037-F1, T-037-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-037))

### US-038 — View my cart
- **ID:** US-038 · **Epic:** E10 · **Phase 04 story:** US-11

> As a member,  
> I want to see my reserved items, my saving and my total,  
> so that I know what I'm about to order.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S4
- **Dependencies:** US-037
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-CRT-01 (CONFIRMED), FR-CRT-03 (CONFIRMED), FR-CRT-05 (CONFIRMED), FR-CRT-06 (CONFIRMED), FR-PRC-02 (CONFIRMED)
- **API:** `GET /api/v1/cart`
- **Tables:** `cart_reservations`
- **Note:** The packaging fee amount (Q-30) is a setting and is never added to the total: charging it is checkout (OUT OF SCOPE).

**Acceptance criteria**
1. *Functional.* When the member activates "Votre panier", then a dialog opens, the page URL doesn't change and close buttons dismiss it (AC-FR-CRT-01-1, AC-FR-CRT-01-2).
2. *Functional.* Given one reserved item, then its line shows every FR-CRT-03 field under the "Réservé" label (AC-FR-CRT-03-1).
3. *Functional.* Given one line at 115,00 € with RRP 250,00 €, then the summary shows "Vous économisez 135,00 €" and "Somme totale 115,00 €", and the line shows "-54%" (AC-FR-CRT-05-1).
4. *Functional.* Given a non-empty cart, then the summary says "Taxe sur la valeur ajoutée incluse, hors emballage & manutention" (AC-FR-PRC-02-1).
5. *Functional.* Given no reserved items, when the cart opens, then the empty-state text is shown (AC-FR-CRT-06-1); expired reservations are never listed.
6. *Caching.* Given the cart response, then it carries `Cache-Control: no-store` (AC-NFR-SEC-03-1).

**Tasks:** T-038-B1, T-038-BT1, T-038-F1, T-038-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-038))

### US-039 — Remove an item after confirming
- **ID:** US-039 · **Epic:** E10 · **Phase 04 story:** US-12

> As a member,  
> I want to remove an item after a confirmation,  
> so that I don't remove it by mistake, and it goes back to other shoppers.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S4
- **Dependencies:** US-038
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-CRT-04 (CONFIRMED)
- **API:** `DELETE /api/v1/cart/items/{id}`
- **Tables:** `cart_reservations`, `inventory_items`

**Acceptance criteria**
1. *Functional.* When the member clicks remove, then the alertdialog "Retirer l’article du panier" explains the unit becomes available to others, with Supprimer and Annuler (AC-FR-CRT-04-1).
2. *Functional.* When the member confirms "Supprimer", then the item leaves the cart and the unit is available again (AC-FR-CRT-04-2); when they choose "Annuler", the cart is unchanged (AC-FR-CRT-04-3).
3. *Authorization.* Given another member's or an expired reservation ID, then 404, so ownership isn't disclosed; CSRF is required.

**Tasks:** T-039-B1, T-039-BT1, T-039-F1, T-039-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-039))

### US-040 — Release expired reservations
- **ID:** US-040 · **Epic:** E10 · **Phase 04 story:** US-25

> As a member,  
> I want reservations that run out to return their units to stock,  
> so that stock stays fair for everyone.

- **Priority:** Could
- **Status:** Needs Clarification (Q-37, Q-32)
- **MVP class:** Blocked · **Sprint:** S4
- **Dependencies:** US-037
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-CRT-08 (HYPOTHESIS)
- **API:** none
- **Tables:** `cart_reservations`, `inventory_items`
- **Note:** HYPOTHESIS (Phase 04 Could) but an MVP-critical decision: without expiry a reserved unit never returns to other members (C-08, R-01). Built behind `release_expired_reservations`; the PO decides before the MVP release.

**Acceptance criteria**
1. *Functional.* Given the switch on and a reservation older than `reservation_minutes`, then the item is no longer in the cart and the unit is available to others (AC-FR-CRT-08-1).
2. *Functional.* Given the sweep, then it runs every minute and releases expired rows in batches of at most 500, one transaction per batch, emitting `ReservationReleased(expired)`.
3. *Functional.* Given an expired row not yet swept, then it is never shown in the cart and doesn't count against the limit.
4. *Pending.* What the member sees when a reservation expires (Q-37) is not designed; no notification is built.

**Tasks:** T-040-B1, T-040-BT1, T-040-F1, T-040-FT1, T-040-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-040))

### US-041 — Hand-off button from cart to checkout
- **ID:** US-041 · **Epic:** E10 · **Phase 04 story:** US-13

> As a member,  
> I want to go from my cart towards ordering,  
> so that I can order my reserved items.

- **Priority:** Must
- **Status:** Blocked (Q-04, Q-80)
- **MVP class:** MVP · **Sprint:** S5
- **Dependencies:** US-038
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-CRT-07 (CONFIRMED)
- **API:** none
- **Tables:** none
- **Note:** Checkout and payment (M21) are OUT OF SCOPE. This story covers the button only; no order table, endpoint or payment integration is created, and the boundary contracts stay outlines (api-contracts §10).

**Acceptance criteria**
1. *Functional.* Given a non-empty cart, then "Commander (<N> Article)" is shown (AC-FR-CRT-07-1).
2. *Pending.* Where the button leads is undecided: no checkout is in scope (Q-04), and what the MVP does instead is Q-80.
3. *Scope.* Given the codebase, then no checkout or payment endpoint, table or third-party integration exists (FR-OOS-01).

**Tasks:** T-041-F1, T-041-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-041))

### US-042 — Throttle bulk add-to-cart
- **ID:** US-042 · **Epic:** E10 · **Phase 04 story:** new

> As a member,  
> I want automated bulk reservations to be slowed down,  
> so that scripts can't hoard limited stock.

- **Priority:** Could
- **Status:** Needs Clarification (Q-42)
- **MVP class:** Blocked · **Sprint:** S7
- **Dependencies:** US-037
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** NFR-SEC-05 (HYPOTHESIS)
- **API:** `POST /api/v1/cart/items`
- **Tables:** none
- **Note:** HYPOTHESIS. Placeholder rate limits are designed; any bot challenge is pending Q-42.

**Acceptance criteria**
1. *Functional.* Given automated bulk add-to-cart attempts from one session or IP, then they are throttled with 429 `rate_limited` and `Retry-After` (AC-NFR-SEC-05-1).
2. *Pending.* Limit values and any challenge mechanism are Q-42.

**Tasks:** T-042-B1, T-042-BT1, T-042-F1 ([05-technical-tasks.md](05-technical-tasks.md#us-042))


## E11 — Storefront UI

### US-043 — App shell and header
- **ID:** US-043 · **Epic:** E11 · **Phase 04 story:** new (FR-NAV-02 from US-01)

> As a member,  
> I want the same header on every page, with search, account and cart,  
> so that I can always reach them.

- **Priority:** Must
- **Status:** Needs Clarification (Q-75, Q-85)
- **MVP class:** MVP · **Sprint:** S1
- **Dependencies:** US-002, US-046
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-NAV-01 (CONFIRMED), FR-NAV-02 (CONFIRMED)
- **API:** `GET /api/v1/storefront/settings`; `GET /api/v1/me`
- **Tables:** none

**Acceptance criteria**
1. *Functional.* Given any landing, campaign, product, brand or search page, then the header shows the logo, search, "Compte" and "Votre panier" (AC-FR-NAV-01-1).
2. *Functional.* When the member activates the logo, then `/event` opens (AC-FR-NAV-01-2).
3. *Accessibility.* Given keyboard focus at the top of a page, then the skip link "Aller au contenu principal" moves focus to the main content (AC-FR-NAV-01-3).
4. *Functional.* Given any page, then the USP bar shows the configured claims, and a discount claim carries the "*" footnote "Par rapport au prix de vente conseillé" (AC-FR-NAV-02-1).
5. *Pending.* The product name, logo and claim texts are PO content (Q-75, Q-85); no reference branding is used.

**Tasks:** T-043-F1, T-043-FT1, T-043-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-043))

### US-044 — Main navigation by vertical
- **ID:** US-044 · **Epic:** E11 · **Phase 04 story:** US-02

> As a member,  
> I want to switch between verticals from the main navigation,  
> so that I only see the sales relevant to me.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S2
- **Dependencies:** US-016
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-NAV-03 (CONFIRMED)
- **API:** `GET /api/v1/storefront/navigation`
- **Tables:** `landings`

**Acceptance criteria**
1. *Functional.* Given a landing page, then the main nav shows the configured verticals in order, each linking to `/event/{id}` (AC-FR-NAV-03-1).
2. *Functional.* Given a campaign, product, brand or search page, then the main nav is not shown (AC-FR-NAV-03-2).

**Tasks:** T-044-B1, T-044-BT1, T-044-F1, T-044-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-044))

### US-045 — Mega-menu by category
- **ID:** US-045 · **Epic:** E11 · **Phase 04 story:** US-03

> As a member,  
> I want to open a category from a vertical's mega-menu,  
> so that I can shop a product type across sales.

- **Priority:** Should
- **Status:** Ready
- **MVP class:** MVP (pulled in) · **Sprint:** S3
- **Dependencies:** US-044, US-013
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-NAV-04 (CONFIRMED)
- **API:** `GET /api/v1/storefront/navigation`
- **Tables:** `categories`, `landings`
- **Note:** Pulled into the MVP sprints: it is the entry point to category listings (FR-CAT-01, a Must).

**Acceptance criteria**
1. *Functional.* Given a vertical with a mega-menu, when the member hovers its chevron or presses Enter on it, then the panel opens with category columns linking to `/articles/categories/{id}` (AC-FR-NAV-04-1).
2. *Functional.* Given a vertical configured without a mega-menu, then no chevron is shown (AC-FR-NAV-04-2).
3. *Accessibility.* Given keyboard use, then the panel can be opened, navigated and closed with Escape, and focus returns to the chevron (NFR-A11Y-01).

**Tasks:** T-045-B1, T-045-F1, T-045-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-045))

### US-046 — French price, date and countdown formatting
- **ID:** US-046 · **Epic:** E11 · **Phase 04 story:** US-27

> As a member,  
> I want prices, dates and countdowns in correct French formats and on server time,  
> so that what I read is right.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S0
- **Dependencies:** US-059
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** NFR-I18N-03 (CONFIRMED)
- **API:** none
- **Tables:** none

**Acceptance criteria**
1. *Localisation.* Given a price of 115 euros (11500 cents), then it renders as "115,00 €"; given a start date, then it renders like "jeu. 5 novembre 06:00" (AC-NFR-I18N-03-1).
2. *Functional.* Given `meta.server_time` in a response, then the client clock offset is updated and every countdown uses `useServerNow()`.
3. *Functional.* Given money from the API, then it is integer cents with currency EUR, and the client never does floating-point money arithmetic.

**Tasks:** T-046-B1, T-046-F1, T-046-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-046))

### US-047 — Show times in the decided timezone
- **ID:** US-047 · **Epic:** E11 · **Phase 04 story:** new

> As a member,  
> I want every start and end time shown in one defined timezone,  
> so that I know exactly when a sale starts.

- **Priority:** Must
- **Status:** Blocked (Q-10)
- **MVP class:** MVP · **Sprint:** S5
- **Dependencies:** US-046
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** no
- **Source requirements:** NFR-TIME-01 (UNKNOWN)
- **API:** none
- **Tables:** none

**Acceptance criteria**
1. *Functional.* Given a campaign starting at 07:00 Paris, then every member sees the start time in the defined timezone (AC-NFR-TIME-01-1), from the `display_timezone` setting; storage stays UTC.
2. *Pending.* The timezone is UNKNOWN (Q-10); production won't start until it is set.

**Tasks:** T-047-B1, T-047-F1, T-047-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-047))

### US-048 — Responsive layouts
- **ID:** US-048 · **Epic:** E11 · **Phase 04 story:** new

> As a member,  
> I want the store to work on my screen size,  
> so that I can shop on any device.

- **Priority:** Must
- **Status:** Blocked (Q-15)
- **MVP class:** MVP · **Sprint:** S5
- **Dependencies:** US-043
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** no
- **Source requirements:** NFR-RESP-01 (UNKNOWN)
- **API:** none
- **Tables:** none

**Acceptance criteria**
1. *Pending.* Breakpoints and mobile behaviour are to be specified (AC-NFR-RESP-01-1, Q-15). Until then the only check is that layouts follow the mobile-first structure of frontend-architecture §13.

**Tasks:** T-048-F1, T-048-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-048))


## E12 — Content & Help Pages

### US-049 — Help centre
- **ID:** US-049 · **Epic:** E12 · **Phase 04 story:** US-20

> As a member,  
> I want to read help articles by category,  
> so that I find answers about delivery and returns myself.

- **Priority:** Should
- **Status:** Needs Clarification (Q-49, Q-85)
- **MVP class:** MVP (pulled in) · **Sprint:** S5
- **Dependencies:** US-051
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-CNT-02 (CONFIRMED), FR-DLV-01 (CONFIRMED)
- **API:** `GET /api/v1/help`; `GET /api/v1/pages/{path}`
- **Tables:** `help_categories`, `content_pages`
- **Note:** Pulled into the MVP sprints: FR-DLV-02 (a Must) needs the returns help article to state the same period as the PDP and the terms.

**Acceptance criteria**
1. *Functional.* Given the help home, then categories list articles at `/help/<category>/<slug>` (AC-FR-CNT-02-1).
2. *Functional.* Given the delivery help category, then each listed topic has an article (AC-FR-DLV-01-1).
3. *Validation.* Given a path that doesn't match `^[a-z0-9/-]+$` or an unpublished page, then 404.
4. *Security.* Given page bodies, then HTML is sanitised on write and rendered without script execution.
5. *Pending.* The texts are PO content (Q-85), and delivery rules in them depend on Q-49.

**Tasks:** T-049-B1, T-049-BT1, T-049-F1, T-049-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-049))

### US-050 — Terms, legal pages and one return period
- **ID:** US-050 · **Epic:** E12 · **Phase 04 story:** US-20

> As a member,  
> I want clear terms and one consistent return period everywhere,  
> so that I know my rights before buying.

- **Priority:** Must
- **Status:** Needs Clarification (Q-48, Q-31)
- **MVP class:** MVP · **Sprint:** S5
- **Dependencies:** US-049, US-051
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-CNT-03 (CONFIRMED), FR-DLV-02 (CONFIRMED)
- **API:** `GET /api/v1/pages/{path}`
- **Tables:** `content_pages`

**Acceptance criteria**
1. *Functional.* Given the terms page, then the contracting entity, the contract language and the mediator are stated (AC-FR-CNT-03-1).
2. *Functional.* Given the PDP, the returns help article and the terms, then all three state the same period, taken from `return_period_days` (AC-FR-DLV-02-1, C-03).
3. *Pending.* Entity, language and mediator (Q-48) and the period (Q-31) are PO and Legal decisions; reference texts are never copied.

**Tasks:** T-050-B1, T-050-BT1, T-050-F1, T-050-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-050))

### US-051 — Footer and footnotes
- **ID:** US-051 · **Epic:** E12 · **Phase 04 story:** US-20

> As a member,  
> I want a footer that explains every footnote and links to help and legal pages,  
> so that I understand claims such as "jusqu'à*".

- **Priority:** Should
- **Status:** Ready
- **MVP class:** MVP (pulled in) · **Sprint:** S2
- **Dependencies:** US-043
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-CNT-01 (CONFIRMED), FR-CNT-05 (CONFIRMED)
- **API:** none
- **Tables:** none
- **Note:** Pulled into the MVP sprints: the "*" footnotes of FR-NAV-02 and FR-CMP-03 (Musts) are defined in the footer, and the consent link (FR-CNT-04) lives there.

**Acceptance criteria**
1. *Functional.* Given a page with footnote markers, then the footer defines every marker used (AC-FR-CNT-01-1).
2. *Functional.* Given the footer, then app-store badges are shown only when apps exist (AC-FR-CNT-05-1); there are none, so none are shown.
3. *Functional.* Given the footer, then it links to the help, terms and legal pages.

**Tasks:** T-051-F1, T-051-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-051))

### US-052 — Cookie consent and privacy
- **ID:** US-052 · **Epic:** E12 · **Phase 04 story:** US-21

> As a member,  
> I want to manage my cookie consent,  
> so that my personal data is only used as I allow.

- **Priority:** Must
- **Status:** Needs Clarification (Q-86)
- **MVP class:** MVP · **Sprint:** S5
- **Dependencies:** US-051
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** FR-CNT-04 (CONFIRMED), NFR-PRV-01 (CONFIRMED)
- **API:** none
- **Tables:** none

**Acceptance criteria**
1. *Functional.* Given the footer, then a link opens the consent settings (AC-FR-CNT-04-1).
2. *Privacy.* Given a member who refused analytics consent, then no personal attribute (hashed e-mail, name, IDs) is sent to analytics or CRM tools (AC-NFR-PRV-01-1).
3. *Privacy.* Given no consent yet, then no third-party script is loaded (frontend-architecture §15).
4. *Pending.* The consent solution and its purposes are Q-86.

**Tasks:** T-052-F1, T-052-FT1, T-052-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-052))


## E13 — Quality Assurance

### US-053 — Concurrency and time-travel test suite
- **ID:** US-053 · **Epic:** E13 · **Phase 04 story:** new

> As a product owner,  
> I want the stock and lifecycle rules proven by automated tests on MySQL,  
> so that overselling and wrong sale times can't reach members.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S4
- **Dependencies:** US-037, US-039, US-058
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** NFR-CON-01 (CONFIRMED), FR-CMP-05 (CONFIRMED), FR-CRT-02 (CONFIRMED)
- **API:** none
- **Tables:** none

**Acceptance criteria**
1. *Concurrency.* Given N parallel add requests for a size with one unit, then exactly one 201 and N−1 409 `out_of_stock`, and the inventory invariants hold (AC-NFR-CON-01-1).
2. *Lifecycle.* Given request clocks frozen at S−1s, S, E−1s and E, then the campaign state and grid membership match AC-FR-CMP-05-1 and AC-FR-CMP-05-2.
3. *Timer.* Given an item added at T, then `expires_at` equals T + `reservation_minutes` under the chosen timer mode.
4. *Conditional.* If the PO enables FR-CRT-08, then the suite also covers expiry, the sweep racing with remove, and re-adding after expiry before the sweep.

**Tasks:** T-053-BT1, T-053-BT2, T-053-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-053))

### US-054 — Keyboard and screen-reader access to the core journey
- **ID:** US-054 · **Epic:** E13 · **Phase 04 story:** US-27

> As a member,  
> I want to complete the core journey with the keyboard and a screen reader,  
> so that the store is usable for me.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S5
- **Dependencies:** US-039
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** NFR-A11Y-01 (CONFIRMED)
- **API:** none
- **Tables:** none

**Acceptance criteria**
1. *Accessibility.* Given keyboard-only use, then J1 (browse → PDP → add → cart → remove) can be completed (AC-NFR-A11Y-01-1).
2. *Accessibility.* Given an alert such as "Choisissez une taille", then it is announced (AC-NFR-A11Y-01-2).
3. *Accessibility.* Given every image, then it has alt text; given every dialog, then focus is trapped inside and returned on close.

**Tasks:** T-054-F1, T-054-FT1, T-054-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-054))

### US-055 — French-only, correctly pluralised copy
- **ID:** US-055 · **Epic:** E13 · **Phase 04 story:** US-27

> As a member,  
> I want the whole store in correct French,  
> so that nothing confuses me.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S5
- **Dependencies:** US-039
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** NFR-I18N-01 (CONFIRMED), NFR-I18N-02 (CONFIRMED)
- **API:** none
- **Tables:** none

**Acceptance criteria**
1. *Localisation.* Given any page, then no label is in another language, e.g. no German ARIA labels, and "Max", not "Maks" (AC-NFR-I18N-01-1).
2. *Localisation.* Given N = 1, then "Plus que 1 disponible"; given N > 1, then "Plus que N disponibles" (AC-NFR-I18N-02-1).
3. *Localisation.* Given API messages, then they come from `lang/fr` and are plural-aware.

**Tasks:** T-055-B1, T-055-F1, T-055-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-055))

### US-056 — Performance targets and load test
- **ID:** US-056 · **Epic:** E13 · **Phase 04 story:** new

> As a product owner,  
> I want agreed performance targets checked by a load test,  
> so that launches don't fail under traffic.

- **Priority:** Must
- **Status:** Blocked (Q-42, Q-72)
- **MVP class:** MVP · **Sprint:** S5
- **Dependencies:** US-039, US-035
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** NFR-PERF-01 (UNKNOWN)
- **API:** none
- **Tables:** none

**Acceptance criteria**
1. *Pending.* Targets are set by the PO (AC-NFR-PERF-01-1, Q-42). Once set, a load test of the launch journey (landing, listing, add to cart) on a production-like environment meets them.

**Tasks:** T-056-BT1, T-056-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-056))


## E14 — Infrastructure & Deployment

### US-057 — Local Docker environment
- **ID:** US-057 · **Epic:** E14 · **Phase 04 story:** new

> As a developer,  
> I want one documented command sequence to start the whole stack locally,  
> so that everyone works on the same environment.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S0
- **Dependencies:** none
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** NFR-TECH-01 (CONFIRMED)
- **API:** none
- **Tables:** none

**Acceptance criteria**
1. *Functional.* Given a fresh clone on Windows 11 (WSL2) or Linux, when the developer follows docker-architecture §7, then nginx, app, scheduler, queue, mysql and vite start and the SPA loads on `http://localhost:8080`.
2. *Functional.* Given the stack, then `/api/*`, `/sanctum/*` and `/up` reach Laravel through Nginx on the same origin.
3. *Health.* Given each service, then its health check from docker-architecture §6 passes.
4. *Optional.* Given the `redis` or `mail` profile, then Redis or Mailpit starts; without them the database drivers are used.
5. *Scope.* Nothing for checkout or payment is included.

**Tasks:** T-057-BT1, T-057-I1, T-057-I2 ([05-technical-tasks.md](05-technical-tasks.md#us-057))

### US-058 — Continuous integration
- **ID:** US-058 · **Epic:** E14 · **Phase 04 story:** new

> As a developer,  
> I want every change checked automatically before it is merged,  
> so that regressions and forbidden dependencies are caught early.

- **Priority:** Must
- **Status:** Needs Clarification (Q-82)
- **MVP class:** MVP · **Sprint:** S0
- **Dependencies:** US-057
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** NFR-TECH-01 (CONFIRMED)
- **API:** none
- **Tables:** none

**Acceptance criteria**
1. *Functional.* Given a pull request, then CI runs the PHP and JS linters, the back-end tests on MySQL 8, the front-end tests and the SPA build, and blocks the merge on failure.
2. *Functional.* Given the module dependency rules (backend-architecture §2), then an architecture test fails the build on a forbidden dependency.
3. *Functional.* Given the documentation, then the Phase 05 design-validation checks and the Phase 06 backlog checks (`tools/phase06/gen.py`) run as scripts.
4. *Pending.* The Git host and CI platform are Q-82.

**Tasks:** T-058-BT1, T-058-I1, T-058-I2 ([05-technical-tasks.md](05-technical-tasks.md#us-058))

### US-059 — API foundation
- **ID:** US-059 · **Epic:** E14 · **Phase 04 story:** new

> As a developer,  
> I want one versioned API base with a uniform error format, request IDs, server time and fail-closed settings,  
> so that every endpoint is built the same way.

- **Priority:** Must
- **Status:** Done
- **MVP class:** MVP · **Sprint:** S0
- **Dependencies:** US-057
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** NFR-OBS-01 (CONFIRMED), NFR-TECH-01 (CONFIRMED)
- **API:** `GET /api/v1/storefront/settings`
- **Tables:** none

**Acceptance criteria**
1. *Functional.* Given any error, then the body is `application/problem+json` with `type`, `title`, `status`, `detail`, `instance`, `code` and `request_id`, and never a stack trace or SQL (error-format §1).
2. *Observability.* Given a failed API call, then it can be correlated with a server log entry by request ID, echoed in `X-Request-Id` (AC-NFR-OBS-01-1).
3. *Functional.* Given any success response, then `meta.server_time` is present in UTC.
4. *Functional.* Given production with an unset policy value, then boot fails or `GET /storefront/settings` answers 503 `settings_incomplete`; no silent default is used.
5. *Validation.* Given an unknown query parameter on any endpoint, then 422 `validation_failed`.

**Tasks:** T-059-B1, T-059-BT1, T-059-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-059))

### US-060 — Transport security and caching headers
- **ID:** US-060 · **Epic:** E14 · **Phase 04 story:** new

> As a member,  
> I want my session and data protected in transit and in the browser cache,  
> so that nobody else can read or reuse them.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S0
- **Dependencies:** US-057
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** NFR-SEC-03 (CONFIRMED), NFR-SEC-04 (CONFIRMED), NFR-CACHE-01 (CONFIRMED)
- **API:** none
- **Tables:** none

**Acceptance criteria**
1. *Security.* Given any response, then HSTS is present; given HTML, then framing is restricted and a CSP is sent (AC-NFR-SEC-04-1). HSTS is checked in the production-like mode, not on local HTTP.
2. *Security.* Given a cart or account response, then it carries `Cache-Control: no-store` (AC-NFR-SEC-03-1).
3. *Caching.* Given a built asset, then its file name contains a content hash and it is served with a one-year immutable cache (AC-NFR-CACHE-01-1).

**Tasks:** T-060-B1, T-060-BT1, T-060-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-060))

### US-061 — MySQL behaviour spike
- **ID:** US-061 · **Epic:** E14 · **Phase 04 story:** new

> As a development team,  
> I want the three unverified MySQL behaviours checked before the schema is frozen,  
> so that migrations neither fail nor silently differ from the design.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S0
- **Dependencies:** US-057
- **Waits on (Blocked ancestors):** — · **Startable once its dependencies are done:** yes
- **Source requirements:** NFR-CON-01 (CONFIRMED), NFR-TECH-01 (CONFIRMED)
- **API:** none
- **Tables:** none

**Acceptance criteria**
1. *Spike.* Given the MySQL 8 version chosen for production, then the team records whether CHECK constraints are accepted on tables whose columns also carry FK referential actions, and adjusts the affected constraints.
2. *Spike.* Given the generated columns that subtract unsigned values (`quantity_available`, `discount_percent`), then the behaviour at the boundaries (zero, reserved + sold = allocated, a rejected over-reserve) is recorded, and the design is adjusted if an intermediate negative value raises a different error than designed.
3. *Spike.* Given the composite FKs through `product_id`, then they are created and enforced as designed.
4. *Outcome.* Given any difference, then it is recorded as an erratum to schema.md and approved by the architect before US-012 and US-015 freeze their migrations.

**Tasks:** T-061-BT1, T-061-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-061))


## E15 — Recommendations

### US-062 — Recommendation rails on the default landing
- **ID:** US-062 · **Epic:** E15 · **Phase 04 story:** US-16

> As a member,  
> I want recommendations on the default landing,  
> so that I discover relevant products.

- **Priority:** Should
- **Status:** Needs Clarification (Q-29)
- **MVP class:** Post-MVP · **Sprint:** S7
- **Dependencies:** US-016, US-023
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-REC-01 (CONFIRMED)
- **API:** `GET /api/v1/recommendations/{rail}`; `GET /api/v1/articles`
- **Tables:** none

**Acceptance criteria**
1. *Functional.* Given `/event`, then the personal rails are requested; given a vertical landing, then neither rail is shown (AC-FR-REC-01-2).
2. *Functional.* Given a rail with items, then it is shown and "Voir tout" opens `/campaigns/top-picks/articles` (AC-FR-REC-01-1).
3. *Functional.* Given an empty rail (the v1 `ForYou` and `ContinueShopping` bindings), then the UI hides it.
4. *Pending.* The logic is UNKNOWN (Q-29); AC-FR-REC-01-1 ("both rails are shown") can't pass until a provider returns items.

**Tasks:** T-062-B1, T-062-BT1, T-062-F1, T-062-FT1 ([05-technical-tasks.md](05-technical-tasks.md#us-062))

### US-063 — Popular rail
- **ID:** US-063 · **Epic:** E15 · **Phase 04 story:** US-16

> As a member,  
> I want a rail of popular live articles on landings,  
> so that I see what others are reserving.

- **Priority:** Should
- **Status:** Needs Clarification (Q-78, Q-29)
- **MVP class:** Post-MVP · **Sprint:** S7
- **Dependencies:** US-062, US-037
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-REC-02 (CONFIRMED)
- **API:** `GET /api/v1/recommendations/{rail}`
- **Tables:** `article_reservation_stats`

**Acceptance criteria**
1. *Functional.* Given a landing with the popular rail and at least 12 qualifying articles, then the rail shows 12 live product tiles linking to article URLs (AC-FR-REC-02-1).
2. *Functional.* Given reservations in the last 24 hours, then the PROPOSED v1 strategy ranks articles by reservation count, with no regional dimension (Q-78).
3. *Functional.* Given stats older than 7 days, then the daily prune removes them.

**Tasks:** T-063-B1, T-063-BT1, T-063-F1, T-063-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-063))


## E16 — Data Administration

### US-064 — Synthetic seed data
- **ID:** US-064 · **Epic:** E16 · **Phase 04 story:** new

> As a development team,  
> I want realistic synthetic data in every campaign state,  
> so that features can be built, tested and demonstrated without real or reference data.

- **Priority:** Must
- **Status:** Ready
- **MVP class:** MVP · **Sprint:** S2
- **Dependencies:** US-012, US-015
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-EVT-01 (CONFIRMED), FR-CMP-09 (CONFIRMED), FR-CAT-01 (CONFIRMED)
- **API:** none
- **Tables:** none
- **Note:** Enabler (C-16, docker-architecture §7). It adds no business rule.

**Acceptance criteria**
1. *Data.* Given `php artisan migrate --seed`, then brands, categories, size systems, products, landings, campaigns (upcoming, live and ended), campaign articles and stock exist.
2. *Data.* Given the seed data, then no brand name, image, text or identifier comes from the reference platform.
3. *Data.* Given the seed data, then at least one listing has more than 60 articles (FR-CAT-09) and at least one size has exactly one unit (for the NFR-CON-01 tests).
4. *Data.* Given placeholder content for UNKNOWN values (reasons, legal texts), then it is visibly labelled as a placeholder.

**Tasks:** T-064-B1, T-064-BT1, T-064-I1 ([05-technical-tasks.md](05-technical-tasks.md#us-064))

### US-065 — Production data entry
- **ID:** US-065 · **Epic:** E16 · **Phase 04 story:** US-23, US-24 (HYPOTHESIS merchandiser persona)

> As a product owner,  
> I want a way to load real brands, products, campaigns and stock,  
> so that the store can open with real sales.

- **Priority:** Must
- **Status:** Blocked (Q-50, Q-80)
- **MVP class:** MVP · **Sprint:** Backlog (unscheduled)
- **Dependencies:** US-012, US-015
- **Waits on (Blocked ancestors):** US-012 (Q-70, Q-84); US-015 (Q-70, Q-77, Q-20, Q-33) · **Startable once its dependencies are done:** no
- **Source requirements:** FR-CMP-05 (CONFIRMED), FR-CMP-09 (CONFIRMED)
- **API:** none
- **Tables:** none
- **Note:** Release gate for any launch with real products. No back-office, import format or admin UI is designed (C-16). If Q-80 makes the MVP an internal demo, seed data (US-064) is enough.

**Acceptance criteria**
1. *Pending.* No acceptance criteria can be written until Q-50 says who enters which data, how (back-office, import file or API) and with which roles; `merchandiser` and `admin` are HYPOTHESIS roles.

**Tasks:** none until the open questions are answered ([05-technical-tasks.md](05-technical-tasks.md#us-065))
