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
