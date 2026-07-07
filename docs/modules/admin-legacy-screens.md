# Admin Legacy Screens

The root `MilkMan_web/*.php` files are legacy admin panel screens or helper endpoints. The Laravel migration treats these as workflow references. Admin panel UI is now owned by `milkman-admin`; API behavior is owned by `milkman-api`.

This map prevents root legacy screens from being silently skipped while modules are migrated.

| Legacy file | Laravel coverage |
| --- | --- |
| `a.php` | Legacy helper/debug reference; no production API behavior required. |
| `add_Page.php` | `POST/PUT /api/v1/admin/pages` through Admin Page CRUD. |
| `add_Zone.php` | `POST/PUT /api/v1/admin/zones` through Admin Zone CRUD. |
| `add_banner.php` | `POST/PUT /api/v1/admin/banners` through Admin Banner CRUD. |
| `add_category.php` | `POST/PUT /api/v1/admin/categories` through Admin Category CRUD. |
| `add_coupon.php` | `POST/PUT /api/v1/admin/coupons` through Admin Coupon CRUD. |
| `add_delivery.php` | `POST/PUT /api/v1/admin/delivery-options` through Admin Delivery Option CRUD. |
| `add_extra.php` | `POST/PUT /api/v1/admin/product-images` through Admin Product Image CRUD. |
| `add_faq.php` | `POST/PUT /api/v1/admin/faqs` through Admin FAQ CRUD. |
| `add_mcat.php` | `POST/PUT /api/v1/admin/store-categories` through Admin Store Category CRUD. |
| `add_payout.php` | `POST/PUT /api/v1/admin/payout-requests` through Admin Payout Request CRUD. |
| `add_photo.php` | `POST/PUT /api/v1/admin/store-gallery-images` through Admin Store Gallery Image CRUD. |
| `add_product.php` | `POST/PUT /api/v1/admin/products` through Admin Product CRUD. |
| `add_product_attr.php` | `POST/PUT /api/v1/admin/product-variants` through Admin Product Variant CRUD. |
| `add_rider.php` | `POST/PUT /api/v1/admin/riders` through Admin Rider CRUD. |
| `add_store.php` | `POST/PUT /api/v1/admin/stores` through Admin Store CRUD. |
| `add_time.php` | `POST/PUT /api/v1/admin/time-slots` through Admin Time Slot CRUD. |
| `cancle.php` | Admin order status workflow reference; covered by order update/status flows. |
| `check.php` | Legacy session/domain helper; no production API behavior required. |
| `complete.php` | Admin order completion reference; operational completion now belongs to store/rider completion APIs. |
| `coporder.php` | Admin completed normal-order listing reference; covered by Admin Order CRUD/list filters. |
| `cporder.php` | Admin completed subscription-order listing reference; covered by Admin Subscription Order CRUD/list filters. |
| `dashboard.php` | `GET /api/v1/admin/dashboard` through Admin Dashboard API. |
| `earningreport.php` | `GET /api/v1/admin/earning-reports` through Admin Earning Report API. |
| `edit_payment.php` | `PUT /api/v1/admin/payment-methods/{paymentMethod}` through Admin Payment Method CRUD. |
| `get_admin.php` | `GET /api/v1/admin/profile` through Admin Profile API. |
| `get_milk_data.php` | `GET /api/v1/admin/milk-data` through Admin Milk Data CRUD. |
| `index.php` | Admin login UI reference; covered by `POST /api/v1/admin/auth/login`. |
| `list_Page.php` | `GET /api/v1/admin/pages` through Admin Page CRUD. |
| `list_Zone.php` | `GET /api/v1/admin/zones` through Admin Zone CRUD. |
| `list_banner.php` | `GET /api/v1/admin/banners` through Admin Banner CRUD. |
| `list_category.php` | `GET /api/v1/admin/categories` through Admin Category CRUD. |
| `list_coupon.php` | `GET /api/v1/admin/coupons` through Admin Coupon CRUD. |
| `list_delivery.php` | `GET /api/v1/admin/delivery-options` through Admin Delivery Option CRUD. |
| `list_extra.php` | `GET /api/v1/admin/product-images` through Admin Product Image CRUD. |
| `list_faq.php` | `GET /api/v1/admin/faqs` through Admin FAQ CRUD. |
| `list_mcat.php` | `GET /api/v1/admin/store-categories` through Admin Store Category CRUD. |
| `list_payout.php` | `GET /api/v1/admin/payout-requests` through Admin Payout Request CRUD. |
| `list_photo.php` | `GET /api/v1/admin/store-gallery-images` through Admin Store Gallery Image CRUD. |
| `list_product.php` | `GET /api/v1/admin/products` through Admin Product CRUD. |
| `list_product_attr.php` | `GET /api/v1/admin/product-variants` through Admin Product Variant CRUD. |
| `list_rider.php` | `GET /api/v1/admin/riders` through Admin Rider CRUD. |
| `list_store.php` | `GET /api/v1/admin/stores` through Admin Store CRUD. |
| `list_time.php` | `GET /api/v1/admin/time-slots` through Admin Time Slot CRUD. |
| `logout.php` | `POST /api/v1/admin/auth/logout` through identity auth. |
| `order_product_data.php` | Admin normal-order item detail reference; covered by Admin Order Item CRUD and order resources. |
| `payment_method.php` | `GET/POST /api/v1/admin/payment-methods` through Admin Payment Method CRUD. |
| `pending.php` | Admin pending normal-order listing reference; covered by Admin Order CRUD/list filters. |
| `porder_product_data.php` | Admin subscription-order item detail reference; covered by Admin Subscription Order Item CRUD and subscription resources. |
| `pporder.php` | Admin pending subscription-order listing reference; covered by Admin Subscription Order CRUD/list filters. |
| `profile.php` | `GET/PUT /api/v1/admin/profile` through Admin Profile API. |
| `setting.php` | `GET/PUT /api/v1/admin/settings` through Admin Setting CRUD. |
| `test_use.php` | Legacy helper/debug reference; no production API behavior required. |
| `userlist.php` | `GET /api/v1/admin/customers` through Admin Customer CRUD. |
| `validate_domain.php` | Legacy domain validation helper; deployment/domain validation belongs outside the API runtime. |
