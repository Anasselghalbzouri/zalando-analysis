# API error format — Phase 05

**Status:** PROPOSED. Every error response from `/api/v1` uses **Problem Details for HTTP APIs (RFC 9457)**, with the media type `application/problem+json`, plus two extensions: `code` and `errors`. One exception renderer produces them all ([backend-architecture.md §11](../backend-architecture.md)).

## 1. Shape
```json
{
  "type": "string (URI)",          // e.g. "https://<app-domain>/problems/out-of-stock" — a stable doc link
  "title": "string",               // short, stable summary (French)
  "status": "int",                 // the HTTP status, repeated
  "detail": "string",              // human-readable explanation for this occurrence (French)
  "instance": "string",            // request path
  "code": "string",                // stable machine code — the SPA switches on this, never on text
  "request_id": "string",          // same value as the X-Request-Id response header (NFR-OBS-01)
  "errors": { "<field>": [ { "code": "string", "message": "string" } ] }   // only for 422
}
```

**Rules:**
1. **`code` is the contract.** `title` and `detail` are French text for people and may change wording.
2. **No internals in error bodies:** no stack traces, SQL, class names, file paths or upstream messages. The renderer logs them server-side, with the `request_id` (NFR-SEC-04).
3. **No account enumeration.**
   - Login failure uses the same `invalid_credentials` for an unknown e-mail and a wrong password.
   - Forgot-password always returns 202.
   - Another member's reservation answers 404, not 403.
4. **Field keys** in `errors` use the request's own names, including bracket paths such as `filter.price_min` and `profiles.0.size_value_ids`.
5. **Rate limits:** `429` responses carry `Retry-After`.

## 2. Code catalogue
| HTTP | `code` | When | SPA behaviour |
|---|---|---|---|
| 400 | `bad_request` | Malformed JSON | Generic error toast |
| 401 | `unauthenticated` | No or expired session on an S/C route | Clear auth state; go to `/login?redirect=` |
| 403 | `forbidden` | A policy denies (e.g. a non-admin hits a back-office ability) | Error page |
| 403 | `invalid_signature` | Expired or tampered e-mail verification link | Offer to resend |
| 404 | `not_found` | Unknown resource; a draft or **not-yet-started** campaign (PROPOSED server-side rule); another member's or an expired reservation | Not-found page or toast |
| 409 | `out_of_stock` | The reserve found no available unit (NFR-CON-01) | Toast; refetch the article (copy UNKNOWN) |
| 409 | `reservation_limit_reached` | Above `max_quantity_per_article` (Q-46) | Toast |
| 409 | `campaign_not_live` | Add attempted outside the live window. PROPOSED guard (FR-CMP-08) | Toast; refetch |
| 410 | `campaign_ended` | Read of an ended campaign or article. PROPOSED (Q-17) | Message + link to `/event` |
| 419 | `csrf_token_mismatch` | Missing or invalid `X-XSRF-TOKEN` | Refresh the CSRF cookie, retry once |
| 422 | `validation_failed` | Form Request failure, with field `errors` | Map to fields; focus the first |
| 422 | `invalid_credentials` | Login failure | Form-level message |
| 422 | `invalid_token` | Password-reset token invalid or expired | Form-level message |
| 429 | `rate_limited` | Throttled | Wait for `Retry-After` |
| 503 | `settings_incomplete` | A production policy value is unset (fail closed) | Maintenance message |
| 503 | `service_unavailable` | Maintenance or a dependency is down | Maintenance message |
| 500 | `server_error` | Unhandled | Error boundary, showing the request ID |

## 3. Field error codes (inside `errors`)
| Field code | Typical field | French message |
|---|---|---|
| `required` | any | "Ce champ est obligatoire." (PROPOSED) |
| `size_required` | `variant_id` on `POST /cart/items` | **"Choisissez une taille"**, the observed copy (FR-PDP-02, AC-FR-PDP-02-1) |
| `invalid` | any | "La valeur est invalide." (PROPOSED) |
| `taken` | `email` on register | PROPOSED copy |
| `must_be_accepted` | `adult_confirmed`, `terms_accepted` | PROPOSED copy (BR-15-02) |
| `min_greater_than_max` | `filter.price_min` | PROPOSED copy |
| `unknown_parameter` | any query key | Shown in development only |
| `reason_required` | `reason_id` on unsubscribe | PROPOSED copy (BR-15-04) |
| `size_not_in_system` | `profiles.*.size_value_ids` | PROPOSED copy |

Message wording is a PO decision unless it was observed. Messages live in `lang/fr` and are plural-aware (NFR-I18N-02).

## 4. Examples (shapes only)
```json
// 422 on POST /api/v1/cart/items without a size
{ "type": "https://<app-domain>/problems/validation-failed", "title": "Données invalides", "status": 422,
  "detail": "La requête contient des erreurs.", "instance": "/api/v1/cart/items",
  "code": "validation_failed", "request_id": "string",
  "errors": { "variant_id": [ { "code": "size_required", "message": "Choisissez une taille" } ] } }

// 409 on POST /api/v1/cart/items when the last unit was just reserved by someone else
{ "type": "https://<app-domain>/problems/out-of-stock", "title": "Article indisponible", "status": 409,
  "detail": "string", "instance": "/api/v1/cart/items", "code": "out_of_stock", "request_id": "string" }
```
`<app-domain>` is a placeholder; the product domain is UNKNOWN (Q-75). The titles shown are PROPOSED wording.
