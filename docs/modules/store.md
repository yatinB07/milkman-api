# Store APIs

Legacy references include `store_api/u_dashboard.php`, store-owned catalog management files, store order history files, payout request flows, and rider assignment endpoints.

## Store Dashboard API

```text
GET /api/v1/store/dashboard
```

This endpoint requires a store Sanctum token. Admin, customer, and rider tokens are rejected by identity boundary checks.

Legacy `store_api/u_dashboard.php` returned store-owned counts for products, product attributes, deliveries, categories, FAQs, time slots, coupons, riders, product images, gallery images, normal orders, and subscription orders. The Laravel dashboard keeps those counts under `counts`, keeps a legacy-compatible `cards` array for admin-panel style dashboards, and adds explicit `financials` fields.

Earnings follow the legacy formulas:

- Normal orders: `(subtotal - coupon_amount) - ((subtotal - coupon_amount + delivery_charge) * commission_percent / 100)`
- Subscription orders: `(subtotal - coupon_amount + delivery_charge) - ((subtotal - coupon_amount + delivery_charge) * commission_percent / 100)`
- Final earning: normal earning + subscription earning - payout requests

`on_hand_amount` is calculated from completed normal-order gross amount minus cash collections. The legacy `withdraw_limit` value came from `tbl_setting.pstore`; the current normalized `settings` table does not include that field yet, so this endpoint returns `0.00` until a dedicated withdrawal-limit setting is added.

The store dashboard module uses:

- `App\Http\Controllers\Api\V1\Store\StoreDashboardController`
- `ShowStoreDashboardAction`
- `StoreRepository::dashboardMetrics`
- `App\Http\Resources\Store\StoreDashboardResource`

## Store Category CRUD

```text
GET    /api/v1/store/categories
GET    /api/v1/store/categories/{category}
POST   /api/v1/store/categories
PUT    /api/v1/store/categories/{category}
DELETE /api/v1/store/categories/{category}
```

These endpoints require a store Sanctum token with `products.manage`. They modernize legacy `store_api/list_category.php`, `store_api/add_category.php`, and `store_api/update_category.php` by using the authenticated store instead of accepting `store_id` from the payload.

The list endpoint supports `search` across category title. It accepts `per_page` and returns Laravel pagination metadata. Show, update, and delete operations are store-scoped, so a store cannot read or change another store's category. Delete requests soft delete categories.

The store category module uses:

- `App\Http\Controllers\Api\V1\Store\StoreCategoryController`
- `ListStoreResourcesRequest`, `StoreCategoryRequest`, and `UpdateStoreCategoryRequest`
- `App\Data\Store\ListStoreQueryData` and `StoreCategoryData`
- store category actions under `App\Actions\Store\Categories`
- `StoreCategoryRepository`
- `App\Http\Resources\Store\StoreCategoryResource`

## Store Delivery Option CRUD

```text
GET    /api/v1/store/delivery-options
GET    /api/v1/store/delivery-options/{deliveryOption}
POST   /api/v1/store/delivery-options
PUT    /api/v1/store/delivery-options/{deliveryOption}
DELETE /api/v1/store/delivery-options/{deliveryOption}
```

These endpoints require a store Sanctum token with `stores.update`. They modernize legacy `store_api/list_deliveries.php`, `store_api/add_deliveries.php`, and `store_api/update_deliveries.php`.

The list endpoint supports `search` across delivery option title. It accepts `per_page` and returns Laravel pagination metadata. Show, update, and delete operations are store-scoped, so a store cannot read or change another store's delivery option. Delete requests soft delete delivery options.

The store delivery option module uses:

- `App\Http\Controllers\Api\V1\Store\StoreDeliveryOptionController`
- `ListStoreResourcesRequest`, `StoreDeliveryOptionRequest`, and `UpdateStoreDeliveryOptionRequest`
- `App\Data\Store\ListStoreQueryData` and `StoreDeliveryOptionData`
- store delivery option actions under `App\Actions\Store\DeliveryOptions`
- `DeliveryOptionRepository`
- `App\Http\Resources\Store\StoreDeliveryOptionResource`

## Store Coupon CRUD

```text
GET    /api/v1/store/coupons
GET    /api/v1/store/coupons/{coupon}
POST   /api/v1/store/coupons
PUT    /api/v1/store/coupons/{coupon}
DELETE /api/v1/store/coupons/{coupon}
```

These endpoints require a store Sanctum token with `stores.update`. They modernize legacy `store_api/coupon_list.php`, `store_api/add_coupon.php`, and `store_api/update_coupon.php`.

The list endpoint supports `search` across title, subtitle, coupon code, and description. It accepts `per_page` and returns Laravel pagination metadata. Show, update, and delete operations are store-scoped, so a store cannot read or change another store's coupon. Delete requests soft delete coupons.

The store coupon module uses:

- `App\Http\Controllers\Api\V1\Store\StoreCouponController`
- `ListStoreResourcesRequest`, `StoreCouponRequest`, and `UpdateStoreCouponRequest`
- `App\Data\Store\ListStoreQueryData` and `StoreCouponData`
- store coupon actions under `App\Actions\Store\Coupons`
- `CouponRepository`
- `App\Http\Resources\Store\StoreCouponResource`

## Store FAQ CRUD

```text
GET    /api/v1/store/faqs
GET    /api/v1/store/faqs/{faq}
POST   /api/v1/store/faqs
PUT    /api/v1/store/faqs/{faq}
DELETE /api/v1/store/faqs/{faq}
```

These endpoints require a store Sanctum token with `stores.update`. They modernize legacy `store_api/list_faq.php`, `store_api/add_faq.php`, and `store_api/update_faq.php` by using the authenticated store instead of accepting `store_id` from the payload.

The list endpoint supports `search` across FAQ question and answer text. It accepts `per_page` and returns Laravel pagination metadata. Show, update, and delete operations are store-scoped, so a store cannot read or change another store's FAQ. Delete requests soft delete FAQs.

The store FAQ module uses:

- `App\Http\Controllers\Api\V1\Store\StoreFaqController`
- `ListStoreResourcesRequest`, `StoreFaqRequest`, and `UpdateStoreFaqRequest`
- `App\Data\Store\ListStoreQueryData` and `StoreFaqData`
- store FAQ actions under `App\Actions\Store\Faqs`
- `FaqRepository`
- `App\Http\Resources\Store\StoreFaqResource`

## Store Time Slot CRUD

```text
GET    /api/v1/store/time-slots
GET    /api/v1/store/time-slots/{timeSlot}
POST   /api/v1/store/time-slots
PUT    /api/v1/store/time-slots/{timeSlot}
DELETE /api/v1/store/time-slots/{timeSlot}
```

These endpoints require a store Sanctum token with `stores.update`. They modernize legacy `store_api/list_timeslot.php`, `store_api/add_timeslot.php`, and `store_api/update_timeslot.php`.

The list endpoint supports `search` across start and end times. It accepts `per_page` and returns Laravel pagination metadata. Show, update, and delete operations are store-scoped, so a store cannot read or change another store's time slot. Delete requests soft delete time slots.

The store time slot module uses:

- `App\Http\Controllers\Api\V1\Store\StoreTimeSlotController`
- `ListStoreResourcesRequest`, `StoreTimeSlotRequest`, and `UpdateStoreTimeSlotRequest`
- `App\Data\Store\ListStoreQueryData` and `StoreTimeSlotData`
- store time slot actions under `App\Actions\Store\TimeSlots`
- `TimeSlotRepository`
- `App\Http\Resources\Store\StoreTimeSlotResource`

## Store Product CRUD

```text
GET    /api/v1/store/products
GET    /api/v1/store/products/{product}
POST   /api/v1/store/products
PUT    /api/v1/store/products/{product}
DELETE /api/v1/store/products/{product}
```

These endpoints require a store Sanctum token with `products.manage`. They modernize legacy `store_api/product_list.php`, `store_api/add_product.php`, and `store_api/update_product.php` by using the authenticated store instead of accepting `store_id` from the payload.

The list endpoint supports `search` across product title, description, and category title. It accepts `per_page` and returns Laravel pagination metadata. Create/update requests only accept categories owned by the authenticated store. Show, update, and delete operations are store-scoped, so a store cannot read or change another store's product. Delete requests soft delete products.

The store product module uses:

- `App\Http\Controllers\Api\V1\Store\StoreProductController`
- `ListStoreResourcesRequest`, `StoreProductRequest`, and `UpdateStoreProductRequest`
- `App\Data\Store\ListStoreQueryData` and `StoreProductData`
- store product actions under `App\Actions\Store\Products`
- `ProductRepository`
- `App\Http\Resources\Store\StoreProductResource`

## Store Product Image CRUD

```text
GET    /api/v1/store/product-images
GET    /api/v1/store/product-images/{productImage}
POST   /api/v1/store/product-images
PUT    /api/v1/store/product-images/{productImage}
DELETE /api/v1/store/product-images/{productImage}
```

These endpoints require a store Sanctum token with `products.manage`. They modernize legacy `store_api/u_extra_list.php`, `store_api/u_add_exra.php`, and `store_api/u_extra_edit.php`.

The list endpoint supports `search` across image path and product title. It accepts `per_page` and returns Laravel pagination metadata. Create and update requests only accept products owned by the authenticated store. Show, update, and delete operations are store-scoped, so a store cannot read or change another store's product image. Delete requests soft delete product images.

The store product image module uses:

- `App\Http\Controllers\Api\V1\Store\StoreProductImageController`
- `ListStoreResourcesRequest`, `StoreProductImageRequest`, and `UpdateStoreProductImageRequest`
- `App\Data\Store\ListStoreQueryData` and `StoreProductImageData`
- store product image actions under `App\Actions\Store\ProductImages`
- `ProductImageRepository`
- `App\Http\Resources\Store\StoreProductImageResource`

## Store Product Variant CRUD

```text
GET    /api/v1/store/product-variants
GET    /api/v1/store/product-variants/{productVariant}
POST   /api/v1/store/product-variants
PUT    /api/v1/store/product-variants/{productVariant}
DELETE /api/v1/store/product-variants/{productVariant}
```

These endpoints require a store Sanctum token with `products.manage`. They modernize legacy `store_api/list_product_attr.php`, `store_api/add_product_attr.php`, and `store_api/update_product_attr.php`.

The list endpoint supports `search` across variant title and product title. It accepts `per_page` and returns Laravel pagination metadata. Create/update requests only accept products owned by the authenticated store. Show, update, and delete operations are store-scoped, so a store cannot read or change another store's variant. Delete requests soft delete variants.

The store product variant module uses:

- `App\Http\Controllers\Api\V1\Store\StoreProductVariantController`
- `ListStoreResourcesRequest`, `StoreProductVariantRequest`, and `UpdateStoreProductVariantRequest`
- `App\Data\Store\ListStoreQueryData` and `StoreProductVariantData`
- store product variant actions under `App\Actions\Store\ProductVariants`
- `ProductVariantRepository`
- `App\Http\Resources\Store\StoreProductVariantResource`
