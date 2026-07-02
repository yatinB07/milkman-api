# MilkMan API Architecture

MilkMan API is a Laravel 13 backend for the MilkMan migration. The legacy PHP app and `milkyway` database schema are the source-of-truth references for behavior and required table coverage. Existing rows are test data and are not imported.

## Request Flow

API requests should follow this path:

`Route -> FormRequest -> Data DTO -> Controller -> Action -> Service/Repository -> Model -> Resource`

- Controllers stay thin and return resources.
- Form requests own validation and request-level authorization.
- Data DTOs convert validated request input into explicit objects before it reaches Actions.
- Actions execute one use case.
- Services own domain workflows and integrations.
- Repositories own Eloquent queries, filters, persistence helpers, eager loading, and aggregates.
- Policies own record-level authorization.
- Resources own response shape.
- Code should follow OOP and SOLID principles: focused classes, explicit dependencies, and business rules outside controllers and models.
- User-facing messages belong in `lang/{locale}` files. Domain/application errors should use named exception classes instead of hardcoded framework exceptions.

## API Lists And Deletes

List endpoints must be paginated and should expose `search` when a module has searchable text fields. Repositories own pagination, search, filters, sorting, and eager loading.

CRUD delete endpoints soft delete records by default. Hard deletes require an explicit documented exception.

Admin list endpoints should convert validated `search` and `per_page` inputs into `App\Data\Admin\ListQueryData`. Module create/update requests should convert validated payloads into module-specific DTOs, such as `BannerData` or `CategoryData`, before calling Actions.

Public list endpoints should convert validated query inputs into `App\Data\Catalog\PublicListQueryData` and return paginated resource collections.

## Repository Coverage

Repositories are required when a module has persistence or query workflows. Do not create empty repositories for models that do not yet have implemented use cases. When a module is implemented, add the repository alongside the Action/Request/Resource tests and keep all Eloquent query building inside that repository.

Current implemented workflow repositories:

- `IdentityRepository`
- `CatalogRepository`
- `BannerRepository`
- `CategoryRepository`
- `CustomerAddressRepository`
- `CustomerNotificationRepository`
- `CustomerRepository`
- `StoreRepository`
- `StoreCategoryRepository`
- `ProductRepository`
- `ProductVariantRepository`
- `ProductImageRepository`
- `StoreGalleryImageRepository`
- `StoreNotificationRepository`
- `RiderNotificationRepository`
- `DeliveryOptionRepository`
- `FavoriteRepository`
- `TimeSlotRepository`
- `CouponRepository`
- `FaqRepository`
- `PageRepository`
- `PaymentMethodRepository`
- `ZoneRepository`
- `RiderRepository`
- `WalletTransactionRepository`
- `PayoutRequestRepository`
- `CashCollectionRepository`
- `SettingRepository`
- `OrderRepository`
- `OrderItemRepository`
- `SubscriptionOrderRepository`
- `SubscriptionOrderItemRepository`

The architecture layering test prevents Controllers and Actions from building Eloquent queries directly.

## Domain Foundations

Shared enum, event, and job classes live under:

- `app/Enums`
- `app/Events`
- `app/Jobs`

Add enum cases before hardcoding shared statuses or identity names. Dispatch domain events for important state changes, and put slow notification/media side effects into jobs.

## Eloquent Models

Application models use explicit `$fillable` arrays. Do not use open `protected $guarded = []` mass assignment. Keep fillable fields aligned with migrations and validated input, define casts for typed columns, and cover model fillable lists and relationships with tests.

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
