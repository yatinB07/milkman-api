<?php

namespace App\Support;

class LegacySchemaCoverage
{
    /**
     * @return list<array{legacy: string, destination: string, status: string, notes: string}>
     */
    public static function decisions(): array
    {
        return [
            ['legacy' => 'admin', 'destination' => 'admins', 'status' => 'Schema implemented', 'notes' => 'Foundation identity migration plus legacy username coverage.'],
            ['legacy' => 'tbl_user', 'destination' => 'customers', 'status' => 'Schema implemented', 'notes' => 'Foundation identity migration plus registration date coverage.'],
            ['legacy' => 'service_details', 'destination' => 'stores', 'status' => 'Schema implemented', 'notes' => 'Store profile, zone, bank, charge, media, slogan, and policy columns covered.'],
            ['legacy' => 'tbl_rider', 'destination' => 'riders', 'status' => 'Schema implemented', 'notes' => 'Foundation identity migration plus image coverage.'],
            ['legacy' => 'banner', 'destination' => 'banners', 'status' => 'Schema implemented', 'notes' => 'Marketing/home display.'],
            ['legacy' => 'tbl_category', 'destination' => 'categories', 'status' => 'Schema implemented', 'notes' => 'Product/catalog category.'],
            ['legacy' => 'zones', 'destination' => 'zones', 'status' => 'Schema implemented', 'notes' => 'Delivery zone lookup; coordinates stored as text for portable schema tests.'],
            ['legacy' => 'tbl_mcat', 'destination' => 'store_categories', 'status' => 'Schema implemented', 'notes' => 'Store category mapping.'],
            ['legacy' => 'tbl_product', 'destination' => 'products', 'status' => 'Schema implemented', 'notes' => 'Catalog product.'],
            ['legacy' => 'tbl_product_attribute', 'destination' => 'product_variants', 'status' => 'Schema implemented', 'notes' => 'Product variants/pricing.'],
            ['legacy' => 'tbl_extra', 'destination' => 'product_images', 'status' => 'Schema implemented', 'notes' => 'Product image gallery.'],
            ['legacy' => 'tbl_photo', 'destination' => 'store_gallery_images', 'status' => 'Schema implemented', 'notes' => 'Store gallery.'],
            ['legacy' => 'tbl_delivery', 'destination' => 'delivery_options', 'status' => 'Schema implemented', 'notes' => 'Store delivery options.'],
            ['legacy' => 'tbl_time', 'destination' => 'time_slots', 'status' => 'Schema implemented', 'notes' => 'Store delivery/pickup slots.'],
            ['legacy' => 'tbl_coupon', 'destination' => 'coupons', 'status' => 'Schema implemented', 'notes' => 'Store coupon rules.'],
            ['legacy' => 'tbl_faq', 'destination' => 'faqs', 'status' => 'Schema implemented', 'notes' => 'Store FAQ content.'],
            ['legacy' => 'tbl_page', 'destination' => 'pages', 'status' => 'Schema implemented', 'notes' => 'CMS/static pages.'],
            ['legacy' => 'tbl_payment_list', 'destination' => 'payment_methods', 'status' => 'Schema implemented', 'notes' => 'Payment method catalog.'],
            ['legacy' => 'tbl_normal_order', 'destination' => 'orders', 'status' => 'Schema implemented', 'notes' => 'Normal order workflow.'],
            ['legacy' => 'tbl_normal_order_product', 'destination' => 'order_items', 'status' => 'Schema implemented', 'notes' => 'Normal order line items.'],
            ['legacy' => 'tbl_subscribe_order', 'destination' => 'subscription_orders', 'status' => 'Schema implemented', 'notes' => 'Subscription order workflow.'],
            ['legacy' => 'tbl_subscribe_order_product', 'destination' => 'subscription_order_items', 'status' => 'Schema implemented', 'notes' => 'Subscription line items/schedules.'],
            ['legacy' => 'tbl_notification', 'destination' => 'customer_notifications', 'status' => 'Schema implemented', 'notes' => 'Customer notification inbox.'],
            ['legacy' => 'tbl_snoti', 'destination' => 'store_notifications', 'status' => 'Schema implemented', 'notes' => 'Store notification inbox.'],
            ['legacy' => 'tbl_rnoti', 'destination' => 'rider_notifications', 'status' => 'Schema implemented', 'notes' => 'Rider notification inbox.'],
            ['legacy' => 'tbl_fav', 'destination' => 'favorites', 'status' => 'Schema implemented', 'notes' => 'Customer favorite stores/products.'],
            ['legacy' => 'tbl_address', 'destination' => 'customer_addresses', 'status' => 'Schema implemented', 'notes' => 'Customer saved addresses.'],
            ['legacy' => 'payout_setting', 'destination' => 'payout_requests', 'status' => 'Schema implemented', 'notes' => 'Store payout requests.'],
            ['legacy' => 'tbl_cash', 'destination' => 'cash_collections', 'status' => 'Schema implemented', 'notes' => 'Cash settlement tracking.'],
            ['legacy' => 'wallet_report', 'destination' => 'wallet_transactions', 'status' => 'Schema implemented', 'notes' => 'Customer wallet ledger.'],
            ['legacy' => 'tbl_setting', 'destination' => 'settings', 'status' => 'Schema implemented', 'notes' => 'Global settings and integration keys.'],
            ['legacy' => 'tbl_milk', 'destination' => 'milk_data', 'status' => 'Schema implemented, behavior pending review', 'notes' => 'Stored as raw reference payload until the legacy purpose is fully reviewed.'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function destinationTables(): array
    {
        return array_values(array_map(
            static fn (array $decision): string => $decision['destination'],
            self::decisions(),
        ));
    }
}
