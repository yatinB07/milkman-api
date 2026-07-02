# Subscriptions

Legacy references include `tbl_subscribe_order`, `tbl_subscribe_order_product`, `pporder.php`, `cporder.php`, `coporder.php`, and related user/store/rider subscription API files.

## Admin Subscription Order CRUD

```text
GET    /api/v1/admin/subscription-orders
GET    /api/v1/admin/subscription-orders/{subscriptionOrder}
POST   /api/v1/admin/subscription-orders
PUT    /api/v1/admin/subscription-orders/{subscriptionOrder}
DELETE /api/v1/admin/subscription-orders/{subscriptionOrder}
```

These endpoints require an admin Sanctum token with `orders.update-status`. They manage subscription order records from the legacy `tbl_subscribe_order` table and expose related store, customer, payment method, rider, and subscription item data for admin operations.

The list endpoint supports `search` across transaction id, customer snapshot fields, status, order type, store identity, customer identity, and rider identity. It accepts `per_page` and returns Laravel pagination metadata. Delete requests soft delete subscription orders.

This admin CRUD intentionally handles the subscription order row only. Subscription item schedules, skip/extend, delivery completion dates, wallet effects, notifications, and store/rider/customer subscription APIs should be implemented as separate Actions/Services so side effects stay explicit and testable.

The admin subscription order module uses:

- `SubscriptionOrderController`
- `SubscriptionOrderRequest` and `UpdateSubscriptionOrderRequest`
- `App\Data\Admin\SubscriptionOrderData` and `App\Data\Admin\ListQueryData`
- subscription order actions under `App\Actions\Admin\SubscriptionOrders`
- `SubscriptionOrderRepository`
- `App\Http\Resources\Admin\SubscriptionOrderResource`
