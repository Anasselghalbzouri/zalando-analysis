# Definition of Done — Phase 06

A story is **Done** only when every item below that applies to it is true. The items are checked in review and at sprint review.
- **Items that can't be met yet:** an item that can't be met because of an open question is not waived. The story stays Needs Clarification or Blocked until it can be met.
- **Readiness:** the definitions of Ready, Needs Clarification and Blocked are in [03-product-backlog.md §2](03-product-backlog.md).

## 1. Requirements satisfied
- [ ] Every acceptance criterion of the story passes. "Pending" criteria are either resolved by a recorded PO decision or the story isn't Done.
- [ ] The behaviour traces to the story's source requirements (SRS IDs) and to nothing else. No unconfirmed business rule was added on the way.
- [ ] HYPOTHESIS behaviour (FR-CMP-08, FR-CRT-08, FR-PDP-04, FR-STK-03, FR-UPC-04, FR-EVT-03, FR-EVT-05, NFR-SEC-05) sits behind its switch or seam. It is only enabled after a recorded PO validation.
- [ ] Policy values are read from `config/storefront.php` and never hard-coded. Development placeholders are labelled as such, and production fails closed when a value is missing.

## 2. Code reviewed
- [ ] At least one reviewer other than the author approved the change.
- [ ] The module dependency rules of [backend-architecture.md §2](../architecture/backend-architecture.md) hold, and the architecture test is green.
- [ ] Controllers stay thin. Validation sits in Form Requests and business rules in actions or services, as in backend-architecture §3–§6.
- [ ] **Stock changes:** `quantity_reserved` is changed only by `ReserveUnit` and `ReleaseUnit`, never by a read-then-write.
- [ ] **Scope:** no checkout, payment, order, voucher or fee-charging code, table or endpoint was added (M21 OUT OF SCOPE, FR-OOS-01).
- [ ] **Independence:** no reference-platform branding, copy, images, identifiers or source code appears in the code, seeds, fixtures or assets.

## 3. Backend and frontend tests passed
- [ ] **Back-end tests:**
  - Feature tests cover every endpoint the story touches: success, validation, authorization and error codes.
  - They run against **MySQL 8**, not SQLite, wherever transactions, constraints or concurrency matter.
- [ ] **Front-end tests:** component and hook tests cover the story's UI states (loading, empty, error, success) and the error-code mapping.
- [ ] **Time and stock stories:**
  - Time-dependent stories have time-travel tests at the boundaries, using the request clock.
  - Stock stories pass the concurrency suite of US-053.
- [ ] **CI:** green on the merge commit (lint, tests, build, architecture test, Phase 06 backlog checks).

## 4. API contracts respected
- [ ] **Contract:** routes, methods, auth codes (S, C, P, G), parameters, bodies and response shapes match [api-contracts.md](../architecture/api/api-contracts.md).
  - Any difference is first recorded as an approved contract change.
- [ ] **Envelope:** every success response has `data` and `meta.server_time`.
  - Money is `{amount_cents, currency}`, and times are UTC ISO-8601.
- [ ] **Pagination:** it follows the page-based convention, capped by `listing_page_size`.
- [ ] **Identity:** no user ID appears in any URL or request body for member resources (NFR-SEC-01).

## 5. Validation and error handling implemented
- [ ] **Error format:** errors use `application/problem+json` with a `code` from the catalogue in [error-format.md](../architecture/api/error-format.md).
  - Field errors use the documented field codes.
- [ ] **Unknown parameters:** they are rejected with 422.
- [ ] **No internals in responses:** no stack traces, SQL, class names or paths.
  - Server logs carry the `request_id` (NFR-OBS-01).
- [ ] **French copy:** user-facing messages come from `lang/fr` (API) or `messages.fr` (SPA).
  - The only copy used is observed or PO-approved, and plurals are correct.

## 6. Security and authorization verified
- [ ] **Session and CSRF:** S routes require a session, and every unsafe method requires a valid CSRF token (419 otherwise).
- [ ] **Ownership:** it is enforced by policy, and a foreign resource answers 404.
- [ ] **Headers:** cart, account and availability responses carry `Cache-Control: no-store`.
  - Nginx security headers are unchanged or improved (NFR-SEC-03, NFR-SEC-04).
- [ ] **Rate limits:** they apply where the contract names them, with `Retry-After` on 429.
- [ ] **No secrets or personal data:** none in code, logs, fixtures, commits or test snapshots.
  - Seed data is synthetic.
- [ ] **Consent:** no third-party script is loaded before consent (NFR-PRV-01).

## 7. Responsive UI verified
- [ ] **Accessibility:** keyboard operation, visible focus, focus trap and return in dialogs, announced alerts, alt text on images, French-only ARIA labels (NFR-A11Y-01, NFR-I18N-01).
- [ ] **Layout structure:** layouts use the mobile-first structure of [frontend-architecture.md §13](../architecture/frontend-architecture.md).
  - Breakpoint-specific behaviour is checked only once Q-15 has defined it. Until then the story records "responsive: structure only".
- [ ] **Time display:** countdowns use `useServerNow`, and dates are formatted in `display_timezone`.

## 8. Documentation updated
- [ ] **Docs and contracts:** API contracts, schema notes and ADRs are updated when the story changed them, with architect approval for schema errata.
- [ ] **Backlog source:** `tools/phase06/backlog.txt` reflects the story's final status, and `python -I tools/phase06/gen.py .` passes.
- [ ] **Questions:** any new open question is added to [open-questions.md](../requirements/open-questions.md) with the next free Q-ID, and linked from the story.

## 9. No critical bugs
- [ ] No open defect of severity critical or high is linked to the story.
- [ ] No known regression in the core journey J1 (browse → PDP → add → cart → remove).

## 10. Feature accepted against its criteria
- [ ] The Product Owner accepted the story at sprint review, against its acceptance criteria, in the production-like environment.
- [ ] For HYPOTHESIS stories, the PO's validation decision is recorded with its date and the question it closes.

## Release-level Done (MVP)
The MVP release additionally needs every gate G-01 to G-09 in [10-phase-06-summary.md §4](10-phase-06-summary.md) to be closed or explicitly waived by the sponsor.
