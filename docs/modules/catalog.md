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

The list endpoint supports `search` and `per_page` and always returns Laravel pagination metadata.

The admin category module uses:

- `CategoryController`
- `CategoryRequest` and `UpdateCategoryRequest`
- category actions under `App\Actions\Admin\Categories`
- `CategoryRepository`
- `App\Http\Resources\Admin\CategoryResource`

## Admin Banner CRUD

```text
GET    /api/v1/admin/banners
POST   /api/v1/admin/banners
PUT    /api/v1/admin/banners/{banner}
DELETE /api/v1/admin/banners/{banner}
```

These endpoints require an admin Sanctum token with `settings.update`. They manage home/app banner records from the legacy `banner` table, including the display `title`, `image_path`, and active flag.

The list endpoint supports `search` across banner title and image path, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete banners.

The admin banner module uses:

- `BannerController`
- `BannerRequest` and `UpdateBannerRequest`
- banner actions under `App\Actions\Admin\Banners`
- `BannerRepository`
- `App\Http\Resources\Admin\BannerResource`
