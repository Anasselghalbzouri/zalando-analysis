# ADR-003: API authentication strategy (Sanctum SPA sessions + CSRF)

- **Status:** Proposed
- **Date:** 2026-11-04 (Phase 05)
- **Related requirements:** NFR-SEC-01, NFR-SEC-02, NFR-SEC-03, NFR-SEC-04, FR-ACC-08

## Context
The only client is the first-party SPA. The requirements need session-based identity with no user ID in URLs (NFR-SEC-01), CSRF protection (NFR-SEC-02) and `no-store` personal data (NFR-SEC-03). No mobile or third-party API clients are in scope. The reference platform's mechanisms must not be copied.

## Decision
Use **Laravel Sanctum SPA authentication**:
- **Session:** a stateful, HTTP-only session cookie.
- **CSRF:** the `XSRF-TOKEN` cookie mirrored into the `X-XSRF-TOKEN` header on unsafe methods.
- **Origin:** the SPA and API share **one origin** through Nginx, so CORS is off.
- **Tokens:** none issued.
- **Authorization:** a role column (`member`, `merchandiser`, `admin`) plus policies and gates.

Detail is in [authentication-design.md](../authentication-design.md).

## Alternatives considered
- **Sanctum API tokens:** a token in JS storage is exposed to XSS; that only makes sense for native or third-party clients.
- **Passport / OAuth 2.0:** heavy, and no delegated access is needed.
- **JWT:** hard revocation and a high misuse risk.
- **A roles/permissions package:** unnecessary for three coarse roles.

## Consequences
- **Positive:** no token handling in JS; logout really revokes; CSRF and session fixation are handled by the framework.
- **Negative:** a native app (FR-CNT-05, Could) would need an extra token path. Cross-site deployment of the SPA isn't possible without reconfiguring stateful domains.
