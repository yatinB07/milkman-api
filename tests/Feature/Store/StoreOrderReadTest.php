<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Rider;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreOrderReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_view_own_current_order_history_and_detail(): void
    {
        [$store, $token] = $this->storeToken();
        $rider = Rider::factory()->create(['store_id' => $store->getKey(), 'name' => 'Asha Rider']);
        $paymentMethod = PaymentMethod::factory()->create(['title' => 'Cash']);
        $order = Order::factory()->create([
            'store_id' => $store->getKey(),
            'payment_method_id' => $paymentMethod->getKey(),
            'rider_id' => $rider->getKey(),
            'customer_name' => 'Mina Customer',
            'customer_mobile' => '919900000001',
            'status' => 'Pending',
            'order_type' => 'Delivery',
            'total' => 120,
            'ordered_at' => now()->subMinute(),
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->getKey(),
            'product_title' => 'Fresh Milk',
            'variant_title' => '1 Litre',
            'quantity' => 2,
            'price' => 60,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/orders?status=current')
            ->assertOk()
            ->assertJsonPath('data.0.id', $order->getKey())
            ->assertJsonPath('data.0.rider.name', 'Asha Rider')
            ->assertJsonPath('data.0.customer_name', 'Mina Customer');

        $this->withToken($token)
            ->getJson('/api/v1/store/orders/'.$order->getKey())
            ->assertOk()
            ->assertJsonPath('data.id', $order->getKey())
            ->assertJsonPath('data.payment_method.title', 'Cash')
            ->assertJsonPath('data.items.0.product_title', 'Fresh Milk');
    }

    public function test_store_order_list_is_paginated_searchable_and_status_filtered(): void
    {
        [$store, $token] = $this->storeToken();

        Order::factory()->create([
            'store_id' => $store->getKey(),
            'customer_name' => 'Milk Current',
            'status' => 'Pending',
            'ordered_at' => now()->subMinutes(2),
        ]);
        Order::factory()->create([
            'store_id' => $store->getKey(),
            'customer_name' => 'Milk Processing',
            'status' => 'Processing',
            'ordered_at' => now()->subMinute(),
        ]);
        Order::factory()->create([
            'store_id' => $store->getKey(),
            'customer_name' => 'Milk Completed',
            'status' => 'Completed',
            'ordered_at' => now(),
        ]);
        Order::factory()->create([
            'store_id' => Store::factory(),
            'customer_name' => 'Milk Other Store',
            'status' => 'Pending',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/orders?status=current&search=milk&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.customer_name', 'Milk Processing')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);

        $this->withToken($token)
            ->getJson('/api/v1/store/orders?status=past&search=milk&per_page=10')
            ->assertOk()
            ->assertJsonPath('data.0.customer_name', 'Milk Completed')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_store_cannot_view_another_stores_order(): void
    {
        [, $token] = $this->storeToken();
        $otherOrder = Order::factory()->create(['store_id' => Store::factory()]);

        $this->withToken($token)
            ->getJson('/api/v1/store/orders/'.$otherOrder->getKey())
            ->assertNotFound()
            ->assertJsonPath('message', 'Order was not found.');
    }

    public function test_store_order_routes_reject_other_identity_tokens(): void
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
            ->getJson('/api/v1/store/orders')
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
