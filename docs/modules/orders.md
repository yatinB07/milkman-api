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
