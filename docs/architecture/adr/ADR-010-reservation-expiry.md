# ADR-010: Reservation expiry

- **Status:** Proposed
- **Date:** 2026-11-04 (Phase 05)
- **Related requirements:** FR-CRT-02, FR-CRT-08, FR-STK-01, NFR-CON-01

## Context
- **Observed:** the 20-minute reservation and its countdown are CONFIRMED (BR-14-02).
- **Not observed:** what happens at zero. Releasing the item is a HYPOTHESIS (BR-14-06, FR-CRT-08), and the multi-item timer rule is UNKNOWN (Q-32).

A timer without expiry would let units stay reserved forever.

## Decision
**PROPOSED, pending PO validation:**
- **Storage:** each reservation stores `expires_at`. The timer mode (per item or shared) is a setting, and storing per row supports both.
- **Read side:** carts, availability and checkout hand-off **ignore** expired rows immediately, so expiry is correct without waiting for a job.
- **Re-add before sweep:** `AddToCart` first releases an expired row with the same (user, inventory item) inside its own transaction. The checkout `MarkSold` contract refuses expired rows.
- **Sweep:** a scheduled command runs every minute and releases expired rows in batches, through the same `ReleaseUnit` service used by remove. It decrements `quantity_reserved` and emits `ReservationReleased(expired)`.
- **Switch:** release is controlled by `FEATURE_RELEASE_EXPIRED_RESERVATIONS`.

## Alternatives considered
- **Job per reservation** (delayed queue job at `expires_at`): precise, but thousands of delayed jobs at launch peaks, and lost jobs leave stock stuck.
- **Lazy release only** (on next read): stock counters stay inflated until someone reads.
- **Database event scheduler:** logic outside the application, and hard to test.

## Consequences
- **Positive:** correctness doesn't depend on the scheduler's timing; counters are corrected within about a minute; one code path for remove and expiry.
- **Negative:** `quantity_available` may understate stock by up to one sweep interval after expiry. That's acceptable; the PO can shorten the interval.
- **Open:** the user-facing behaviour at expiry is UNKNOWN (Q-37).
