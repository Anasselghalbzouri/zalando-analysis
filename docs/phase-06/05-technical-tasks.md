# Technical tasks — Phase 06

Tasks are listed per story and grouped into Backend, Frontend and Infrastructure. Test tasks are marked "(test)".

**Task IDs:** `T-<story number>-<type><n>`, where the types are:
- `B` backend
- `BT` backend test
- `F` frontend
- `FT` frontend test
- `I` infrastructure

**Not duplicated:** shared foundations are built once and reused, not repeated per story:
- the API base, error renderer, request ID, server time and settings (US-059)
- the API client, auth state and app shell (US-002, US-043)
- the shared listing feature (US-023)
- the MySQL test harness (US-053, US-058)
- formatting and the server clock (US-046)

**Scope of a task:** each task names the Phase 05 building block it implements: module, controller, Form Request, action, query object, component or service. The task list is a plan: no code, migrations, SQL, Docker files or components are written in this phase.



## E01 — Authentication & Identity

### US-001
**Register as an adult member** · Needs Clarification · S1 · API: `GET /sanctum/csrf-cookie`; `POST /api/v1/auth/register`

- **Backend:**
  - T-001-B1: Identity module: `RegistrationController`, `RegisterRequest`, `RegisterMember` action recording the accepted terms version
  - T-001-B2: Password hashing and the breached-password check (PROPOSED; drop it if the PO rejects it)
  - T-001-BT1 (test): Feature tests: success, duplicate e-mail, unaccepted checkboxes, missing CSRF, throttling
- **Frontend:**
  - T-001-F1: `/register` page in `AuthLayout`; native HTML validation plus server error mapping (frontend-architecture §11)
  - T-001-F2: RTK Query `register` mutation; on success, set the auth state from the response
  - T-001-FT1 (test): Tests for field error mapping and the guest-only redirect

### US-002
**Log in and log out with a protected session** · Needs Clarification · S1 · API: `GET /sanctum/csrf-cookie`; `POST /api/v1/auth/login`; `POST /api/v1/auth/logout`; `GET /api/v1/me`

- **Backend:**
  - T-002-B1: `SessionController` (login, logout), `LoginRequest` throttled per e-mail and IP, `CurrentUserController`
  - T-002-B2: Session and cookie settings per authentication-design §4 (HttpOnly, Secure, SameSite=Lax); lifetime read from config, value pending Q-63
  - T-002-BT1 (test): Tests: login success, generic failure, throttling, logout invalidation; 419 on every unsafe route (route-list driven); architecture test that no route takes a user ID parameter
- **Frontend:**
  - T-002-F1: API client base query: CSRF cookie bootstrap, refresh and retry once on 419, redirect on 401 (frontend-architecture §8)
  - T-002-F2: `/login` page; auth state loaded from `GET /me` at start-up
  - T-002-FT1 (test): Tests for the single 419 retry and the 401 redirect with return path
- **Infrastructure:**
  - T-002-I1: Database session driver (baseline); env variables for session lifetime and secure cookies

### US-003
**Reset a forgotten password** · Needs Clarification · S1 · API: `POST /api/v1/auth/forgot-password`; `POST /api/v1/auth/reset-password`

- **Backend:**
  - T-003-B1: `PasswordResetController`; reset e-mail queued on `default`
  - T-003-BT1 (test): Tests for non-enumeration, token expiry and session invalidation
- **Frontend:**
  - T-003-F1: `/forgot-password` and `/reset-password/:token` pages
  - T-003-FT1 (test): Form tests
- **Infrastructure:**
  - T-003-I1: `mail` compose profile (Mailpit) for local e-mail capture; mail env variables

### US-004
**Verify my e-mail address** · Needs Clarification · S6 · API: `POST /api/v1/auth/email/verification-notification`; `GET /api/v1/auth/email/verify/{id}/{hash}`

- **Backend:**
  - T-004-B1: `EmailVerificationController`; listener on `Registered` sends the queued e-mail
  - T-004-BT1 (test): Signed-URL tests
- **Frontend:**
  - T-004-F1: `/verify-email` page and resend action
  - T-004-FT1 (test): Tests
- **Infrastructure:**
  - T-004-I1: `queue` service running

### US-005
**Apply one catalogue access mode** · Needs Clarification · S1 · API: `GET /api/v1/storefront/settings`; `GET /api/v1/storefront/navigation`

- **Backend:**
  - T-005-B1: `catalog.access` middleware on every C route; route groups by auth code S, C, P and G
  - T-005-BT1 (test): Route-list test: every `/api/v1` route belongs to exactly one auth-code group
- **Frontend:**
  - T-005-F1: Route guards reading `catalog_access` from the settings
  - T-005-FT1 (test): Guard tests for both modes
- **Infrastructure:**
  - T-005-I1: Env variable for `catalog_access`; production boot check


## E02 — User Account

### US-006
**Account navigation and overview** · Ready · S6 · API: `GET /api/v1/account/overview`

- **Backend:**
  - T-006-B1: `AccountOverviewController` returning the first name and an empty `updates` list
  - T-006-BT1 (test): Auth and no-store tests
- **Frontend:**
  - T-006-F1: `AccountLayout` with `AccountNav`; `/myaccount` redirects to `/myaccount/overview`; `AccountOverviewPage`
  - T-006-FT1 (test): Navigation and empty-state tests

### US-007
**Edit my personal details** · Needs Clarification · S6 · API: `GET /api/v1/account/profile`; `PATCH /api/v1/account/profile`

- **Backend:**
  - T-007-B1: `ProfileController`, `UpdateProfileRequest`
  - T-007-BT1 (test): Validation tests
- **Frontend:**
  - T-007-F1: `ProfilePage` at `/myaccount/personal-details`
  - T-007-FT1 (test): Form tests

### US-008
**Choose which sales e-mails I receive** · Ready · S6 · API: `GET /api/v1/account/newsletter`; `PUT /api/v1/account/newsletter`

- **Backend:**
  - T-008-B1: `NewsletterPreferenceController`, `UpdateNewsletterPreferencesRequest`, `UpdateNewsletterPreferences` action emitting `NewsletterPreferencesUpdated`
  - T-008-BT1 (test): Tests, including the `unsubscribed_at` rule
- **Frontend:**
  - T-008-F1: `NewsletterPage` with dirty-state tracking for "Enregistrer"
  - T-008-FT1 (test): Disabled-until-changed test

### US-009
**Unsubscribe with a reason** · Needs Clarification · S6 · API: `GET /api/v1/account/newsletter/unsubscribe-reasons`; `POST /api/v1/account/newsletter/unsubscribe`

- **Backend:**
  - T-009-B1: `NewsletterUnsubscribeController`, `UnsubscribeRequest`, `Unsubscribe` action
  - T-009-BT1 (test): Validation tests
- **Frontend:**
  - T-009-F1: Unsubscribe section with reason radios
  - T-009-FT1 (test): Enable-on-select test

### US-010
**Save my sizes** · Ready · S6 · API: `GET /api/v1/account/saved-sizes`; `PUT /api/v1/account/saved-sizes`

- **Backend:**
  - T-010-B1: `SavedSizeController`, `SaveSizeProfilesRequest`, `SaveSizeProfiles` action (replace in one transaction)
  - T-010-BT1 (test): Tests for size-system membership and replace semantics
- **Frontend:**
  - T-010-F1: `SavedSizesPage` with tabs and groups
  - T-010-FT1 (test): Form tests
- **Infrastructure:**
  - T-010-I1: Seeders for the garment groups listed in AC-FR-ACC-07-1

### US-011
**Orders and returns entry pages** · Ready · S6

- **Backend:**
  - *No task:* None: no API by design (api-contracts §8)
- **Frontend:**
  - T-011-F1: `OrdersPage`, `ReturnsPage`, `ReturnSelectionPage` with static entry content and no API call
  - T-011-FT1 (test): Route and copy tests


## E03 — Catalog Management

### US-012
**Catalogue data model** · Blocked · S1

- **Backend:**
  - T-012-B1: Migrations, models and factories for the Catalog tables, in per-module folders (backend-architecture §16)
  - T-012-B2: `CategoryTree` service maintaining `path`
  - T-012-B3: Root-slug uniqueness fix per Q-84; record the erratum against schema.md for architect approval
  - T-012-BT1 (test): Constraint tests on MySQL 8, not SQLite


## E04 — Categories & Brands

### US-013
**Browse a category listing** · Ready · S3 · API: `GET /api/v1/categories/{id}`; `GET /api/v1/articles`; `GET /api/v1/articles/facets`

- **Backend:**
  - T-013-B1: `CategoryController` (header and breadcrumb from `path`); `category` context in `ArticleListingQuery` and `FacetQuery` using the `path` subtree
  - T-013-BT1 (test): Query tests on seed data for descendants and live-only filtering
- **Frontend:**
  - T-013-F1: `CategoryListingPage` reusing the shared listing feature
  - T-013-FT1 (test): Page test with a mocked API

### US-014
**Browse a brand page** · Ready · S4 · API: `GET /api/v1/brands/{code}`; `GET /api/v1/articles`

- **Backend:**
  - T-014-B1: `BrandController`; `brand` context in the listing query
  - T-014-BT1 (test): Tests
- **Frontend:**
  - T-014-F1: `BrandListingPage`
  - T-014-FT1 (test): Breadcrumb test


## E05 — Campaign Management

### US-015
**Campaigns and landings with a derived lifecycle** · Blocked · S2

- **Backend:**
  - T-015-B1: Migrations, models and factories for the Sales tables
  - T-015-B2: `LiveCampaigns`: the single live predicate with a request-scoped clock (ADR-009)
  - T-015-B3: `sales:emit-boundaries` scheduled command with a stored watermark; `CampaignStarted` and `CampaignEnded` invalidate landing caches
  - T-015-BT1 (test): Time-travel tests at S−1s, S, E−1s and E (shared harness with US-053)
- **Infrastructure:**
  - T-015-I1: `scheduler` service running `schedule:work`

### US-016
**Event landing pages** · Ready · S2 · API: `GET /api/v1/landings/{landing}`

- **Backend:**
  - T-016-B1: `LandingController` and `LandingView`: section flags, hero, grid ordered by position then `starts_at` descending
  - T-016-BT1 (test): Tests for live-only grids and hero rules
- **Frontend:**
  - T-016-F1: `LandingPage` with `CampaignGrid` and `Hero`; redirect from `/`
  - T-016-FT1 (test): Page tests for the aggregate and vertical types

### US-017
**Campaign tile with headline and countdown** · Needs Clarification · S2 · API: `GET /api/v1/landings/{landing}`; `GET /api/v1/campaigns/{campaign}`

- **Backend:**
  - T-017-B1: `CampaignHeadline` for the computed, manual and special modes of `headline_mode`; `CampaignController` header
  - T-017-BT1 (test): Headline tests for each mode
- **Frontend:**
  - T-017-F1: `CampaignTile` and `Countdown` using `useServerNow`
  - T-017-FT1 (test): Countdown format tests at the 1-day boundary with a mocked server offset

### US-018
**Upcoming sales** · Needs Clarification · S6 · API: `GET /api/v1/campaigns/upcoming`

- **Backend:**
  - T-018-B1: `UpcomingCampaignController` and `UpcomingCampaigns` within `upcoming_horizon_hours`; literal route registered before `/campaigns/{campaign}`
  - T-018-BT1 (test): Route-order test; horizon test
- **Frontend:**
  - T-018-F1: `UpcomingSection` with the relative and absolute formats
  - T-018-FT1 (test): Threshold boundary tests

### US-019
**Merchandised order in a landing grid** · Needs Clarification · S7

- **Backend:**
  - T-019-B1: Use `landing_campaigns.position` in `LandingView` (already in the query order); seed positions
  - T-019-BT1 (test): Ordering test

### US-020
**Curated landing template** · Needs Clarification · S7 · API: `GET /api/v1/landings/{landing}`

- **Backend:**
  - T-020-B1: The `curated` type sets both section flags to false
- **Frontend:**
  - T-020-F1: Layout honours the section flags
  - T-020-FT1 (test): Test

### US-021
**Refuse reservations outside a campaign's live window** · Needs Clarification · S7 · API: `POST /api/v1/cart/items`; `GET /api/v1/campaigns/{campaign}`; `GET /api/v1/campaigns/{campaign}/articles/{sku}`

- **Backend:**
  - T-021-B1: Guard step (1) of `AddToCart` behind the switch; 410 handling in campaign and article reads
  - T-021-BT1 (test): Switch on and off tests
- **Frontend:**
  - T-021-F1: Toast and refetch on `campaign_not_live`; ended-campaign message
  - T-021-FT1 (test): Error-code mapping tests

### US-022
**Ask to be notified when a sale starts** · Blocked · Backlog (unscheduled)

No tasks: the behaviour is not designed and must not be invented (see the story note).


## E06 — Product Listing

### US-023
**Campaign listing with tiles and pages** · Needs Clarification · S3 · API: `GET /api/v1/campaigns/{campaign}`; `GET /api/v1/articles`

- **Backend:**
  - T-023-B1: `ArticleListingController` and `ArticleListingQuery` with the campaign context, live predicate and page-based pagination
  - T-023-BT1 (test): Query tests on seed data; pagination meta and links
- **Frontend:**
  - T-023-F1: Shared listing feature: `ArticleGrid`, `ArticleTile`, query state in the URL; `CampaignListingPage`
  - T-023-FT1 (test): Tile rendering and URL-state tests

### US-024
**Sort a listing** · Needs Clarification · S3 · API: `GET /api/v1/articles`

- **Backend:**
  - T-024-B1: Sort clauses for `newest`, `preferred`, `price_asc`, `price_desc` and `discount`, each backed by an index
  - T-024-BT1 (test): Ordering test per option
- **Frontend:**
  - T-024-F1: `SortDialog`: accessible dialog with radios
  - T-024-FT1 (test): Dialog keyboard test

### US-025
**Filter a listing** · Needs Clarification · S3 · API: `GET /api/v1/articles`; `GET /api/v1/articles/facets`

- **Backend:**
  - T-025-B1: `FacetQuery`; filter clauses in `ArticleListingQuery`; `ListArticlesRequest` validation
  - T-025-BT1 (test): Facet count tests (self-exclusion) and validation tests
- **Frontend:**
  - T-025-F1: `FilterBar` and filter dialogs; URL-to-filter mapping
  - T-025-FT1 (test): URL round-trip tests

### US-026
**Filter dialog refinements** · Ready · S6 · API: `GET /api/v1/articles/facets`

- **Backend:**
  - *No task:* None beyond US-025: facets already return the total, size systems and price bounds
- **Frontend:**
  - T-026-F1: Pending-selection state per dialog; debounced facet total on change; size tabs; price inputs
  - T-026-FT1 (test): Tests for the count button and the "Retirer" state

### US-027
**"Réservé" state on tiles** · Needs Clarification · S3 · API: `GET /api/v1/articles`

- **Backend:**
  - T-027-B1: `availability` computed from inventory in the listing query
  - T-027-BT1 (test): Tests for the three states
- **Frontend:**
  - T-027-F1: Tile badge
  - T-027-FT1 (test): Badge tests

### US-028
**"Dernières heures" badge** · Needs Clarification · S7

- **Backend:**
  - T-028-B1: Switch and threshold exposed in the settings (already in the Settings shape)
- **Frontend:**
  - T-028-F1: Badge logic in `ArticleTile` using `useServerNow`
  - T-028-FT1 (test): Threshold boundary test

### US-029
**Link-source attribution** · Ready · S7

- **Backend:**
  - *No task:* None: the API ignores the parameter
- **Frontend:**
  - T-029-F1: Add `src` to tile links per surface
  - T-029-FT1 (test): Link tests


## E07 — Product Details

### US-030
**Product detail page** · Needs Clarification · S4 · API: `GET /api/v1/campaigns/{campaign}/articles/{sku}`

- **Backend:**
  - T-030-B1: `CampaignArticleController` and `ArticleLookup`; `DeliveryEstimator` seam returning null when no lead time is set
  - T-030-BT1 (test): Tests for the 404 cases and the response shape against api-contracts
- **Frontend:**
  - T-030-F1: `ProductPage`: gallery, price block, `SizeSelector` radios, accordions, delivery and returns blocks
  - T-030-FT1 (test): Rendering tests; keyboard size selection

### US-031
**Low-stock indicator** · Needs Clarification · S4 · API: `GET /api/v1/campaigns/{campaign}/articles/{sku}`

- **Backend:**
  - T-031-B1: `low_stock_quantity` per size from `quantity_available`
  - T-031-BT1 (test): Threshold boundary tests
- **Frontend:**
  - T-031-F1: Size label using `Intl.PluralRules('fr-FR')`
  - T-031-FT1 (test): Plural tests

### US-032
**Size required before adding** · Ready · S4 · API: `POST /api/v1/cart/items`

- **Backend:**
  - T-032-B1: `AddCartItemRequest` rules
  - T-032-BT1 (test): Validation tests
- **Frontend:**
  - T-032-F1: Client-side guard with a live-region alert
  - T-032-FT1 (test): Announcement test

### US-033
**Button after adding an article** · Needs Clarification · S7 · API: `GET /api/v1/campaigns/{campaign}/articles/{sku}`

- **Backend:**
  - T-033-B1: `in_cart` flag on the article detail
- **Frontend:**
  - T-033-F1: Button visibility rule behind the switch
  - T-033-FT1 (test): Test


## E08 — Search

### US-034
**Brand suggestions while typing** · Needs Clarification · S5 · API: `GET /api/v1/search/suggestions`

- **Backend:**
  - T-034-B1: `SearchSuggestionController`; brand-name prefix query
  - T-034-BT1 (test): Tests
- **Frontend:**
  - T-034-F1: `SearchCombobox` in the header, debounced
  - T-034-FT1 (test): Combobox keyboard tests

### US-035
**Search results across campaigns** · Needs Clarification · S5 · API: `GET /api/v1/articles`

- **Backend:**
  - T-035-B1: `q` context with FULLTEXT on `products.search_text`; queued `RebuildProductSearchText` listener
  - T-035-BT1 (test): Search tests on seed data; listener test
- **Frontend:**
  - T-035-F1: `SearchResultsPage`
  - T-035-FT1 (test): Page test
- **Infrastructure:**
  - T-035-I1: `queue` service for the rebuild listener


## E09 — Inventory Management

### US-036
**Atomic stock reservation and release** · Needs Clarification · S3

- **Backend:**
  - T-036-B1: `inventory_items` migration and model; `ReserveUnit` as one conditional atomic update; `ReleaseUnit`; `AvailabilityFor`
  - T-036-BT1 (test): Parallel-connection test on MySQL (harness from US-053); invariant tests
- **Infrastructure:**
  - T-036-I1: MySQL service in CI (US-058)


## E10 — Cart & Reservations

### US-037
**Reserve an article in my cart** · Needs Clarification · S4 · API: `POST /api/v1/cart/items`; `GET /api/v1/cart`

- **Backend:**
  - T-037-B1: `CartItemController@store`; `AddToCart` steps 1–6 (backend-architecture §6.1); `ReservationCreated` emitted after commit
  - T-037-BT1 (test): Tests: success, out of stock, limit reached, re-add after expiry, missing CSRF, 401
- **Frontend:**
  - T-037-F1: Add-to-cart mutation updating the cart cache (RTK Query tags); `CartButton` with count and countdown
  - T-037-FT1 (test): Countdown from the server offset; toast per error code

### US-038
**View my cart** · Ready · S4 · API: `GET /api/v1/cart`

- **Backend:**
  - T-038-B1: `CartController@show`; `GetCart` with active rows only, savings and total in cents
  - T-038-BT1 (test): Money computation tests in integer cents
- **Frontend:**
  - T-038-F1: `CartDrawer`: accessible dialog with focus trap and focus return
  - T-038-FT1 (test): Dialog accessibility tests; summary formatting

### US-039
**Remove an item after confirming** · Ready · S4 · API: `DELETE /api/v1/cart/items/{id}`

- **Backend:**
  - T-039-B1: `CartItemController@destroy`; `RemoveFromCart`; `CartReservationPolicy`
  - T-039-BT1 (test): Ownership and release tests
- **Frontend:**
  - T-039-F1: `ConfirmRemoveDialog` (alertdialog)
  - T-039-FT1 (test): Confirm and cancel tests

### US-040
**Release expired reservations** · Needs Clarification · S4

- **Backend:**
  - T-040-B1: `cart:release-expired` command running `ReleaseExpiredReservations` (backend-architecture §6.1); scheduler entry
  - T-040-BT1 (test): Time-travel tests; sweep racing with remove and re-add (R-05)
- **Frontend:**
  - T-040-F1: Refetch the cart when the header countdown reaches zero
  - T-040-FT1 (test): Test
- **Infrastructure:**
  - T-040-I1: Scheduler health monitoring (R-06)

### US-041
**Hand-off button from cart to checkout** · Blocked · S5

- **Backend:**
  - *No task:* None until Q-04; `ActiveReservationsForCheckout` and `MarkSold` stay contract outlines
- **Frontend:**
  - T-041-F1: "Commander (<N> Article)" button with the target chosen under Q-80
  - T-041-FT1 (test): Label and count test

### US-042
**Throttle bulk add-to-cart** · Needs Clarification · S7 · API: `POST /api/v1/cart/items`

- **Backend:**
  - T-042-B1: Named rate limiter on `POST /cart/items`
  - T-042-BT1 (test): Throttle test
- **Frontend:**
  - T-042-F1: 429 handling that waits for `Retry-After`


## E11 — Storefront UI

### US-043
**App shell and header** · Needs Clarification · S1 · API: `GET /api/v1/storefront/settings`; `GET /api/v1/me`

- **Backend:**
  - *No task:* None beyond the settings endpoint (US-059)
- **Frontend:**
  - T-043-F1: `AppShell`, `Header`, `UspBar`, `ToastRegion`, `StorefrontLayout`, `AuthLayout`; React Router route table; store set-up with the RTK Query API slice and the `ui` and `clock` slices
  - T-043-FT1 (test): Skip-link and header tests
- **Infrastructure:**
  - T-043-I1: Vite build with content-hashed assets (served per US-060)

### US-044
**Main navigation by vertical** · Ready · S2 · API: `GET /api/v1/storefront/navigation`

- **Backend:**
  - T-044-B1: `NavigationController` ordered by `nav_position`
  - T-044-BT1 (test): Test
- **Frontend:**
  - T-044-F1: `MainNav` in the landing layout only
  - T-044-FT1 (test): Test

### US-045
**Mega-menu by category** · Ready · S3 · API: `GET /api/v1/storefront/navigation`

- **Backend:**
  - T-045-B1: `mega_menu` tree built from `menu_category_id`
- **Frontend:**
  - T-045-F1: `MegaMenu` using the disclosure pattern
  - T-045-FT1 (test): Keyboard tests

### US-046
**French price, date and countdown formatting** · Ready · S0

- **Backend:**
  - T-046-B1: Response middleware adding `meta.server_time` (delivered with US-059)
- **Frontend:**
  - T-046-F1: Formatting utilities with `Intl.NumberFormat` and `Intl.DateTimeFormat` for fr-FR; `clock` slice and `useServerNow`
  - T-046-FT1 (test): Formatting unit tests, including the non-breaking spaces that Intl inserts

### US-047
**Show times in the decided timezone** · Blocked · S5

- **Backend:**
  - T-047-B1: `display_timezone` exposed in the settings (US-059)
- **Frontend:**
  - T-047-F1: Pass `display_timezone` to every date formatter
  - T-047-FT1 (test): Tests across a daylight-saving change

### US-048
**Responsive layouts** · Blocked · S5

- **Frontend:**
  - T-048-F1: Layout primitives and per-breakpoint grid columns (frontend-architecture §13); mobile variants once Q-15 is answered
  - T-048-FT1 (test): Visual checks at the decided breakpoints


## E12 — Content & Help Pages

### US-049
**Help centre** · Needs Clarification · S5 · API: `GET /api/v1/help`; `GET /api/v1/pages/{path}`

- **Backend:**
  - T-049-B1: `HelpController`, `ContentPageController`; HTML sanitiser on write
  - T-049-BT1 (test): Sanitisation tests
- **Frontend:**
  - T-049-F1: `HelpPages`
  - T-049-FT1 (test): Route tests

### US-050
**Terms, legal pages and one return period** · Needs Clarification · S5 · API: `GET /api/v1/pages/{path}`

- **Backend:**
  - T-050-B1: Render the period from the setting inside page bodies (PROPOSED placeholder mechanism, Architect to confirm) so the text can't drift from the setting
  - T-050-BT1 (test): Consistency test: the PDP, help and terms show the same number
- **Frontend:**
  - T-050-F1: `ContentPage` for `/terms` and `/legal/:slug`
  - T-050-FT1 (test): Test

### US-051
**Footer and footnotes** · Ready · S2

- **Backend:**
  - *No task:* None
- **Frontend:**
  - T-051-F1: `Footer` with a footnote registry fed by the components that use markers
  - T-051-FT1 (test): Every-marker-defined test

### US-052
**Cookie consent and privacy** · Needs Clarification · S5

- **Backend:**
  - *No task:* None: no analytics integration is designed
- **Frontend:**
  - T-052-F1: Consent settings entry point; gate for any third-party script
  - T-052-FT1 (test): No-script-before-consent test
- **Infrastructure:**
  - T-052-I1: CSP allows the consent tool's origin only once it is chosen (with US-060)


## E13 — Quality Assurance

### US-053
**Concurrency and time-travel test suite** · Ready · S4

- **Backend:**
  - T-053-BT1 (test): Parallel-connection harness on MySQL 8, not SQLite
  - T-053-BT2 (test): Helpers that freeze the request clock
- **Infrastructure:**
  - T-053-I1: CI job with a MySQL 8 service (US-058)

### US-054
**Keyboard and screen-reader access to the core journey** · Ready · S5

- **Frontend:**
  - T-054-F1: Fix the findings
  - T-054-FT1 (test): End-to-end keyboard test of J1 in a real browser, plus automated accessibility scans of the J1 pages
- **Infrastructure:**
  - T-054-I1: E2E job in CI

### US-055
**French-only, correctly pluralised copy** · Ready · S5

- **Backend:**
  - T-055-B1: `lang/fr` messages for every error code and field code
- **Frontend:**
  - T-055-F1: Every UI string, including ARIA labels, in `messages.fr` (frontend-architecture §14)
  - T-055-FT1 (test): Test that flags UI strings outside `messages.fr`

### US-056
**Performance targets and load test** · Blocked · S5

- **Backend:**
  - T-056-BT1 (test): Query-plan review of listing, facets and search (indexing-strategy verification plan)
- **Infrastructure:**
  - T-056-I1: Load-test tooling and a production-like environment (Q-72, Q-82)


## E14 — Infrastructure & Deployment

### US-057
**Local Docker environment** · Ready · S0

- **Backend:**
  - T-057-BT1 (test): Smoke test on `/up`
- **Infrastructure:**
  - T-057-I1: Compose file; Dockerfiles for `app` (PHP 8.3+ FPM with pdo_mysql, intl, bcmath, opcache) and `vite`; Nginx config; volumes; `.env.example` with labelled development placeholders
  - T-057-I2: `.gitattributes` with `eol=lf` (docker-architecture §8)

### US-058
**Continuous integration** · Needs Clarification · S0

- **Backend:**
  - T-058-BT1 (test): Architecture tests for the namespace rules
- **Infrastructure:**
  - T-058-I1: CI workflow with a MySQL 8 service; Composer and npm caches
  - T-058-I2: Re-script the Phase 05 design-validation checks, whose original scripts weren't kept

### US-059
**API foundation** · Ready · S0 · API: `GET /api/v1/storefront/settings`

- **Backend:**
  - T-059-B1: `/api/v1` route groups by auth code; exception renderer covering the error-format catalogue; request-ID and server-time middleware; `config/storefront.php` and `StorefrontSettings` with the production boot check
  - T-059-BT1 (test): Renderer tests for every catalogue code
- **Infrastructure:**
  - T-059-I1: Env variable categories (docker-architecture §5)

### US-060
**Transport security and caching headers** · Ready · S0

- **Backend:**
  - T-060-B1: `no-store` middleware on S endpoints and on availability reads
  - T-060-BT1 (test): Header tests per route group
- **Infrastructure:**
  - T-060-I1: Nginx security headers per authentication-design §7; asset cache rules

### US-061
**MySQL behaviour spike** · Ready · S0

- **Backend:**
  - T-061-BT1 (test): Throwaway migration experiments, not merged
- **Infrastructure:**
  - T-061-I1: MySQL 8 version pinned in Docker and CI


## E15 — Recommendations

### US-062
**Recommendation rails on the default landing** · Needs Clarification · S7 · API: `GET /api/v1/recommendations/{rail}`; `GET /api/v1/articles`

- **Backend:**
  - T-062-B1: `RecommendationProvider` interface with empty v1 bindings; `RecommendationController`; `collection=top-picks` context
  - T-062-BT1 (test): Tests
- **Frontend:**
  - T-062-F1: `RecommendationRail`; `TopPicksListingPage` route declared before `:campaignId`
  - T-062-FT1 (test): Hidden-when-empty test

### US-063
**Popular rail** · Needs Clarification · S7 · API: `GET /api/v1/recommendations/{rail}`

- **Backend:**
  - T-063-B1: Queued `IncrementArticleReservationStats` listener; `Popular` strategy; `recommendations:prune-stats`
  - T-063-BT1 (test): Ranking and prune tests
- **Frontend:**
  - T-063-F1: Rail on landings with `popular_rail = true`
- **Infrastructure:**
  - T-063-I1: `queue` and `scheduler` services


## E16 — Data Administration

### US-064
**Synthetic seed data** · Ready · S2

- **Backend:**
  - T-064-B1: Factories and per-module seeders; campaign windows relative to the seeding time, so states stay valid on any date
  - T-064-BT1 (test): Seeder smoke test
- **Infrastructure:**
  - T-064-I1: Seed step in the local workflow and in CI

### US-065
**Production data entry** · Blocked · Backlog (unscheduled)

No tasks: the behaviour is not designed and must not be invented (see the story note).
