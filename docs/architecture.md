# MilkMan API Architecture

MilkMan API is a Laravel 13 backend for the MilkMan migration. The legacy PHP app and `milkyway` database schema are the source-of-truth references for behavior and required table coverage. Existing rows are test data and are not imported.

## Request Flow

API requests should follow this path:

`Route -> FormRequest -> Controller -> Action -> Service/Repository -> Model -> Resource`

- Controllers stay thin and return resources.
- Form requests own validation and request-level authorization.
- Actions execute one use case.
- Services own domain workflows and integrations.
- Repositories own Eloquent queries, filters, persistence helpers, eager loading, and aggregates.
- Policies own record-level authorization.
- Resources own response shape.

## Auth

Sanctum is used for API tokens. The baseline identity models are:

- `Admin`
- `Customer`
- `Store`
- `Rider`

Spatie Laravel Permission uses the `sanctum` guard for seeded roles and permissions.

## Documentation

Scramble generates OpenAPI documentation from routes, requests, resources, and PHP types.

- UI: `/docs/api`
- JSON: `/docs/api.json`

## Testing

Use TDD for each feature. Write the failing feature/unit test first, implement the smallest working code, then refactor.
