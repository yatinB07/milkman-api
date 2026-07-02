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

## Admin Product Variant CRUD

```text
GET    /api/v1/admin/product-variants
GET    /api/v1/admin/product-variants/{productVariant}
POST   /api/v1/admin/product-variants
PUT    /api/v1/admin/product-variants/{productVariant}
DELETE /api/v1/admin/product-variants/{productVariant}
```

These endpoints require an admin Sanctum token with `products.manage`. They manage product pricing/volume records from the legacy `tbl_product_attribute` table.

The list endpoint supports `search` across variant title, product title, and store title, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete product variants.

The admin product variant module uses:

- `ProductVariantController`
- `ProductVariantRequest` and `UpdateProductVariantRequest`
- `App\Data\Admin\ProductVariantData` and `App\Data\Admin\ListQueryData`
- product variant actions under `App\Actions\Admin\ProductVariants`
- `ProductVariantRepository`
- `App\Http\Resources\Admin\ProductVariantResource`

## Admin Product Image CRUD

```text
GET    /api/v1/admin/product-images
GET    /api/v1/admin/product-images/{productImage}
POST   /api/v1/admin/product-images
PUT    /api/v1/admin/product-images/{productImage}
DELETE /api/v1/admin/product-images/{productImage}
```

These endpoints require an admin Sanctum token with `products.manage`. They manage product gallery records from the legacy `tbl_extra` table.

The list endpoint supports `search` across image path, product title, and store title, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete product images.

The admin product image module uses:

- `ProductImageController`
- `ProductImageRequest` and `UpdateProductImageRequest`
- `App\Data\Admin\ProductImageData` and `App\Data\Admin\ListQueryData`
- product image actions under `App\Actions\Admin\ProductImages`
- `ProductImageRepository`
- `App\Http\Resources\Admin\ProductImageResource`

## Admin Store Gallery Image CRUD

```text
GET    /api/v1/admin/store-gallery-images
GET    /api/v1/admin/store-gallery-images/{storeGalleryImage}
POST   /api/v1/admin/store-gallery-images
PUT    /api/v1/admin/store-gallery-images/{storeGalleryImage}
DELETE /api/v1/admin/store-gallery-images/{storeGalleryImage}
```

These endpoints require an admin Sanctum token with `stores.manage`. They manage store gallery records from the legacy `tbl_photo` table.

The list endpoint supports `search` across image path and store title, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete store gallery images.

The admin store gallery image module uses:

- `StoreGalleryImageController`
- `StoreGalleryImageRequest` and `UpdateStoreGalleryImageRequest`
- `App\Data\Admin\StoreGalleryImageData` and `App\Data\Admin\ListQueryData`
- store gallery image actions under `App\Actions\Admin\StoreGalleryImages`
- `StoreGalleryImageRepository`
- `App\Http\Resources\Admin\StoreGalleryImageResource`

## Admin Delivery Option CRUD

```text
GET    /api/v1/admin/delivery-options
GET    /api/v1/admin/delivery-options/{deliveryOption}
POST   /api/v1/admin/delivery-options
PUT    /api/v1/admin/delivery-options/{deliveryOption}
DELETE /api/v1/admin/delivery-options/{deliveryOption}
```

These endpoints require an admin Sanctum token with `stores.manage`. They manage store delivery configuration records from the legacy `tbl_delivery` table.

The list endpoint supports `search` across delivery option title and store title, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete delivery options.

The admin delivery option module uses:

- `DeliveryOptionController`
- `DeliveryOptionRequest` and `UpdateDeliveryOptionRequest`
- `App\Data\Admin\DeliveryOptionData` and `App\Data\Admin\ListQueryData`
- delivery option actions under `App\Actions\Admin\DeliveryOptions`
- `DeliveryOptionRepository`
- `App\Http\Resources\Admin\DeliveryOptionResource`

## Admin Time Slot CRUD

```text
GET    /api/v1/admin/time-slots
GET    /api/v1/admin/time-slots/{timeSlot}
POST   /api/v1/admin/time-slots
PUT    /api/v1/admin/time-slots/{timeSlot}
DELETE /api/v1/admin/time-slots/{timeSlot}
```

These endpoints require an admin Sanctum token with `stores.manage`. They manage store delivery time windows from the legacy `tbl_time` table. Legacy `mintime`, `maxtime`, and `status` map to Laravel `starts_at`, `ends_at`, and `is_active`.

The list endpoint supports `search` across start time, end time, and store title, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete time slots.

The admin time slot module uses:

- `TimeSlotController`
- `TimeSlotRequest` and `UpdateTimeSlotRequest`
- `App\Data\Admin\TimeSlotData` and `App\Data\Admin\ListQueryData`
- time slot actions under `App\Actions\Admin\TimeSlots`
- `TimeSlotRepository`
- `App\Http\Resources\Admin\TimeSlotResource`

## Admin Coupon CRUD

```text
GET    /api/v1/admin/coupons
GET    /api/v1/admin/coupons/{coupon}
POST   /api/v1/admin/coupons
PUT    /api/v1/admin/coupons/{coupon}
DELETE /api/v1/admin/coupons/{coupon}
```

These endpoints require an admin Sanctum token with `stores.manage`. They manage store coupon records from the legacy `tbl_coupon` table. Legacy `coupon_img`, `coupon_code`, `expire_date`, `min_amt`, `coupon_val`, and `status` map to Laravel `image_path`, `code`, `expires_at`, `minimum_amount`, `value`, and `is_active`.

The list endpoint supports `search` across coupon title, subtitle, code, and store title, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete coupons.

The admin coupon module uses:

- `CouponController`
- `CouponRequest` and `UpdateCouponRequest`
- `App\Data\Admin\CouponData` and `App\Data\Admin\ListQueryData`
- coupon actions under `App\Actions\Admin\Coupons`
- `CouponRepository`
- `App\Http\Resources\Admin\CouponResource`

## Admin FAQ CRUD

```text
GET    /api/v1/admin/faqs
GET    /api/v1/admin/faqs/{faq}
POST   /api/v1/admin/faqs
PUT    /api/v1/admin/faqs/{faq}
DELETE /api/v1/admin/faqs/{faq}
```

These endpoints require an admin Sanctum token with `stores.manage`. They manage store FAQ content from the legacy `tbl_faq` table. Legacy `status` maps to Laravel `is_active`.

The list endpoint supports `search` across question, answer, and store title, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete FAQs.

The admin FAQ module uses:

- `FaqController`
- `FaqRequest` and `UpdateFaqRequest`
- `App\Data\Admin\FaqData` and `App\Data\Admin\ListQueryData`
- FAQ actions under `App\Actions\Admin\Faqs`
- `FaqRepository`
- `App\Http\Resources\Admin\FaqResource`

## Admin Page CRUD

```text
GET    /api/v1/admin/pages
GET    /api/v1/admin/pages/{page}
POST   /api/v1/admin/pages
PUT    /api/v1/admin/pages/{page}
DELETE /api/v1/admin/pages/{page}
```

These endpoints require an admin Sanctum token with `settings.update`. They manage CMS/static page content from the legacy `tbl_page` table. Legacy `status` maps to Laravel `is_active`.

The list endpoint supports `search` across page title and description, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete pages.

The admin page module uses:

- `PageController`
- `PageRequest` and `UpdatePageRequest`
- `App\Data\Admin\PageData` and `App\Data\Admin\ListQueryData`
- page actions under `App\Actions\Admin\Pages`
- `PageRepository`
- `App\Http\Resources\Admin\PageResource`
