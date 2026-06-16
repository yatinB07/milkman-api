# Schema Coverage

This tracker defines the required Laravel coverage for the legacy `milkyway` application tables.

Legacy rows are test/demo data and are not imported. Legacy tables, columns, SQL usage, relationships, and workflows are required reference material. A legacy table is complete only when it is implemented in Laravel, intentionally merged into another documented model, or marked obsolete with a reason.

| Legacy table | Laravel destination | Status | Notes |
| --- | --- | --- | --- |
| `admin` | `admins` | Schema implemented | Foundation identity migration plus legacy `username` coverage. |
| `tbl_user` | `customers` | Schema implemented | Foundation identity migration plus registration date coverage. |
| `service_details` | `stores` | Schema implemented | Store profile, zone, bank, charge, media, slogan, and policy columns covered. |
| `tbl_rider` | `riders` | Schema implemented | Foundation identity migration plus image coverage. |
| `banner` | `banners` | Schema implemented | Marketing/home display. |
| `tbl_category` | `categories` | Schema implemented | Product/catalog category. |
| `zones` | `zones` | Schema implemented | Delivery zone lookup; coordinates stored as text for portable schema tests. |
| `tbl_mcat` | `store_categories` | Schema implemented | Store category mapping. |
| `tbl_product` | `products` | Schema implemented | Catalog product. |
| `tbl_product_attribute` | `product_variants` | Schema implemented | Product variants/pricing. |
| `tbl_extra` | `product_images` | Schema implemented | Product image gallery. |
| `tbl_photo` | `store_gallery_images` | Schema implemented | Store gallery. |
| `tbl_delivery` | `delivery_options` | Schema implemented | Store delivery options. |
| `tbl_time` | `time_slots` | Schema implemented | Store delivery/pickup slots. |
| `tbl_coupon` | `coupons` | Schema implemented | Store coupon rules. |
| `tbl_faq` | `faqs` | Schema implemented | Store FAQ content. |
| `tbl_page` | `pages` | Schema implemented | CMS/static pages. |
| `tbl_payment_list` | `payment_methods` | Schema implemented | Payment method catalog. |
| `tbl_normal_order` | `orders` | Schema implemented | Normal order workflow. |
| `tbl_normal_order_product` | `order_items` | Schema implemented | Normal order line items. |
| `tbl_subscribe_order` | `subscription_orders` | Schema implemented | Subscription order workflow. |
| `tbl_subscribe_order_product` | `subscription_order_items` | Schema implemented | Subscription line items/schedules. |
| `tbl_notification` | `customer_notifications` | Schema implemented | Customer notification inbox. |
| `tbl_snoti` | `store_notifications` | Schema implemented | Store notification inbox. |
| `tbl_rnoti` | `rider_notifications` | Schema implemented | Rider notification inbox. |
| `tbl_fav` | `favorites` | Schema implemented | Customer favorite stores/products. |
| `tbl_address` | `customer_addresses` | Schema implemented | Customer saved addresses. |
| `payout_setting` | `payout_requests` | Schema implemented | Store payout requests. |
| `tbl_cash` | `cash_collections` | Schema implemented | Cash settlement tracking. |
| `wallet_report` | `wallet_transactions` | Schema implemented | Customer wallet ledger. |
| `tbl_setting` | `settings` | Schema implemented | Global settings and integration keys. |
| `tbl_milk` | `milk_data` | Schema implemented, behavior pending review | Stored as raw reference payload until the legacy purpose is fully reviewed. |

## Completion Rules

- `Schema implemented` means the Laravel migration, Eloquent model, explicit `$fillable` coverage, casts, relationship coverage where applicable, and schema tests exist.
- `Implemented` means the schema, API behavior, factory/seeder path, and feature tests exist.
- `Pending` means it is required and not yet complete.
- `Merged` means the legacy table's responsibility was intentionally absorbed into another Laravel model and documented.
- `Obsolete` means the table was intentionally removed with a documented reason.

## Verification Commands

Use these commands during Phase 2 schema work:

```bash
php artisan milkman:inspect-legacy-schema
php artisan milkman:verify-schema
php artisan milkman:seed-demo-data
```

`milkman:inspect-legacy-schema` prints the current legacy-to-Laravel table coverage decisions. It ignores legacy rows because the old database only contains disposable test data.

`milkman:verify-schema` checks that every required Laravel destination table exists in the migrated schema.

`milkman:seed-demo-data` runs the idempotent demo seeder explicitly. Docker startup does not run seeders automatically.

## Demo Seed Data

`Database\Seeders\DemoDataSeeder` creates an idempotent development dataset without importing legacy rows.

The dataset includes:

- Role-backed demo identities for admin, store, rider, and customer accounts.
- Store/catalog records: zone, banner, category, store category, product, variant, product image, gallery image, delivery option, time slot, coupon, FAQ, page, and payment method.
- Customer workflow records: address, favorite store, normal order, subscription order, order items, wallet transaction, and notifications.
- Store/rider operations records: payout request, cash collection, store notification, and rider notification.
- Global/reference records: settings and `milk_data` placeholder data for the legacy `tbl_milk` table.

The seeder uses deterministic natural keys such as demo emails, coupon code, and transaction IDs so it can be run repeatedly without creating duplicate demo records.

## Factory Coverage

Every legacy-covered application model has a working factory unless it is intentionally documented otherwise. `tests/Feature/Schema/LegacyModelFactoryTest.php` creates each domain model once to prove the factories and foreign-key defaults stay valid.
