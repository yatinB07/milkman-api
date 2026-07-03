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

## Customer Subscription Order Placement

```text
POST /api/v1/customer/subscription-orders
```

This endpoint requires a customer Sanctum token. Admin, store, and rider tokens are rejected by identity boundary checks.

Legacy `d_order_now.php` placed subscription orders when `type` was not `Normal`, created rows in `tbl_subscribe_order` and `tbl_subscribe_order_product`, generated delivery dates from `startdate`, `select_days`, and `total_deliveries`, debited wallet balance when `wall_amt` was used, and wrote a `wallet_report` debit row. The Laravel endpoint covers that placement flow with a modern request shape, authenticated customer ownership, transactional subscription order/item creation, generated `total_dates`, wallet balance validation, wallet debit, and wallet transaction logging.

OneSignal notifications, customer/store notification rows, skip/extend behavior, and delivery completion workflows are intentionally deferred to later focused slices so those side effects can use explicit Actions, Events, Jobs, and tests.

The customer subscription order module uses:

- `App\Http\Controllers\Api\V1\Customer\CustomerSubscriptionOrderController`
- `CustomerSubscriptionOrderRequest`
- `App\Data\Customer\CustomerSubscriptionOrderData` and `CustomerSubscriptionOrderItemData`
- `PlaceCustomerSubscriptionOrderAction`
- `SubscriptionOrderRepository`, `CustomerRepository`, `StoreRepository`, and `WalletTransactionRepository`
- `App\Http\Resources\Customer\CustomerSubscriptionOrderResource`

## Admin Subscription Order Item CRUD

```text
GET    /api/v1/admin/subscription-order-items
GET    /api/v1/admin/subscription-order-items/{subscriptionOrderItem}
POST   /api/v1/admin/subscription-order-items
PUT    /api/v1/admin/subscription-order-items/{subscriptionOrderItem}
DELETE /api/v1/admin/subscription-order-items/{subscriptionOrderItem}
```

These endpoints require an admin Sanctum token with `orders.update-status`. They manage rows from the legacy `tbl_subscribe_order_product` table and expose the item snapshot fields used by the legacy customer subscription order product list: quantity, product title, discount, image, price, variation, start date, delivery count, total dates, completed dates, selected days, and delivery time slot.

The list endpoint supports `search` across product title, variation, time slot, and parent subscription order transaction/customer snapshot fields. It accepts `per_page`, returns Laravel pagination metadata, and soft deletes subscription order items.

Skip/extend behavior from `user_api/skip_extend.php`, wallet refunds, total recalculation, and delivery completion workflows are not hidden in this CRUD endpoint. They should be implemented as dedicated subscription schedule Actions/Services so financial side effects stay explicit and testable.

The admin subscription order item module uses:

- `SubscriptionOrderItemController`
- `SubscriptionOrderItemRequest` and `UpdateSubscriptionOrderItemRequest`
- `App\Data\Admin\SubscriptionOrderItemData` and `App\Data\Admin\ListQueryData`
- subscription order item actions under `App\Actions\Admin\SubscriptionOrderItems`
- `SubscriptionOrderItemRepository`
- `App\Http\Resources\Admin\SubscriptionOrderItemResource`
