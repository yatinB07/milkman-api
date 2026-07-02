# Catalog

Phase 4 starts with public catalog read APIs. These endpoints expose active catalog data without authentication.

## Endpoints

```text
GET /api/v1/public/categories
GET /api/v1/public/categories?search={term}&per_page={size}
GET /api/v1/public/stores?search={term}&per_page={size}
GET /api/v1/public/stores/{store}
GET /api/v1/public/stores/{store}/products?search={term}&per_page={size}
```

## Implementation

Request flow:

```text
Route -> FormRequest -> Data DTO -> Controller -> Action -> CatalogRepository -> Model -> Resource
```

`CatalogRepository` owns active-only filters, search, eager loading, pagination, and store lookup. `StoreNotFoundException` returns localized catalog errors from `lang/en/catalog.php`.

Current coverage:

- Active categories with search and pagination
- Active stores with search and pagination
- Store detail with gallery images, delivery options, time slots, coupons, and FAQs
- Store products with search, pagination, active products, available variants, images, and store category data

## Admin Category CRUD

```text
GET    /api/v1/admin/categories
GET    /api/v1/admin/categories/{category}
POST   /api/v1/admin/categories
PUT    /api/v1/admin/categories/{category}
DELETE /api/v1/admin/categories/{category}
```

These endpoints require an admin Sanctum token with `products.manage`. Customer, store, or rider tokens are rejected by identity boundary checks.

The list endpoint supports `search` and `per_page` and always returns Laravel pagination metadata.

The admin category module uses:

- `CategoryController`
- `CategoryRequest` and `UpdateCategoryRequest`
- `App\Data\Admin\CategoryData` and `App\Data\Admin\ListQueryData`
- category actions under `App\Actions\Admin\Categories`
- `CategoryRepository`
- `App\Http\Resources\Admin\CategoryResource`

## Admin Banner CRUD

```text
GET    /api/v1/admin/banners
GET    /api/v1/admin/banners/{banner}
POST   /api/v1/admin/banners
PUT    /api/v1/admin/banners/{banner}
DELETE /api/v1/admin/banners/{banner}
```

These endpoints require an admin Sanctum token with `settings.update`. They manage home/app banner records from the legacy `banner` table, including the display `title`, `image_path`, and active flag.

The list endpoint supports `search` across banner title and image path, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete banners.

The admin banner module uses:

- `BannerController`
- `BannerRequest` and `UpdateBannerRequest`
- `App\Data\Admin\BannerData` and `App\Data\Admin\ListQueryData`
- banner actions under `App\Actions\Admin\Banners`
- `BannerRepository`
- `App\Http\Resources\Admin\BannerResource`

## Admin Store Category CRUD

```text
GET    /api/v1/admin/store-categories
GET    /api/v1/admin/store-categories/{storeCategory}
POST   /api/v1/admin/store-categories
PUT    /api/v1/admin/store-categories/{storeCategory}
DELETE /api/v1/admin/store-categories/{storeCategory}
```

These endpoints require an admin Sanctum token with `products.manage`. They manage store-owned product category records from the legacy `tbl_mcat` table.

The list endpoint supports `search` across store category title and store title, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete store categories.

The admin store category module uses:

- `StoreCategoryController`
- `StoreCategoryRequest` and `UpdateStoreCategoryRequest`
- `App\Data\Admin\StoreCategoryData` and `App\Data\Admin\ListQueryData`
- store category actions under `App\Actions\Admin\StoreCategories`
- `StoreCategoryRepository`
- `App\Http\Resources\Admin\StoreCategoryResource`

## Admin Product CRUD

```text
GET    /api/v1/admin/products
GET    /api/v1/admin/products/{product}
POST   /api/v1/admin/products
PUT    /api/v1/admin/products/{product}
DELETE /api/v1/admin/products/{product}
```

These endpoints require an admin Sanctum token with `products.manage`. They manage catalog product records from the legacy `tbl_product` table.

The list endpoint supports `search` across product title, description, store title, and store category title, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete products.

The admin product module uses:

- `ProductController`
- `ProductRequest` and `UpdateProductRequest`
- `App\Data\Admin\ProductData` and `App\Data\Admin\ListQueryData`
- product actions under `App\Actions\Admin\Products`
- `ProductRepository`
- `App\Http\Resources\Admin\ProductResource`
