# Orders

Legacy references include `tbl_normal_order`, `tbl_normal_order_product`, `pending.php`, `complete.php`, `cancle.php`, and the user/store/rider order API files.

## Admin Order CRUD

```text
GET    /api/v1/admin/orders
GET    /api/v1/admin/orders/{order}
POST   /api/v1/admin/orders
PUT    /api/v1/admin/orders/{order}
DELETE /api/v1/admin/orders/{order}
```

These endpoints require an admin Sanctum token with `orders.update-status`. They manage normal order records from the legacy `tbl_normal_order` table and expose related store, customer, payment method, rider, and item data for admin operations.

The list endpoint supports `search` across transaction id, customer snapshot fields, status, order type, store identity, customer identity, and rider identity. It accepts `per_page` and always returns Laravel pagination metadata. Delete requests soft delete orders.

This admin CRUD intentionally handles the order row only. Order item management, assignment workflows, status transitions, wallet effects, stock effects, notifications, and store/rider/customer order APIs should be implemented as separate Actions/Services so business side effects stay explicit and testable.

The admin order module uses:

- `OrderController`
- `OrderRequest` and `UpdateOrderRequest`
- `App\Data\Admin\OrderData` and `App\Data\Admin\ListQueryData`
- order actions under `App\Actions\Admin\Orders`
- `OrderRepository`
- `App\Http\Resources\Admin\OrderResource`

## Customer Normal Order Placement

```text
POST /api/v1/customer/orders
```

This endpoint requires a customer Sanctum token. Admin, store, and rider tokens are rejected by identity boundary checks.

Legacy `d_order_now.php` placed normal orders when `type = Normal`, created rows in `tbl_normal_order` and `tbl_normal_order_product`, debited wallet balance when `wall_amt` was used, and wrote a `wallet_report` debit row. The Laravel endpoint covers the normal-order part of that flow with a modern request shape, authenticated customer ownership, transactional order/item creation, wallet balance validation, wallet debit, and wallet transaction logging.

Subscription order placement, OneSignal notifications, customer/store notification rows, and advanced scheduling are intentionally left for later focused slices so those side effects can be implemented with explicit Actions, Events, Jobs, and tests.

The customer normal order module uses:

- `App\Http\Controllers\Api\V1\Customer\CustomerOrderController`
- `CustomerOrderRequest`
- `App\Data\Customer\CustomerOrderData` and `CustomerOrderItemData`
- `PlaceCustomerOrderAction`
- `OrderRepository`, `CustomerRepository`, `StoreRepository`, and `WalletTransactionRepository`
- `App\Http\Resources\Customer\CustomerOrderResource`

## Customer Order History APIs

```text
GET /api/v1/customer/orders?status=current|past&search={term}&per_page={size}
GET /api/v1/customer/orders/{order}
```

These endpoints require a customer Sanctum token. Admin, store, and rider tokens are rejected by identity boundary checks.

Legacy `d_order_history.php` split normal orders into `Current` and past groups. The Laravel list endpoint maps that to `status=current` for orders not `Completed` or `Cancelled`, and `status=past` for completed/cancelled orders. It also adds pagination and search across transaction id, customer snapshot fields, order status, and store data.

Legacy `d_order_product_list.php` returned a customer-owned order detail with payment method, store, rider, totals, notes, and item snapshots. The Laravel detail endpoint keeps that ownership boundary and returns related store, payment method, rider, and item data through `CustomerOrderResource`.

The customer order history module uses:

- `App\Http\Controllers\Api\V1\Customer\CustomerOrderController`
- `CustomerOrderHistoryRequest`
- `App\Data\Customer\CustomerOrderHistoryQueryData`
- `ListCustomerOrdersAction` and `ShowCustomerOrderAction`
- `OrderRepository`
- `App\Http\Resources\Customer\CustomerOrderResource`

## Customer Order Rating API

```text
POST /api/v1/customer/orders/{order}/rating
```

This endpoint requires a customer Sanctum token. It lets the authenticated customer rate their own normal order, matching the legacy `u_rate_update.php` behavior that wrote `total_rate`, `rate_text`, `is_rate`, and `review_date` onto `tbl_normal_order`.

The Laravel endpoint accepts `total_rating` from 1 to 5 and `rating_text`. It keeps the customer ownership check in the action/repository flow, stores user-facing messages in the language file, and returns the refreshed `CustomerOrderResource` with rating fields.

The customer order rating module uses:

- `CustomerOrderRatingRequest`
- `App\Data\Customer\CustomerOrderRatingData`
- `RateCustomerOrderAction`
- `OrderRepository::rate`
- `App\Http\Resources\Customer\CustomerOrderResource`

## Admin Order Item CRUD

```text
GET    /api/v1/admin/order-items
GET    /api/v1/admin/order-items/{orderItem}
POST   /api/v1/admin/order-items
PUT    /api/v1/admin/order-items/{orderItem}
DELETE /api/v1/admin/order-items/{orderItem}
```

These endpoints require an admin Sanctum token with `orders.update-status`. They manage normal order line items from the legacy `tbl_normal_order_product` table. Legacy `oid`, `pquantity`, `ptitle`, `pdiscount`, `pimg`, `pprice`, and `ptype` map to Laravel `order_id`, `quantity`, `product_title`, `discount`, `image_path`, `price`, and `variant_title`.

The list endpoint supports `search` across product title, variant title, and order snapshot fields, accepts `per_page`, and returns Laravel pagination metadata. Delete requests soft delete order items.

The admin order item module uses:

- `OrderItemController`
- `OrderItemRequest` and `UpdateOrderItemRequest`
- `App\Data\Admin\OrderItemData` and `App\Data\Admin\ListQueryData`
- order item actions under `App\Actions\Admin\OrderItems`
- `OrderItemRepository`
- `App\Http\Resources\Admin\OrderItemResource`
