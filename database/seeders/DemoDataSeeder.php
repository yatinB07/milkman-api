<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Banner;
use App\Models\CashCollection;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerNotification;
use App\Models\DeliveryOption;
use App\Models\Faq;
use App\Models\Favorite;
use App\Models\MilkData;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\PaymentMethod;
use App\Models\PayoutRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Rider;
use App\Models\RiderNotification;
use App\Models\Setting;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreGalleryImage;
use App\Models\StoreNotification;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionOrderItem;
use App\Models\TimeSlot;
use App\Models\WalletTransaction;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        $admin = Admin::query()->firstOrCreate([
            'email' => 'admin@milkman.test',
        ], [
            'name' => 'MilkMan Admin',
            'password' => 'password',
        ]);
        $admin->assignRole('super-admin');
        $this->command?->info('Demo admin ready: admin@milkman.test in admins');

        $store = Store::query()->firstOrCreate([
            'email' => 'store@milkman.test',
        ], [
            'title' => 'Demo Milk Store',
            'password' => 'password',
        ]);
        $store->assignRole('store-owner');
        $this->command?->info('Demo store ready: store@milkman.test in stores');

        $rider = Rider::query()->firstOrCreate([
            'email' => 'rider@milkman.test',
        ], [
            'store_id' => $store->id,
            'name' => 'Demo Rider',
            'password' => 'password',
        ]);
        $rider->assignRole('rider');
        $this->command?->info('Demo rider ready: rider@milkman.test in riders');

        $customer = Customer::query()->firstOrCreate([
            'email' => 'customer@milkman.test',
        ], [
            'name' => 'Demo Customer',
            'password' => 'password',
        ]);
        $customer->assignRole('customer');
        $this->command?->info('Demo customer ready: customer@milkman.test in customers');

        $this->seedBusinessData($store, $rider, $customer);
        $this->command?->info('Demo business dataset ready.');
    }

    private function seedBusinessData(Store $store, Rider $rider, Customer $customer): void
    {
        $zone = Zone::query()->firstOrCreate([
            'alias' => 'demo-zone',
        ], [
            'title' => 'Demo Zone',
            'coordinates' => '[{"lat":23.0225,"lng":72.5714},{"lat":23.0325,"lng":72.5814}]',
            'is_active' => true,
        ]);

        $store->forceFill([
            'zone_id' => $zone->id,
            'image_path' => 'demo/stores/demo-store.png',
            'cover_image_path' => 'demo/stores/demo-store-cover.png',
            'rating' => 4.80,
            'slogan' => 'Fresh milk at your doorstep',
            'slogan_title' => 'Daily Fresh',
            'language_code' => 'en',
            'category_reference' => 'Milk',
            'country_code' => '+91',
            'mobile' => '9999999999',
            'full_address' => 'Demo Dairy Street',
            'pincode' => '380001',
            'landmark' => 'Near Demo Circle',
            'short_description' => 'A demo dairy store for development workflows.',
            'content_description' => 'Seeded store used by local development, tests, and API documentation examples.',
            'latitude' => 23.022505,
            'longitude' => 72.571365,
            'store_charge' => 5,
            'delivery_charge' => 10,
            'minimum_order_amount' => 50,
            'commission_percent' => 8,
            'opens_at' => '06:00:00',
            'closes_at' => '20:00:00',
            'is_pickup_enabled' => true,
            'bank_name' => 'Demo Bank',
            'ifsc_code' => 'DEMO0001234',
            'receipt_name' => 'Demo Milk Store',
            'account_number' => '1234567890',
            'upi_id' => 'demo@upi',
            'cancel_policy' => 'Orders can be cancelled before rider assignment.',
        ])->save();

        Banner::query()->firstOrCreate([
            'image_path' => 'demo/banners/milkman-demo.png',
        ], [
            'title' => 'MilkMan demo banner',
            'is_active' => true,
        ]);

        Category::query()->firstOrCreate([
            'title' => 'Milk',
        ], [
            'image_path' => 'demo/categories/milk.png',
            'cover_path' => 'demo/categories/milk-cover.png',
            'is_active' => true,
        ]);

        $storeCategory = StoreCategory::query()->firstOrCreate([
            'store_id' => $store->id,
            'title' => 'Daily Milk',
        ], [
            'image_path' => 'demo/store-categories/daily-milk.png',
            'is_active' => true,
        ]);

        $product = Product::query()->firstOrCreate([
            'store_id' => $store->id,
            'title' => 'Farm Fresh Cow Milk',
        ], [
            'store_category_id' => $storeCategory->id,
            'image_path' => 'demo/products/cow-milk.png',
            'description' => 'Pasteurized cow milk for daily delivery.',
            'is_active' => true,
        ]);

        ProductVariant::query()->firstOrCreate([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'title' => '1 Litre',
        ], [
            'subscribe_price' => 58,
            'normal_price' => 60,
            'discount' => 2,
            'is_out_of_stock' => false,
            'is_subscription_required' => false,
        ]);

        ProductImage::query()->firstOrCreate([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'image_path' => 'demo/products/cow-milk-gallery.png',
        ], [
            'is_active' => true,
        ]);

        StoreGalleryImage::query()->firstOrCreate([
            'store_id' => $store->id,
            'image_path' => 'demo/stores/gallery.png',
        ], [
            'is_active' => true,
        ]);

        DeliveryOption::query()->firstOrCreate([
            'store_id' => $store->id,
            'title' => 'Morning Delivery',
        ], [
            'delivery_days' => 1,
            'is_active' => true,
        ]);

        TimeSlot::query()->firstOrCreate([
            'store_id' => $store->id,
            'starts_at' => '06:00:00',
            'ends_at' => '09:00:00',
        ], [
            'is_active' => true,
        ]);

        $coupon = Coupon::query()->firstOrCreate([
            'store_id' => $store->id,
            'code' => 'MILK10',
        ], [
            'image_path' => 'demo/coupons/milk10.png',
            'title' => 'Milk 10',
            'subtitle' => 'Demo discount',
            'expires_at' => now()->addMonth()->toDateString(),
            'minimum_amount' => 100,
            'value' => 10,
            'description' => 'Demo coupon for local testing.',
            'is_active' => true,
        ]);

        Faq::query()->firstOrCreate([
            'store_id' => $store->id,
            'question' => 'When is milk delivered?',
        ], [
            'answer' => 'Demo deliveries are scheduled for the morning slot.',
            'is_active' => true,
        ]);

        Page::query()->firstOrCreate([
            'title' => 'About MilkMan',
        ], [
            'description' => 'Demo about page for the migrated Laravel API.',
            'is_active' => true,
        ]);

        $paymentMethod = PaymentMethod::query()->firstOrCreate([
            'title' => 'Cash on Delivery',
        ], [
            'image_path' => 'demo/payments/cod.png',
            'attributes' => ['code' => 'cod'],
            'subtitle' => 'Pay the rider when your order arrives.',
            'is_visible' => true,
            'is_active' => true,
        ]);

        CustomerAddress::query()->firstOrCreate([
            'customer_id' => $customer->id,
            'type' => 'Home',
        ], [
            'address' => 'Demo Customer Home',
            'landmark' => 'Near Demo Circle',
            'rider_instruction' => 'Call before delivery.',
            'latitude' => 23.022505,
            'longitude' => 72.571365,
        ]);

        Favorite::query()->firstOrCreate([
            'customer_id' => $customer->id,
            'store_id' => $store->id,
        ], [
            'zone_id' => $zone->id,
        ]);

        $order = Order::query()->firstOrCreate([
            'transaction_id' => 'DEMO-ORDER-001',
        ], [
            'store_id' => $store->id,
            'customer_id' => $customer->id,
            'ordered_at' => now(),
            'payment_method_id' => $paymentMethod->id,
            'address' => 'Demo Customer Home',
            'landmark' => 'Near Demo Circle',
            'delivery_charge' => 10,
            'coupon_id' => $coupon->id,
            'coupon_amount' => 10,
            'total' => 110,
            'subtotal' => 110,
            'admin_status' => 1,
            'rider_id' => $rider->id,
            'wallet_amount' => 0,
            'customer_name' => 'Demo Customer',
            'customer_mobile' => '8888888888',
            'status' => 'pending',
            'time_slot' => '06:00 AM - 09:00 AM',
            'order_type' => 'Delivery',
            'commission_percent' => 8,
            'store_charge' => 5,
            'internal_status' => 1,
        ]);

        OrderItem::query()->firstOrCreate([
            'order_id' => $order->id,
            'product_title' => 'Farm Fresh Cow Milk',
        ], [
            'quantity' => 2,
            'discount' => 2,
            'image_path' => 'demo/products/cow-milk.png',
            'price' => 60,
            'variant_title' => '1 Litre',
        ]);

        $subscriptionOrder = SubscriptionOrder::query()->firstOrCreate([
            'transaction_id' => 'DEMO-SUB-001',
        ], [
            'store_id' => $store->id,
            'customer_id' => $customer->id,
            'ordered_at' => now(),
            'payment_method_id' => $paymentMethod->id,
            'address' => 'Demo Customer Home',
            'landmark' => 'Near Demo Circle',
            'delivery_charge' => 0,
            'coupon_id' => $coupon->id,
            'coupon_amount' => 10,
            'total' => 570,
            'subtotal' => 580,
            'admin_status' => 1,
            'rider_id' => $rider->id,
            'wallet_amount' => 0,
            'customer_name' => 'Demo Customer',
            'customer_mobile' => '8888888888',
            'status' => 'active',
            'time_slot' => '06:00 AM - 09:00 AM',
            'order_type' => 'Subscription',
            'commission_percent' => 8,
            'store_charge' => 5,
            'internal_status' => 1,
        ]);

        SubscriptionOrderItem::query()->firstOrCreate([
            'subscription_order_id' => $subscriptionOrder->id,
            'product_title' => 'Farm Fresh Cow Milk',
        ], [
            'quantity' => 1,
            'discount' => 2,
            'image_path' => 'demo/products/cow-milk.png',
            'price' => 58,
            'variant_title' => '1 Litre',
            'starts_at' => now()->addDay()->toDateString(),
            'total_deliveries' => 10,
            'total_dates' => '[]',
            'completed_dates' => '[]',
            'selected_days' => '["mon","wed","fri"]',
            'time_slot' => '06:00 AM - 09:00 AM',
        ]);

        CustomerNotification::query()->firstOrCreate([
            'customer_id' => $customer->id,
            'title' => 'Order placed',
        ], [
            'notified_at' => now(),
            'description' => 'Your demo order has been placed.',
        ]);

        StoreNotification::query()->firstOrCreate([
            'store_id' => $store->id,
            'title' => 'New demo order',
        ], [
            'notified_at' => now(),
            'description' => 'A demo order is ready for review.',
        ]);

        RiderNotification::query()->firstOrCreate([
            'rider_id' => $rider->id,
            'title' => 'Demo assignment',
        ], [
            'notified_at' => now(),
            'message' => 'You have a demo delivery assignment.',
        ]);

        PayoutRequest::query()->firstOrCreate([
            'store_id' => $store->id,
            'requested_at' => now()->startOfDay(),
        ], [
            'amount' => 250,
            'status' => 'pending',
            'request_type' => 'bank',
            'account_number' => '1234567890',
            'bank_name' => 'Demo Bank',
            'account_name' => 'Demo Milk Store',
            'ifsc_code' => 'DEMO0001234',
            'upi_id' => 'demo@upi',
        ]);

        CashCollection::query()->firstOrCreate([
            'store_id' => $store->id,
            'collected_at' => now()->startOfDay(),
        ], [
            'amount' => 110,
            'message' => 'Demo cash collected for order DEMO-ORDER-001.',
        ]);

        WalletTransaction::query()->firstOrCreate([
            'customer_id' => $customer->id,
            'message' => 'Demo signup credit',
        ], [
            'type' => 'credit',
            'amount' => 25,
            'transacted_at' => now(),
        ]);

        Setting::query()->updateOrCreate([
            'web_name' => 'MilkMan Demo',
        ], [
            'web_logo_path' => 'demo/settings/logo.png',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'primary_store_id' => $store->id,
            'signup_credit' => 25,
            'referral_credit' => 15,
            'show_dark_mode' => false,
            'sms_type' => 'demo',
        ]);

        MilkData::query()->firstOrCreate([
            'data' => '{"source":"demo","note":"Legacy tbl_milk reference placeholder"}',
        ]);
    }
}
