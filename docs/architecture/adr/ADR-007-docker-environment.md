# ADR-007: Docker environment

- **Status:** Proposed
- **Date:** 2026-11-04 (Phase 05)
- **Related requirements:** NFR-TECH-01, NFR-SEC-04, FR-CRT-08, NFR-PERF-01

## Context
The stack is Docker + Nginx + PHP-FPM + MySQL + React. The app needs a same-origin entry point for cookie sessions, a scheduler (reservation sweep, campaign boundary events) and a queue (e-mails, listeners). Load targets and hosting are UNKNOWN (Q-42, Q-72). The developers use Windows 11.

## Decision
Local services:
- **Core:** `nginx` (the single entry point), `app` (PHP-FPM), `scheduler` and `queue` (the same image as `app`), `mysql`.
- **Dev only:** `vite` for HMR.
- **Optional profiles:** `redis` and `mailpit`.

**Database drivers** for session, cache and queue are the **baseline**. Redis is an opt-in upgrade.

Detail is in [docker-architecture.md](../docker-architecture.md).

## Alternatives considered
- **Redis mandatory from day one:** faster sessions, cache and queues, but there's no evidence it's needed yet, and it's one more service to run.
- **Laravel Sail as-is:** quick, but less control over Nginx same-origin proxying and security headers.
- **No scheduler container** (cron on the host): it doesn't port across developer machines.

## Consequences
- **Positive:** dev mirrors production's same-origin model; minimal mandatory services; works on Windows with WSL 2.
- **Negative:** database-backed sessions and queues add DB load. Switching to Redis is a configuration change once measured.
