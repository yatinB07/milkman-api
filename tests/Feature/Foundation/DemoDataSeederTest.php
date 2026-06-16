<?php

namespace Tests\Feature\Foundation;

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
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_seeder_creates_secure_role_backed_identities(): void
    {
        $this->seed(DemoDataSeeder::class);
        $this->seed(DemoDataSeeder::class);

        $admin = Admin::query()->where('email', 'admin@milkman.test')->firstOrFail();
        $store = Store::query()->where('email', 'store@milkman.test')->firstOrFail();
        $rider = Rider::query()->where('email', 'rider@milkman.test')->firstOrFail();
        $customer = Customer::query()->where('email', 'customer@milkman.test')->firstOrFail();

        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertNotSame('password', $admin->password);
        $this->assertTrue($admin->hasRole('super-admin'));
        $this->assertTrue($store->hasRole('store-owner'));
        $this->assertTrue($rider->hasRole('rider'));
        $this->assertTrue($customer->hasRole('customer'));
        $this->assertTrue($rider->store()->is($store));
        $this->assertSame(1, Admin::query()->where('email', 'admin@milkman.test')->count());
        $this->assertSame(1, Store::query()->where('email', 'store@milkman.test')->count());
        $this->assertSame(1, Rider::query()->where('email', 'rider@milkman.test')->count());
        $this->assertSame(1, Customer::query()->where('email', 'customer@milkman.test')->count());
    }

    public function test_demo_data_seeder_creates_a_complete_idempotent_business_dataset(): void
    {
        $this->seed(DemoDataSeeder::class);
        $this->seed(DemoDataSeeder::class);

        $store = Store::query()->where('email', 'store@milkman.test')->firstOrFail();
        $customer = Customer::query()->where('email', 'customer@milkman.test')->firstOrFail();
        $rider = Rider::query()->where('email', 'rider@milkman.test')->firstOrFail();
        $order = Order::query()->where('transaction_id', 'DEMO-ORDER-001')->firstOrFail();
        $subscriptionOrder = SubscriptionOrder::query()->where('transaction_id', 'DEMO-SUB-001')->firstOrFail();

        $this->assertSame(1, Banner::query()->count());
        $this->assertSame(1, Category::query()->where('title', 'Milk')->count());
        $this->assertSame(1, Zone::query()->where('alias', 'demo-zone')->count());
        $this->assertSame(1, StoreCategory::query()->whereBelongsTo($store)->where('title', 'Daily Milk')->count());
        $this->assertSame(1, Product::query()->whereBelongsTo($store)->where('title', 'Farm Fresh Cow Milk')->count());
        $this->assertSame(1, ProductVariant::query()->whereBelongsTo($store)->where('title', '1 Litre')->count());
        $this->assertSame(1, ProductImage::query()->whereBelongsTo($store)->count());
        $this->assertSame(1, StoreGalleryImage::query()->whereBelongsTo($store)->count());
        $this->assertSame(1, DeliveryOption::query()->whereBelongsTo($store)->where('title', 'Morning Delivery')->count());
        $this->assertSame(1, TimeSlot::query()->whereBelongsTo($store)->count());
        $this->assertSame(1, Coupon::query()->whereBelongsTo($store)->where('code', 'MILK10')->count());
        $this->assertSame(1, Faq::query()->whereBelongsTo($store)->count());
        $this->assertSame(1, Page::query()->where('title', 'About MilkMan')->count());
        $this->assertSame(1, PaymentMethod::query()->where('title', 'Cash on Delivery')->count());
        $this->assertSame(1, CustomerAddress::query()->whereBelongsTo($customer)->count());
        $this->assertSame(1, Favorite::query()->whereBelongsTo($customer)->whereBelongsTo($store)->count());
        $this->assertSame(1, OrderItem::query()->whereBelongsTo($order)->count());
        $this->assertSame(1, SubscriptionOrderItem::query()->whereBelongsTo($subscriptionOrder, 'subscriptionOrder')->count());
        $this->assertSame(1, CustomerNotification::query()->whereBelongsTo($customer)->count());
        $this->assertSame(1, StoreNotification::query()->whereBelongsTo($store)->count());
        $this->assertSame(1, RiderNotification::query()->whereBelongsTo($rider)->count());
        $this->assertSame(1, PayoutRequest::query()->whereBelongsTo($store)->count());
        $this->assertSame(1, CashCollection::query()->whereBelongsTo($store)->count());
        $this->assertSame(1, WalletTransaction::query()->whereBelongsTo($customer)->count());
        $this->assertSame(1, Setting::query()->where('web_name', 'MilkMan Demo')->count());
        $this->assertSame(1, MilkData::query()->count());
        $this->assertTrue($order->store()->is($store));
        $this->assertTrue($order->customer()->is($customer));
        $this->assertTrue($order->rider()->is($rider));
        $this->assertTrue($subscriptionOrder->store()->is($store));
    }

    public function test_seed_demo_data_command_runs_the_demo_seeder(): void
    {
        $this->artisan('milkman:seed-demo-data')
            ->expectsOutputToContain('Demo data seeded successfully.')
            ->assertSuccessful();

        $this->assertSame(1, Admin::query()->where('email', 'admin@milkman.test')->count());
        $this->assertSame(1, Order::query()->where('transaction_id', 'DEMO-ORDER-001')->count());
    }
}
