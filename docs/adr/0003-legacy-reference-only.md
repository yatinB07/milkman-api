# ADR 0003: Treat Legacy Schema As Required Reference

## Decision

Use the legacy PHP code and `milkyway` database schema as required reference material for the Laravel migration.

## Context

The current legacy database contains testing data only. Existing rows do not need to be preserved, but the table structure, columns, SQL usage, relationships, and workflows define the business scope that must be covered.

## Consequences

- New Laravel migrations define the clean schema.
- Every legacy application table must be mapped, merged into a documented domain model, or explicitly marked obsolete with a reason.
- Empty legacy tables are still part of the required schema coverage review.
- Factories and seeders create fresh demo/development data.
- Plain-text legacy passwords are not copied.
- Legacy SQL and PHP files still inform behavior, relationships, and compatibility wrappers.
