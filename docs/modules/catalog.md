# Catalog

Phase 4 starts with public catalog read APIs. These endpoints expose active catalog data without authentication.

## Endpoints

```text
GET /api/v1/public/categories
GET /api/v1/public/stores
GET /api/v1/public/stores?search={term}
GET /api/v1/public/stores/{store}
GET /api/v1/public/stores/{store}/products
```

## Implementation

Request flow:

```text
Route -> FormRequest/Controller -> Action -> CatalogRepository -> Model -> Resource
```

`CatalogRepository` owns active-only filters, search, eager loading, and store lookup. `StoreNotFoundException` returns localized catalog errors from `lang/en/catalog.php`.

Current coverage:

- Active categories
- Active stores with optional search
- Store detail with gallery images, delivery options, time slots, coupons, and FAQs
- Store products with active products, available variants, images, and store category data

## Admin Category CRUD

```text
GET    /api/v1/admin/categories
POST   /api/v1/admin/categories
PUT    /api/v1/admin/categories/{category}
DELETE /api/v1/admin/categories/{category}
```

These endpoints require an admin Sanctum token with `products.manage`. Customer, store, or rider tokens are rejected by identity boundary checks.

The admin category module uses:

- `CategoryController`
- `CategoryRequest` and `UpdateCategoryRequest`
- category actions under `App\Actions\Admin\Categories`
- `CategoryRepository`
- `App\Http\Resources\Admin\CategoryResource`
