# Schema Coverage

This tracker defines the required Laravel coverage for the legacy `milkyway` application tables.

Legacy rows are test/demo data and are not imported. Legacy tables, columns, SQL usage, relationships, and workflows are required reference material. A legacy table is complete only when it is implemented in Laravel, intentionally merged into another documented model, or marked obsolete with a reason.

| Legacy table | Laravel destination | Status | Notes |
| --- | --- | --- | --- |
| `admin` | `admins` | Implemented | Foundation identity migration. |
| `tbl_user` | `customers` | Implemented | Foundation identity migration. |
| `service_details` | `stores` | Implemented | Foundation identity migration. Needs full store columns reviewed in catalog phase. |
| `tbl_rider` | `riders` | Implemented | Foundation identity migration. |
| `banner` | `banners` | Pending | Marketing/home display. |
| `tbl_category` | `categories` | Pending | Product/catalog category. |
| `zones` | `zones` | Pending | Delivery zone and spatial lookup. |
| `tbl_mcat` | `store_categories` | Pending | Store category mapping. |
| `tbl_product` | `products` | Pending | Catalog product. |
| `tbl_product_attribute` | `product_variants` | Pending | Product variants/pricing. |
| `tbl_extra` | `product_images` | Pending | Product image gallery. |
| `tbl_photo` | `store_gallery_images` | Pending | Store gallery. |
| `tbl_delivery` | `delivery_options` | Pending | Store delivery options. |
| `tbl_time` | `time_slots` | Pending | Store delivery/pickup slots. |
| `tbl_coupon` | `coupons` | Pending | Store coupon rules. |
| `tbl_faq` | `faqs` | Pending | Store FAQ content. |
| `tbl_page` | `pages` | Pending | CMS/static pages. |
| `tbl_payment_list` | `payment_methods` | Pending | Payment method catalog. |
| `tbl_normal_order` | `orders` | Pending | Normal order workflow. |
| `tbl_normal_order_product` | `order_items` | Pending | Normal order line items. |
| `tbl_subscribe_order` | `subscription_orders` | Pending | Subscription order workflow. |
| `tbl_subscribe_order_product` | `subscription_order_items` | Pending | Subscription line items/schedules. |
| `tbl_notification` | `customer_notifications` | Pending | Customer notification inbox. |
| `tbl_snoti` | `store_notifications` | Pending | Store notification inbox. |
| `tbl_rnoti` | `rider_notifications` | Pending | Rider notification inbox. |
| `tbl_fav` | `favorites` | Pending | Customer favorite stores/products. |
| `tbl_address` | `customer_addresses` | Pending | Customer saved addresses. |
| `payout_setting` | `payout_requests` | Pending | Store payout requests. |
| `tbl_cash` | `cash_collections` | Pending | Cash settlement tracking. |
| `wallet_report` | `wallet_transactions` | Pending | Customer wallet ledger. |
| `tbl_setting` | `settings` | Pending | Global settings and integration keys. |
| `tbl_milk` | `milk_data` or documented obsolete decision | Pending review | Must not be removed until legacy references are reviewed and documented. |

## Completion Rules

- `Implemented` means the Laravel migration, model relationship coverage, factory/seeder path, and tests exist.
- `Pending` means it is required and not yet complete.
- `Merged` means the legacy table's responsibility was intentionally absorbed into another Laravel model and documented.
- `Obsolete` means the table was intentionally removed with a documented reason.
