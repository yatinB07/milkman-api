# Notifications

Notification behavior is split between inbox records and outbound delivery transports.

Legacy references include OneSignal settings in `tbl_setting`, customer notifications, store notifications, and rider notifications.

## Admin Customer Notification CRUD

```text
GET    /api/v1/admin/customer-notifications
GET    /api/v1/admin/customer-notifications/{customerNotification}
POST   /api/v1/admin/customer-notifications
PUT    /api/v1/admin/customer-notifications/{customerNotification}
DELETE /api/v1/admin/customer-notifications/{customerNotification}
```

These endpoints require an admin Sanctum token with `users.manage`. They manage customer inbox records from the legacy `tbl_notification` table. Legacy `uid`, `datetime`, `title`, and `description` map to Laravel `customer_id`, `notified_at`, `title`, and `description`.

The list endpoint supports `search` across notification title, description, customer name, customer email, and customer mobile, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete customer notifications.

Legacy order and rider/store decision flows wrote rows to `tbl_notification` and separately sent OneSignal notifications. This admin CRUD manages only the stored inbox records; outbound delivery should stay in jobs/services such as `DispatchDomainNotificationJob`.

The admin customer notification module uses:

- `CustomerNotificationController`
- `CustomerNotificationRequest` and `UpdateCustomerNotificationRequest`
- `App\Data\Admin\CustomerNotificationData` and `App\Data\Admin\ListQueryData`
- customer notification actions under `App\Actions\Admin\CustomerNotifications`
- `CustomerNotificationRepository`
- `App\Http\Resources\Admin\CustomerNotificationResource`

## Admin Store Notification CRUD

```text
GET    /api/v1/admin/store-notifications
GET    /api/v1/admin/store-notifications/{storeNotification}
POST   /api/v1/admin/store-notifications
PUT    /api/v1/admin/store-notifications/{storeNotification}
DELETE /api/v1/admin/store-notifications/{storeNotification}
```

These endpoints require an admin Sanctum token with `stores.manage`. They manage store inbox records from the legacy `tbl_snoti` table. Legacy `sid`, `datetime`, `title`, and `description` map to Laravel `store_id`, `notified_at`, `title`, and `description`.

The list endpoint supports `search` across notification title, description, store title, store email, and store mobile, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete store notifications.

The admin store notification module uses:

- `StoreNotificationController`
- `StoreNotificationRequest` and `UpdateStoreNotificationRequest`
- `App\Data\Admin\StoreNotificationData` and `App\Data\Admin\ListQueryData`
- store notification actions under `App\Actions\Admin\StoreNotifications`
- `StoreNotificationRepository`
- `App\Http\Resources\Admin\StoreNotificationResource`

## Admin Rider Notification CRUD

```text
GET    /api/v1/admin/rider-notifications
GET    /api/v1/admin/rider-notifications/{riderNotification}
POST   /api/v1/admin/rider-notifications
PUT    /api/v1/admin/rider-notifications/{riderNotification}
DELETE /api/v1/admin/rider-notifications/{riderNotification}
```

These endpoints require an admin Sanctum token with `riders.manage`. They manage rider inbox records from the legacy `tbl_rnoti` table. Legacy `rid`, `datetime`, `title`, and notification body columns map to Laravel `rider_id`, `notified_at`, `title`, and `message`.

The list endpoint supports `search` across notification title, message, rider name, rider email, and rider mobile, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete rider notifications.

The admin rider notification module uses:

- `RiderNotificationController`
- `RiderNotificationRequest` and `UpdateRiderNotificationRequest`
- `App\Data\Admin\RiderNotificationData` and `App\Data\Admin\ListQueryData`
- rider notification actions under `App\Actions\Admin\RiderNotifications`
- `RiderNotificationRepository`
- `App\Http\Resources\Admin\RiderNotificationResource`
