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
