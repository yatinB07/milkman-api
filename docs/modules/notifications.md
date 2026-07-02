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
