# ADR-009: Time and money representation

- **Status:** Proposed
- **Date:** 2026-11-04 (Phase 05)
- **Related requirements:** FR-CMP-02, FR-CMP-05, FR-CRT-02, FR-UPC-02, NFR-TIME-01, NFR-I18N-03

## Context
- **Time:** countdowns (FR-CMP-02, FR-CRT-02), launches at fixed local times (BR-03-16) and an UNKNOWN display timezone (Q-10) all need unambiguous time handling.
- **Money:** prices include VAT and are formatted in French (NFR-I18N-03). The discount rounding rule is UNKNOWN (Q-77).

## Decision
- **Storage:** every datetime is stored in **UTC**, with the app and connection timezone set to UTC. It's returned as ISO-8601 with `Z`.
- **Display:** in the `display_timezone` setting, a PO decision (Q-10).
- **One "now" per request** (request clock), used by the shared **live predicate**: `published_at IS NOT NULL AND starts_at <= now AND ends_at > now`.
- **Campaign state** is derived, never stored.
- **Server time:** every response carries `meta.server_time`, and the SPA counts down from that offset.
- **Money:** integer **cents** (`INT UNSIGNED`), EUR, VAT included, with a `Money` value object.
- **Discount %:** a stored generated column using one documented rule (floor, PROPOSED) until Q-77 decides.

## Alternatives considered
- **Store local time (Europe/Paris):** DST ambiguity and wrong ordering around clock changes.
- **A stored status column updated by cron:** drifts if a run is missed.
- **Decimal money columns:** workable, but integers avoid rounding bugs in PHP and JS.

## Consequences
- **Positive:** correct ordering and comparisons; countdowns are immune to wrong client clocks; tests can time-travel through the request clock.
- **Negative:** every display needs explicit timezone formatting, which `Intl.DateTimeFormat` handles.
