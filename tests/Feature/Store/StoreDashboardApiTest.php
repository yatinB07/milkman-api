<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\CashCollection;
use App\Models\Coupon;
use App\Models\DeliveryOption;
use App\Models\Faq;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Rider;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreGalleryImage;
use App\Models\SubscriptionOrder;
use App\Models\TimeSlot;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_view_own_dashboard_metrics(): void
    {
        $token = $this->storeToken();
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();
        $otherStore = Store::factory()->create();

        Product::factory()->count(2)->for($store)->create();
        Product::factory()->for($otherStore)->create();
        ProductVariant::factory()->count(3)->for($store)->create();
        ProductImage::factory()->count(2)->for($store)->create();
        DeliveryOption::factory()->for($store)->create();
        StoreCategory::factory()->for($store)->create();
        Faq::factory()->count(2)->for($store)->create();
        TimeSlot::factory()->for($store)->create();
        Coupon::factory()->for($store)->create();
        Rider::factory()->count(2)->for($store)->create();
        StoreGalleryImage::factory()->for($store)->create();

        Order::factory()->for($store)->create([
            'status' => 'Completed',
            'subtotal' => 200,
            'coupon_amount' => 20,
            'delivery_charge' => 10,
            'commission_percent' => 10,
        ]);
        Order::factory()->for($store)->create(['status' => 'Pending']);
        Order::factory()->for($otherStore)->create(['status' => 'Completed']);
        SubscriptionOrder::factory()->for($store)->create([
            'status' => 'Completed',
            'subtotal' => 300,
            'coupon_amount' => 30,
            'delivery_charge' => 15,
            'commission_percent' => 10,
        ]);
        SubscriptionOrder::factory()->for($store)->create(['status' => 'Active']);
        PayoutRequest::factory()->for($store)->create(['amount' => 50]);
        CashCollection::factory()->for($store)->create(['amount' => 60]);

        $this->withToken($token)
            ->getJson('/api/v1/store/dashboard')
            ->assertOk()
            ->assertJsonPath('data.counts.products', 2)
            ->assertJsonPath('data.counts.product_variants', 3)
            ->assertJsonPath('data.counts.product_images', 2)
            ->assertJsonPath('data.counts.delivery_options', 1)
            ->assertJsonPath('data.counts.store_categories', 1)
            ->assertJsonPath('data.counts.faqs', 2)
            ->assertJsonPath('data.counts.time_slots', 1)
            ->assertJsonPath('data.counts.coupons', 1)
            ->assertJsonPath('data.counts.riders', 2)
            ->assertJsonPath('data.counts.gallery_images', 1)
            ->assertJsonPath('data.counts.normal_orders', 2)
            ->assertJsonPath('data.counts.subscription_orders', 2)
            ->assertJsonPath('data.financials.earning', '367.50')
            ->assertJsonPath('data.financials.payout', '50.00')
            ->assertJsonPath('data.financials.on_hand_amount', '130.00')
            ->assertJsonPath('data.withdraw_limit', '0.00')
            ->assertJsonPath('data.cards.0.title', 'Product')
            ->assertJsonPath('data.cards.0.report_data', 2);
    }

    public function test_store_dashboard_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/store/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    private function storeToken(): string
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $store = Store::factory()->create([
            'email' => 'store@example.test',
            'password' => 'secret-password',
        ]);
        $store->assignRole('store-owner');

        return $this->postJson('/api/v1/store/auth/login', [
            'email' => 'store@example.test',
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
