# Rider APIs

Legacy references include `rider_api/u_dashboard.php`, rider order history files, rider order status decisions, completion flows, and rider notification/page endpoints.

## Rider Dashboard API

```text
GET /api/v1/rider/dashboard
```

This endpoint requires a rider Sanctum token with `orders.view`. Admin, customer, and store tokens are rejected by identity boundary checks.

Legacy `rider_api/u_dashboard.php` returned assigned normal-order count, completed normal-order count, assigned subscription-order count, and completed subscription-order count for the provided `rider_id`. The Laravel endpoint uses the authenticated rider instead of accepting `rider_id`.

The response keeps legacy-style dashboard `cards` for client compatibility and also exposes named `counts` for newer clients. The legacy `withdraw_limit` value came from `tbl_setting.pstore`; the normalized settings table does not include a rider withdrawal-limit field yet, so this endpoint returns `0.00` until that setting is modeled.

The rider dashboard module uses:

- `App\Http\Controllers\Api\V1\Rider\RiderDashboardController`
- `ShowRiderDashboardAction`
- `RiderRepository::dashboardMetrics`
- `App\Http\Resources\Rider\RiderDashboardResource`

## Rider Notification Read API

```text
GET /api/v1/rider/notifications
GET /api/v1/rider/notifications/{notification}
```

These endpoints require a rider Sanctum token with `orders.view`. They modernize legacy `rider_api/u_notification_list.php` by using the authenticated rider instead of accepting `rider_id` from the payload.

The list endpoint supports `search` across notification title and message. It accepts `per_page` and returns Laravel pagination metadata. Show operations are rider-scoped, so a rider cannot read another rider's notification. Notification creation remains part of order assignment and status workflows.

The rider notification module uses:

- `App\Http\Controllers\Api\V1\Rider\RiderNotificationController`
- `ListRiderResourcesRequest`
- `App\Data\Rider\ListRiderQueryData`
- rider notification actions under `App\Actions\Rider\Notifications`
- `RiderNotificationRepository`
- `App\Http\Resources\Rider\RiderNotificationResource`

## Rider Page Read API

```text
GET /api/v1/rider/pages
GET /api/v1/rider/pages/{page}
```

These endpoints require a rider Sanctum token with `orders.view`. They modernize legacy `rider_api/u_pagelist.php` by returning active pages only.

The list endpoint supports `search` across page title and description. It accepts `per_page` and returns Laravel pagination metadata. Show operations only return active pages. Page creation and maintenance remain admin-side workflows.

The rider page module uses:

- `App\Http\Controllers\Api\V1\Rider\RiderPageController`
- `ListRiderResourcesRequest`
- `App\Data\Rider\ListRiderQueryData`
- rider page actions under `App\Actions\Rider\Pages`
- `PageRepository`
- `App\Http\Resources\Rider\RiderPageResource`

## Rider Normal Order Read API

```text
GET /api/v1/rider/orders
GET /api/v1/rider/orders/{order}
```

These endpoints require a rider Sanctum token with `orders.view`. They modernize legacy `rider_api/u_order_history.php` and `rider_api/u_order_information.php` by using the authenticated rider instead of accepting `rider_id` from the payload.

The list endpoint accepts `status=current|past`, `search`, and `per_page`. `current` excludes `Completed` and `Cancelled`; `past` includes only `Completed` and `Cancelled`, matching the legacy current/history split. Search covers transaction id, customer name, customer mobile, address, status, and order type. Show operations are rider-scoped, so a rider cannot read another rider's order.

This module is read-only. Rider order status decisions and completion flows remain separate workflows from legacy `make_decision.php` and `complete_order.php`.

The rider normal order module uses:

- `App\Http\Controllers\Api\V1\Rider\RiderOrderController`
- `RiderOrderHistoryRequest`
- `App\Data\Rider\RiderOrderHistoryQueryData`
- rider order actions under `App\Actions\Rider\Orders`
- `OrderRepository`
- `App\Http\Resources\Rider\RiderOrderResource`

## Rider Normal Order Decision API

```text
POST /api/v1/rider/orders/{order}/decision
```

This endpoint requires a rider Sanctum token with `orders.update-status`. It modernizes legacy `rider_api/make_decision.php` by using the authenticated rider and a named `decision` value instead of the legacy numeric `status` flag.

Payload:

- `decision`: `accepted` or `rejected`
- `rejection_comment`: required when `decision` is `rejected`

Accepted orders are moved to `On Route` with `internal_status=4`. Rejected orders clear `rider_id`, move `internal_status` to `5`, and store the rejection comment so the store can assign another rider. The action records customer/store notifications using language-file messages. Push delivery remains a future integration concern.

The rider normal order decision module uses:

- `RiderOrderController::decide`
- `RiderOrderDecisionRequest`
- `App\Data\Rider\RiderOrderDecisionData`
- `DecideRiderOrderAction`
- `OrderRepository`
- `CustomerNotificationRepository`
- `StoreNotificationRepository`
- `App\Http\Resources\Rider\RiderOrderResource`

## Rider Subscription Order Read API

```text
GET /api/v1/rider/subscription-orders
GET /api/v1/rider/subscription-orders/{subscriptionOrder}
```

These endpoints require a rider Sanctum token with `orders.view`. They modernize legacy `rider_api/u_subscription_history.php` and `rider_api/d_sub_order_product_list.php` by using the authenticated rider instead of accepting `rider_id` from the payload.

The list endpoint accepts `status=current|past`, `search`, and `per_page`. `current` excludes `Completed` and `Cancelled`; `past` includes only `Completed` and `Cancelled`, matching the legacy current/history split. Search covers transaction id, customer name, customer mobile, address, status, and order type. Show operations are rider-scoped and include subscription item schedule data derived from total/completed dates.

This module is read-only. Rider subscription order status decisions, completion, and date workflows remain separate workflows from legacy `sub_decision.php`, `sub_complete.php`, and `completedate.php`.

The rider subscription order module uses:

- `App\Http\Controllers\Api\V1\Rider\RiderSubscriptionOrderController`
- `RiderOrderHistoryRequest`
- `App\Data\Rider\RiderOrderHistoryQueryData`
- rider subscription order actions under `App\Actions\Rider\SubscriptionOrders`
- `SubscriptionOrderRepository`
- `App\Http\Resources\Rider\RiderSubscriptionOrderResource`
