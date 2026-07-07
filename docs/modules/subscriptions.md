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

This admin CRUD intentionally handles the subscription order row only. Subscription item schedules, skip/extend, delivery completion dates, wallet effects, notifications, and store/rider/customer subscription APIs are implemented through separate Actions/Services so side effects stay explicit and testable.

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

Customer skip/extend behavior and rider/store completion workflows are covered by focused APIs below and in the store/rider modules. Push notification delivery remains a future queue/integration slice; persisted notification rows are handled by workflow actions that change subscription state.

The customer subscription order module uses:

- `App\Http\Controllers\Api\V1\Customer\CustomerSubscriptionOrderController`
- `CustomerSubscriptionOrderRequest`
- `App\Data\Customer\CustomerSubscriptionOrderData` and `CustomerSubscriptionOrderItemData`
- `PlaceCustomerSubscriptionOrderAction`
- `SubscriptionOrderRepository`, `StoreRepository`, and `WalletService`
- `App\Http\Resources\Customer\CustomerSubscriptionOrderResource`

## Customer Subscription History APIs

```text
GET /api/v1/customer/subscription-orders?status=current|past&search={term}&per_page={size}
GET /api/v1/customer/subscription-orders/{subscriptionOrder}
```

These endpoints require a customer Sanctum token. Admin, store, and rider tokens are rejected by identity boundary checks.

Legacy `d_subscribe_order_history.php` split subscription orders into `Current` and past groups. The Laravel list endpoint maps that to `status=current` for subscriptions not `Completed` or `Cancelled`, and `status=past` for completed/cancelled subscriptions. It adds pagination and search across transaction id, customer snapshot fields, order status, and store data.

Legacy `d_sub_order_product_list.php` returned subscription detail with payment method, rider, totals, item snapshots, and generated delivery dates marked complete or pending. The Laravel detail endpoint keeps the authenticated customer ownership boundary and exposes item `schedule` entries with `date`, `is_complete`, and `format_date`.

The customer subscription history module uses:

- `App\Http\Controllers\Api\V1\Customer\CustomerSubscriptionOrderController`
- `CustomerOrderHistoryRequest`
- `App\Data\Customer\CustomerOrderHistoryQueryData`
- `ListCustomerSubscriptionOrdersAction` and `ShowCustomerSubscriptionOrderAction`
- `SubscriptionOrderRepository`
- `App\Http\Resources\Customer\CustomerSubscriptionOrderResource`

## Customer Subscription Rating API

```text
POST /api/v1/customer/subscription-orders/{subscriptionOrder}/rating
```

This endpoint requires a customer Sanctum token. It lets the authenticated customer rate their own subscription order, matching the legacy `u_prate_update.php` behavior that wrote `total_rate`, `rate_text`, `is_rate`, and `review_date` onto `tbl_subscribe_order`.

The Laravel endpoint accepts `total_rating` from 1 to 5 and `rating_text`. It keeps the customer ownership check in the action/repository flow, stores user-facing messages in the language file, and returns the refreshed `CustomerSubscriptionOrderResource` with rating fields.

The customer subscription rating module uses:

- `CustomerOrderRatingRequest`
- `App\Data\Customer\CustomerOrderRatingData`
- `RateCustomerSubscriptionOrderAction`
- `SubscriptionOrderRepository::rate`
- `App\Http\Resources\Customer\CustomerSubscriptionOrderResource`

## Customer Subscription Schedule APIs

```text
POST /api/v1/customer/subscription-orders/{subscriptionOrder}/items/{item}/skip
POST /api/v1/customer/subscription-orders/{subscriptionOrder}/items/{item}/extend
```

These endpoints require a customer Sanctum token. They modernize legacy `user_api/skip_extend.php` by replacing its `status=skip|extended` switch with two explicit endpoints and a simple `dates` array payload.

Skip removes selected dates from the subscription item schedule, reduces the item delivery count, recalculates the subscription subtotal/total, credits the customer wallet for the skipped item amount, and writes a credit wallet transaction. Extend removes selected dates and appends the same number of future dates using the item `selected_days` weekday pattern.

Both endpoints enforce customer ownership through the subscription order item repository and reject dates that are not currently present in the item schedule.

The customer subscription schedule module uses:

- `SubscriptionScheduleChangeRequest`
- `App\Data\Customer\SubscriptionScheduleChangeData`
- `SkipCustomerSubscriptionScheduleAction` and `ExtendCustomerSubscriptionScheduleAction`
- `SubscriptionScheduleService`
- `SubscriptionOrderItemRepository` and `SubscriptionOrderRepository`
- `WalletService` for skip refunds
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

Skip/extend behavior from `user_api/skip_extend.php`, wallet refunds, and total recalculation are implemented by the customer subscription schedule API. Delivery completion is handled by the rider and store subscription completion workflows. This CRUD endpoint remains side-effect-light and only manages item records directly.

The admin subscription order item module uses:

- `SubscriptionOrderItemController`
- `SubscriptionOrderItemRequest` and `UpdateSubscriptionOrderItemRequest`
- `App\Data\Admin\SubscriptionOrderItemData` and `App\Data\Admin\ListQueryData`
- subscription order item actions under `App\Actions\Admin\SubscriptionOrderItems`
- `SubscriptionOrderItemRepository`
- `App\Http\Resources\Admin\SubscriptionOrderItemResource`
