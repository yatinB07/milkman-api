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

## Admin Customer CRUD

```text
GET    /api/v1/admin/customers
GET    /api/v1/admin/customers/{customer}
POST   /api/v1/admin/customers
PUT    /api/v1/admin/customers/{customer}
DELETE /api/v1/admin/customers/{customer}
```

These endpoints require an admin Sanctum token with `users.manage`. They manage customer records from the legacy `tbl_user` table. Legacy `pro_pic`, `ccode`, `registartion_date`, `refercode`, `parentcode`, `wallet`, and `status` map to Laravel `profile_image_path`, `country_code`, `registered_at`, `referral_code`, `parent_referral_code`, `wallet_balance`, and `is_active`.

The list endpoint supports `search` across customer name, email, mobile, referral code, and parent referral code, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete customers. Passwords are accepted only as write input and are hashed by the model cast; API resources never expose the password hash.

The admin customer module uses:

- `CustomerController`
- `CustomerRequest` and `UpdateCustomerRequest`
- `App\Data\Admin\CustomerData` and `App\Data\Admin\ListQueryData`
- customer actions under `App\Actions\Admin\Customers`
- `CustomerRepository`
- `App\Http\Resources\Admin\CustomerResource`

## Customer Profile APIs

```text
GET /api/v1/customer/profile
PUT /api/v1/customer/profile
```

These endpoints require a customer Sanctum token. Admin, store, and rider tokens are rejected by identity boundary checks.

Legacy `u_getdata.php` returned the customer's referral code plus signup and referral credits from settings. `GET /customer/profile` returns the authenticated customer profile and a `referral` object with `signup_credit` and `referral_credit`.

Legacy `u_profile_edit.php` updated `name` and `password`. `PUT /customer/profile` keeps that behavior and relies on the customer model's hashed password cast so the new password is never stored as plain text.

The customer profile module uses:

- `App\Http\Controllers\Api\V1\Customer\CustomerProfileController`
- `UpdateCustomerProfileRequest`
- `App\Data\Customer\CustomerProfileData`
- customer profile actions under `App\Actions\Customer\Profile`
- `CustomerRepository` and `SettingRepository`
- `App\Http\Resources\Customer\CustomerProfileResource`

## Customer Home API

```text
GET /api/v1/customer/home?latitude={lat}&longitude={lng}&per_page={size}
```

This endpoint requires a customer Sanctum token and returns the mobile home payload. Admin, store, and rider tokens are rejected by identity boundary checks.

Legacy `u_home_data.php` required `uid`, `lats`, and `longs`, then returned active banners, active categories, favorite stores, spotlight stores, top stores, wallet balance, and app currency. The Laravel API keeps the same coverage with authenticated customer ownership instead of a trusted `uid` payload. Latitude and longitude are accepted for compatibility and future zone-aware filtering; current home sections use active relational store data until spatial zone parsing is formalized.

Home sections are capped by `per_page`, default to five records, and return active-only records. Favorite stores are scoped to the authenticated customer.

The customer home module uses:

- `App\Http\Controllers\Api\V1\Customer\CustomerHomeController`
- `CustomerHomeRequest`
- `App\Data\Customer\CustomerHomeQueryData`
- `ShowCustomerHomeAction`
- `BannerRepository`, `CategoryRepository`, `StoreRepository`, and `SettingRepository`
- `App\Http\Resources\Customer\CustomerHomeResource`

## Customer Store Search APIs

```text
GET /api/v1/customer/stores?latitude={lat}&longitude={lng}&search={term}&category_id={category}&per_page={size}
```

This endpoint requires a customer Sanctum token and returns active stores with customer-specific favorite metadata. Admin, store, and rider tokens are rejected by identity boundary checks.

Legacy `u_search_store.php` searched active stores by keyword, while `u_cat_wise_store.php` filtered active stores by legacy category id. The Laravel API combines those list behaviors into one paginated endpoint with optional `search` and `category_id`. The category filter maps the selected Laravel category title against the store `category_reference` field until the legacy `catid` relationship is normalized.

The list endpoint accepts `per_page`, always returns Laravel pagination metadata, includes total favorite counts, exposes whether the authenticated customer has favorited each store, and includes the first active coupon snippet when available.

The customer store search module uses:

- `App\Http\Controllers\Api\V1\Customer\CustomerStoreController`
- `CustomerStoreSearchRequest`
- `App\Data\Customer\CustomerStoreSearchQueryData`
- `ListCustomerStoresAction`
- `StoreRepository`
- `App\Http\Resources\Customer\CustomerStoreResource`

## Admin Customer Address CRUD

```text
GET    /api/v1/admin/customer-addresses
GET    /api/v1/admin/customer-addresses/{customerAddress}
POST   /api/v1/admin/customer-addresses
PUT    /api/v1/admin/customer-addresses/{customerAddress}
DELETE /api/v1/admin/customer-addresses/{customerAddress}
```

These endpoints require an admin Sanctum token with `users.manage`. They manage saved customer addresses from the legacy `tbl_address` table. Legacy `uid`, `a_lat`, `a_long`, `r_instruction`, and `a_type` map to Laravel `customer_id`, `latitude`, `longitude`, `rider_instruction`, and `type`.

The list endpoint supports `search` across address, landmark, rider instruction, address type, customer name, customer email, and customer mobile, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete customer addresses.

The admin customer address module uses:

- `CustomerAddressController`
- `CustomerAddressRequest` and `UpdateCustomerAddressRequest`
- `App\Data\Admin\CustomerAddressData` and `App\Data\Admin\ListQueryData`
- customer address actions under `App\Actions\Admin\CustomerAddresses`
- `CustomerAddressRepository`
- `App\Http\Resources\Admin\CustomerAddressResource`

## Customer Address APIs

```text
GET    /api/v1/customer/addresses
GET    /api/v1/customer/addresses/{address}
POST   /api/v1/customer/addresses
PUT    /api/v1/customer/addresses/{address}
DELETE /api/v1/customer/addresses/{address}
```

These endpoints require a customer Sanctum token and are scoped to the authenticated customer's saved addresses. Admin, store, and rider tokens are rejected by identity boundary checks.

Legacy `add_address.php` and `update_address.php` accepted `uid`, `lats`, `longs`, `address`, `landmark`, `r_instruction`, and `a_type`. The Laravel API maps those to authenticated customer ownership plus `latitude`, `longitude`, `address`, `landmark`, `rider_instruction`, and `type`.

The list endpoint supports `search` across address, landmark, rider instruction, and type, accepts `per_page`, and returns Laravel pagination metadata. Delete requests soft delete addresses.

The customer address module uses:

- `App\Http\Controllers\Api\V1\Customer\CustomerAddressController`
- `App\Http\Requests\Customer\CustomerAddressRequest` and `UpdateCustomerAddressRequest`
- `App\Data\Customer\CustomerAddressData` and `ListCustomerQueryData`
- customer address actions under `App\Actions\Customer\Addresses`
- `CustomerAddressRepository`
- `App\Http\Resources\Customer\CustomerAddressResource`

## Customer Favorite APIs

```text
GET  /api/v1/customer/favorites
POST /api/v1/customer/favorites/toggle
```

These endpoints require a customer Sanctum token and are scoped to the authenticated customer's favorite stores.

Legacy `u_fav.php` toggled a store favorite: if the customer/store row existed it deleted the row, otherwise it inserted `uid`, `store_id`, and the store's `zone_id`. The Laravel API keeps the same toggle behavior, but uses soft deletes and restores a previous favorite to respect the unique customer/store pair.

The list endpoint supports `search` across store title, store address, and zone title, accepts `per_page`, and returns Laravel pagination metadata.

The customer favorite module uses:

- `App\Http\Controllers\Api\V1\Customer\CustomerFavoriteController`
- `FavoriteToggleRequest`
- `App\Data\Customer\FavoriteToggleData` and `ListCustomerQueryData`
- customer favorite actions under `App\Actions\Customer\Favorites`
- `FavoriteRepository`
- `App\Http\Resources\Customer\FavoriteResource`

## Customer Coupon APIs

```text
GET  /api/v1/customer/stores/{store}/coupons
POST /api/v1/customer/coupons/check
```

These endpoints require a customer Sanctum token. Admin, store, and rider tokens are rejected by identity boundary checks.

Legacy `u_couponlist.php` returned active, non-expired coupons for a store and disabled expired coupons as a side effect. The Laravel API returns active, non-expired coupons without mutating expired rows during reads.

Legacy `u_check_coupon.php` only checked that a coupon id existed. The Laravel API checks that the coupon exists, is active, and is not expired before returning `Coupon applied successfully.`

The list endpoint supports `search` across title, subtitle, code, and description, accepts `per_page`, and returns Laravel pagination metadata.

The customer coupon module uses:

- `App\Http\Controllers\Api\V1\Customer\CustomerCouponController`
- `CouponCheckRequest`
- `App\Data\Customer\CouponCheckData` and `ListCustomerQueryData`
- customer coupon actions under `App\Actions\Customer\Coupons`
- `CouponRepository`
- `App\Http\Resources\Customer\CouponResource`

## Customer Payment Method APIs

```text
GET /api/v1/customer/payment-methods
```

This endpoint requires a customer Sanctum token and returns visible, active payment methods. Admin, store, and rider tokens are rejected by identity boundary checks.

Legacy `u_paymentgateway.php` returned rows from `tbl_payment_list` where `status = 1`. The Laravel API maps that behavior to `is_active = true` and also respects `is_visible = true`.

The list endpoint supports `search` across title, subtitle, and image path, accepts `per_page`, and returns Laravel pagination metadata.

The customer payment method module uses:

- `App\Http\Controllers\Api\V1\Customer\CustomerPaymentMethodController`
- `ListCustomerResourcesRequest`
- `App\Data\Customer\ListCustomerQueryData`
- `ListCustomerPaymentMethodsAction`
- `PaymentMethodRepository`
- `App\Http\Resources\Customer\PaymentMethodResource`

## Admin Favorite CRUD

```text
GET    /api/v1/admin/favorites
GET    /api/v1/admin/favorites/{favorite}
POST   /api/v1/admin/favorites
PUT    /api/v1/admin/favorites/{favorite}
DELETE /api/v1/admin/favorites/{favorite}
```

These endpoints require an admin Sanctum token with `users.manage`. They manage customer favorite-store records from the legacy `tbl_fav` table. Legacy `uid`, `store_id`, and `zone_id` map to Laravel `customer_id`, `store_id`, and `zone_id`.

The legacy customer API toggled favorites in `user_api/u_fav.php`: if a customer/store pair existed it was removed; otherwise it was inserted with the store's zone. The admin API exposes direct CRUD for operational management and keeps the customer/store pair unique.

The list endpoint supports `search` across customer name/email/mobile, store title, and zone title, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete favorites.

The admin favorite module uses:

- `FavoriteController`
- `FavoriteRequest` and `UpdateFavoriteRequest`
- `App\Data\Admin\FavoriteData` and `App\Data\Admin\ListQueryData`
- favorite actions under `App\Actions\Admin\Favorites`
- `FavoriteRepository`
- `App\Http\Resources\Admin\FavoriteResource`

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

## Admin Store CRUD

```text
GET    /api/v1/admin/stores
GET    /api/v1/admin/stores/{store}
POST   /api/v1/admin/stores
PUT    /api/v1/admin/stores/{store}
DELETE /api/v1/admin/stores/{store}
```

These endpoints require an admin Sanctum token with `stores.manage`. They manage store identity and profile records from the legacy `service_details` table. Legacy fields such as `rimg`, `cover_img`, `rate`, `catid`, `sdesc`, `cdesc`, `dcharge`, `morder`, `commission`, `opentime`, `closetime`, and `is_pickup` map to Laravel `image_path`, `cover_image_path`, `rating`, `category_reference`, `short_description`, `content_description`, `delivery_charge`, `minimum_order_amount`, `commission_percent`, `opens_at`, `closes_at`, and `is_pickup_enabled`.

The list endpoint supports `search` across store title, email, mobile, address, and zone title, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete stores. Passwords are accepted only as write input and are hashed by the model cast; API resources never expose the password hash.

The admin store module uses:

- `StoreController`
- `StoreRequest` and `UpdateStoreRequest`
- `App\Data\Admin\StoreData` and `App\Data\Admin\ListQueryData`
- store actions under `App\Actions\Admin\Stores`
- `StoreRepository`
- `App\Http\Resources\Admin\StoreResource`

## Admin Rider CRUD

```text
GET    /api/v1/admin/riders
GET    /api/v1/admin/riders/{rider}
POST   /api/v1/admin/riders
PUT    /api/v1/admin/riders/{rider}
DELETE /api/v1/admin/riders/{rider}
```

These endpoints require an admin Sanctum token with `riders.manage`. They manage delivery rider records from the legacy `tbl_rider` table. Legacy `title`, `img`, `ccode`, `status`, and `rdate` map to Laravel `name`, `image_path`, `country_code`, `is_active`, and `joined_at`.

The list endpoint supports `search` across rider name, email, mobile, and store title, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete riders. Passwords are accepted only as write input and are hashed by the model cast; API resources never expose the password hash.

The admin rider module uses:

- `RiderController`
- `RiderRequest` and `UpdateRiderRequest`
- `App\Data\Admin\RiderData` and `App\Data\Admin\ListQueryData`
- rider actions under `App\Actions\Admin\Riders`
- `RiderRepository`
- `App\Http\Resources\Admin\RiderResource`

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

## Admin Payment Method CRUD

```text
GET    /api/v1/admin/payment-methods
GET    /api/v1/admin/payment-methods/{paymentMethod}
POST   /api/v1/admin/payment-methods
PUT    /api/v1/admin/payment-methods/{paymentMethod}
DELETE /api/v1/admin/payment-methods/{paymentMethod}
```

These endpoints require an admin Sanctum token with `settings.update`. They manage payment gateway records from the legacy `tbl_payment_list` table. Legacy `img`, `attributes`, `subtitle`, `p_show`, and `status` map to Laravel `image_path`, `attributes`, `subtitle`, `is_visible`, and `is_active`.

The list endpoint supports `search` across payment title, subtitle, and image path, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete payment methods.

The admin payment method module uses:

- `PaymentMethodController`
- `PaymentMethodRequest` and `UpdatePaymentMethodRequest`
- `App\Data\Admin\PaymentMethodData` and `App\Data\Admin\ListQueryData`
- payment method actions under `App\Actions\Admin\PaymentMethods`
- `PaymentMethodRepository`
- `App\Http\Resources\Admin\PaymentMethodResource`

## Admin Zone CRUD

```text
GET    /api/v1/admin/zones
GET    /api/v1/admin/zones/{zone}
POST   /api/v1/admin/zones
PUT    /api/v1/admin/zones/{zone}
DELETE /api/v1/admin/zones/{zone}
```

These endpoints require an admin Sanctum token with `settings.update`. They manage delivery zone records from the legacy `zones` table. Legacy `status` maps to Laravel `is_active`; legacy polygon display text is retained as `alias`.

The list endpoint supports `search` across zone title and alias, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete zones.

The admin zone module uses:

- `ZoneController`
- `ZoneRequest` and `UpdateZoneRequest`
- `App\Data\Admin\ZoneData` and `App\Data\Admin\ListQueryData`
- zone actions under `App\Actions\Admin\Zones`
- `ZoneRepository`
- `App\Http\Resources\Admin\ZoneResource`
