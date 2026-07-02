<?php

namespace Tests\Feature\Schema;

use App\Support\LegacySchemaCoverage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacySchemaCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_legacy_application_table_has_a_laravel_destination(): void
    {
        foreach (LegacySchemaCoverage::destinationTables() as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected Laravel table [{$table}] for legacy schema coverage.");
        }
    }

    public function test_identity_tables_cover_legacy_profile_columns(): void
    {
        $this->assertTableHasColumns('admins', ['username']);
        $this->assertTableHasColumns('customers', ['profile_image_path', 'registered_at', 'deleted_at']);
        $this->assertTableHasColumns('stores', [
            'zone_id',
            'image_path',
            'cover_image_path',
            'slogan',
            'short_description',
            'bank_name',
            'ifsc_code',
            'account_number',
            'paypal_id',
            'upi_id',
            'cancel_policy',
            'deleted_at',
        ]);
        $this->assertTableHasColumns('riders', ['image_path', 'deleted_at']);
    }

    public function test_catalog_and_content_tables_cover_legacy_columns(): void
    {
        $this->assertTableHasColumns('banners', ['title', 'image_path', 'is_active']);
        $this->assertTableHasColumns('categories', ['title', 'image_path', 'cover_path', 'is_active']);
        $this->assertTableHasColumns('zones', ['title', 'coordinates', 'alias', 'is_active', 'deleted_at']);
        $this->assertTableHasColumns('store_categories', ['store_id', 'title', 'image_path', 'is_active', 'deleted_at']);
        $this->assertTableHasColumns('products', ['store_id', 'store_category_id', 'title', 'image_path', 'description', 'is_active', 'deleted_at']);
        $this->assertTableHasColumns('product_variants', ['store_id', 'product_id', 'subscribe_price', 'normal_price', 'title', 'discount', 'is_out_of_stock', 'is_subscription_required', 'deleted_at']);
        $this->assertTableHasColumns('product_images', ['store_id', 'product_id', 'image_path', 'is_active', 'deleted_at']);
        $this->assertTableHasColumns('store_gallery_images', ['store_id', 'image_path', 'is_active', 'deleted_at']);
        $this->assertTableHasColumns('delivery_options', ['store_id', 'title', 'delivery_days', 'is_active', 'deleted_at']);
        $this->assertTableHasColumns('time_slots', ['store_id', 'starts_at', 'ends_at', 'is_active', 'deleted_at']);
        $this->assertTableHasColumns('coupons', ['store_id', 'image_path', 'title', 'code', 'subtitle', 'expires_at', 'minimum_amount', 'value', 'description', 'is_active', 'deleted_at']);
        $this->assertTableHasColumns('faqs', ['store_id', 'question', 'answer', 'is_active', 'deleted_at']);
        $this->assertTableHasColumns('pages', ['title', 'description', 'is_active', 'deleted_at']);
        $this->assertTableHasColumns('payment_methods', ['title', 'image_path', 'attributes', 'subtitle', 'is_visible', 'is_active', 'deleted_at']);
    }

    public function test_order_subscription_and_finance_tables_cover_legacy_columns(): void
    {
        $orderColumns = [
            'store_id',
            'customer_id',
            'ordered_at',
            'payment_method_id',
            'address',
            'landmark',
            'delivery_charge',
            'coupon_id',
            'coupon_amount',
            'total',
            'subtotal',
            'transaction_id',
            'admin_note',
            'admin_status',
            'rider_id',
            'wallet_amount',
            'customer_name',
            'customer_mobile',
            'status',
            'rejection_comment',
            'time_slot',
            'order_type',
            'is_rated',
            'reviewed_at',
            'total_rating',
            'rating_text',
            'commission_percent',
            'store_charge',
            'internal_status',
            'signature_path',
        ];

        $this->assertTableHasColumns('orders', $orderColumns);
        $this->assertTableHasColumns('subscription_orders', $orderColumns);
        $this->assertTableHasColumns('order_items', ['order_id', 'quantity', 'product_title', 'discount', 'image_path', 'price', 'variant_title']);
        $this->assertTableHasColumns('subscription_order_items', ['subscription_order_id', 'quantity', 'product_title', 'discount', 'image_path', 'price', 'variant_title', 'starts_at', 'total_deliveries', 'total_dates', 'completed_dates', 'selected_days', 'time_slot']);
        $this->assertTableHasColumns('customer_addresses', ['customer_id', 'address', 'landmark', 'rider_instruction', 'type', 'latitude', 'longitude', 'deleted_at']);
        $this->assertTableHasColumns('favorites', ['customer_id', 'store_id', 'zone_id', 'deleted_at']);
        $this->assertTableHasColumns('payout_requests', ['store_id', 'amount', 'status', 'proof_path', 'requested_at', 'request_type', 'account_number', 'bank_name', 'account_name', 'ifsc_code', 'upi_id', 'paypal_id']);
        $this->assertTableHasColumns('cash_collections', ['store_id', 'amount', 'message', 'collected_at']);
        $this->assertTableHasColumns('wallet_transactions', ['customer_id', 'message', 'type', 'amount', 'transacted_at', 'deleted_at']);
    }

    public function test_notification_settings_and_reference_tables_cover_legacy_columns(): void
    {
        $this->assertTableHasColumns('customer_notifications', ['customer_id', 'notified_at', 'title', 'description', 'deleted_at']);
        $this->assertTableHasColumns('store_notifications', ['store_id', 'notified_at', 'title', 'description']);
        $this->assertTableHasColumns('rider_notifications', ['rider_id', 'notified_at', 'title', 'message']);
        $this->assertTableHasColumns('settings', [
            'web_name',
            'web_logo_path',
            'timezone',
            'currency',
            'primary_store_id',
            'customer_onesignal_key',
            'customer_onesignal_hash',
            'delivery_onesignal_key',
            'delivery_onesignal_hash',
            'store_onesignal_key',
            'store_onesignal_hash',
            'signup_credit',
            'referral_credit',
            'show_dark_mode',
            'google_maps_key',
            'sms_type',
            'message_auth_key',
            'otp_template_id',
            'twilio_account_sid',
            'twilio_auth_token',
            'twilio_number',
            'otp_auth_token',
        ]);
        $this->assertTableHasColumns('milk_data', ['data']);
    }

    /**
     * @param  list<string>  $columns
     */
    private function assertTableHasColumns(string $table, array $columns): void
    {
        $this->assertTrue(Schema::hasTable($table), "Expected table [{$table}] to exist.");

        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn($table, $column), "Expected column [{$table}.{$column}] to exist.");
        }
    }
}
