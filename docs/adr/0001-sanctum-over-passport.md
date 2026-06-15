# ADR 0001: Use Sanctum Over Passport

## Decision

Use Laravel Sanctum for API authentication.

## Context

MilkMan needs first-party admin SPA auth and mobile/API tokens for customers, stores, and riders. It does not currently need to act as an OAuth2 provider for third-party applications.

## Consequences

- Token auth stays simpler than Passport.
- OAuth2 client flows are not available unless Passport is introduced later.
- Spatie permissions use the `sanctum` guard.
