# ADR 0002: Separate API And Admin Frontend

## Decision

Build `milkman-api` as the Laravel backend and `milkman-admin` as a separate React + TypeScript frontend.

## Context

The migration plan prefers a modern API-first backend and frontend separation.

## Consequences

- The admin UI consumes `/api/v1`.
- Laravel stays focused on API, auth, domain logic, queues, seed data, and documentation.
- The frontend must avoid duplicating backend business rules.
