<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\PaymentMethod;
use App\Models\Rider;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionOrderItem;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreSubscriptionOrderReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_view_own_current_subscription_order_history_and_detail(): void
    {
        [$store, $token] = $this->storeToken();
        $rider = Rider::factory()->create(['store_id' => $store->getKey(), 'name' => 'Asha Rider']);
        $paymentMethod = PaymentMethod::factory()->create(['title' => 'Wallet']);
        $order = SubscriptionOrder::factory()->create([
            'store_id' => $store->getKey(),
            'payment_method_id' => $paymentMethod->getKey(),
            'rider_id' => $rider->getKey(),
            'customer_name' => 'Mina Customer',
            'customer_mobile' => '919900000001',
            'status' => 'Active',
            'total' => 580,
            'ordered_at' => now()->subMinute(),
        ]);
        SubscriptionOrderItem::factory()->create([
            'subscription_order_id' => $order->getKey(),
            'product_title' => 'Fresh Milk',
            'variant_title' => '1 Litre',
            'quantity' => 2,
            'price' => 60,
            'total_dates' => '2026-07-04,2026-07-05',
            'completed_dates' => '2026-07-04',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/subscription-orders?status=current')
            ->assertOk()
            ->assertJsonPath('data.0.id', $order->getKey())
            ->assertJsonPath('data.0.rider.name', 'Asha Rider')
            ->assertJsonPath('data.0.customer_name', 'Mina Customer');

        $this->withToken($token)
            ->getJson('/api/v1/store/subscription-orders/'.$order->getKey())
            ->assertOk()
            ->assertJsonPath('data.id', $order->getKey())
            ->assertJsonPath('data.payment_method.title', 'Wallet')
            ->assertJsonPath('data.items.0.product_title', 'Fresh Milk')
            ->assertJsonPath('data.items.0.schedule.0.is_complete', true)
            ->assertJsonPath('data.items.0.schedule.1.is_complete', false);
    }

    public function test_store_subscription_order_list_is_paginated_searchable_and_status_filtered(): void
    {
        [$store, $token] = $this->storeToken();

        SubscriptionOrder::factory()->create([
            'store_id' => $store->getKey(),
            'customer_name' => 'Milk Current',
            'status' => 'Active',
            'ordered_at' => now()->subMinutes(2),
        ]);
        SubscriptionOrder::factory()->create([
            'store_id' => $store->getKey(),
            'customer_name' => 'Milk Processing',
            'status' => 'Processing',
            'ordered_at' => now()->subMinute(),
        ]);
        SubscriptionOrder::factory()->create([
            'store_id' => $store->getKey(),
            'customer_name' => 'Milk Completed',
            'status' => 'Completed',
            'ordered_at' => now(),
        ]);
        SubscriptionOrder::factory()->create([
            'store_id' => Store::factory(),
            'customer_name' => 'Milk Other Store',
            'status' => 'Active',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/subscription-orders?status=current&search=milk&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.customer_name', 'Milk Processing')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);

        $this->withToken($token)
            ->getJson('/api/v1/store/subscription-orders?status=past&search=milk&per_page=10')
            ->assertOk()
            ->assertJsonPath('data.0.customer_name', 'Milk Completed')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_store_cannot_view_another_stores_subscription_order(): void
    {
        [, $token] = $this->storeToken();
        $otherOrder = SubscriptionOrder::factory()->create(['store_id' => Store::factory()]);

        $this->withToken($token)
            ->getJson('/api/v1/store/subscription-orders/'.$otherOrder->getKey())
            ->assertNotFound()
            ->assertJsonPath('message', 'Subscription order was not found.');
    }

    public function test_store_subscription_order_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('orders.view');
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/store/subscription-orders')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    /**
     * @return array{0: Store, 1: string}
     */
    private function storeToken(): array
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $store = Store::factory()->create([
            'email' => 'store@example.test',
            'password' => Hash::make('password'),
        ]);
        $store->assignRole('store-owner');

        $token = $this->postJson('/api/v1/store/auth/login', [
            'email' => 'store@example.test',
            'password' => 'password',
        ])
            ->assertOk()
            ->json('data.token');

        return [$store, $token];
    }
}
