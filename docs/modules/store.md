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

## Store Gallery Image CRUD

```text
GET    /api/v1/store/gallery-images
GET    /api/v1/store/gallery-images/{galleryImage}
POST   /api/v1/store/gallery-images
PUT    /api/v1/store/gallery-images/{galleryImage}
DELETE /api/v1/store/gallery-images/{galleryImage}
```

These endpoints require a store Sanctum token with `stores.update`. They modernize legacy `store_api/view_gallery.php`, `store_api/add_gallery.php`, and `store_api/update_gallery.php` by using the authenticated store instead of accepting `store_id` from the payload.

The list endpoint supports `search` across image path. It accepts `per_page` and returns Laravel pagination metadata. Show, update, and delete operations are store-scoped, so a store cannot read or change another store's gallery image. Delete requests soft delete gallery images.

The store gallery image module uses:

- `App\Http\Controllers\Api\V1\Store\StoreGalleryImageController`
- `ListStoreResourcesRequest`, `StoreGalleryImageRequest`, and `UpdateStoreGalleryImageRequest`
- `App\Data\Store\ListStoreQueryData` and `StoreGalleryImageData`
- store gallery image actions under `App\Actions\Store\GalleryImages`
- `StoreGalleryImageRepository`
- `App\Http\Resources\Store\StoreGalleryImageResource`

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

## Store Rider CRUD

```text
GET    /api/v1/store/riders
GET    /api/v1/store/riders/{rider}
POST   /api/v1/store/riders
PUT    /api/v1/store/riders/{rider}
DELETE /api/v1/store/riders/{rider}
```

These endpoints require a store Sanctum token with `riders.manage`. They modernize legacy `store_api/list_rider.php`, `store_api/add_rider.php`, and `store_api/update_rider.php` by using the authenticated store instead of accepting `store_id` from the payload.

The list endpoint supports `search` across rider name, email, and mobile. It accepts `per_page` and returns Laravel pagination metadata. Create requests assign the `rider` role and rely on Laravel's password hashing cast. Show, update, and delete operations are store-scoped, so a store cannot read or change another store's rider. Delete requests soft delete riders.

The store rider module uses:

- `App\Http\Controllers\Api\V1\Store\StoreRiderController`
- `ListStoreResourcesRequest`, `StoreRiderRequest`, and `UpdateStoreRiderRequest`
- `App\Data\Store\ListStoreQueryData` and `StoreRiderData`
- store rider actions under `App\Actions\Store\Riders`
- `RiderRepository`
- `App\Http\Resources\Store\StoreRiderResource`

## Store Notification Read API

```text
GET /api/v1/store/notifications
GET /api/v1/store/notifications/{notification}
```

These endpoints require a store Sanctum token with `stores.view`. They modernize legacy `store_api/u_notification_list.php` by using the authenticated store instead of accepting `store_id` from the payload.

The list endpoint supports `search` across notification title and description. It accepts `per_page` and returns Laravel pagination metadata. Show operations are store-scoped, so a store cannot read another store's notification. Notification creation and maintenance remain admin-side workflows.

The store notification module uses:

- `App\Http\Controllers\Api\V1\Store\StoreNotificationController`
- `ListStoreResourcesRequest`
- `App\Data\Store\ListStoreQueryData`
- store notification actions under `App\Actions\Store\Notifications`
- `StoreNotificationRepository`
- `App\Http\Resources\Store\StoreNotificationResource`

## Store Page Read API

```text
GET /api/v1/store/pages
GET /api/v1/store/pages/{page}
```

These endpoints require a store Sanctum token with `stores.view`. They modernize legacy `store_api/u_pagelist.php` by returning active pages only.

The list endpoint supports `search` across page title and description. It accepts `per_page` and returns Laravel pagination metadata. Show operations only return active pages. Page creation and maintenance remain admin-side workflows.

The store page module uses:

- `App\Http\Controllers\Api\V1\Store\StorePageController`
- `ListStoreResourcesRequest`
- `App\Data\Store\ListStoreQueryData`
- store page actions under `App\Actions\Store\Pages`
- `PageRepository`
- `App\Http\Resources\Store\StorePageResource`
