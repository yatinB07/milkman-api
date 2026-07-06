<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreOrderCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_complete_own_self_pickup_order(): void
    {
        [$store, $token] = $this->storeToken();
        $customer = Customer::factory()->create(['name' => 'Mina Customer']);
        $order = Order::factory()->create([
            'store_id' => $store->getKey(),
            'customer_id' => $customer->getKey(),
            'customer_name' => 'Mina Customer',
            'order_type' => 'Self Pickup',
            'status' => 'Processing',
            'internal_status' => 3,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/store/orders/'.$order->getKey().'/complete')
            ->assertOk()
            ->assertJsonPath('message', 'Order completed successfully.')
            ->assertJsonPath('data.status', 'Completed')
            ->assertJsonPath('data.internal_status', 7);

        $this->assertDatabaseHas('orders', [
            'id' => $order->getKey(),
            'status' => 'Completed',
            'internal_status' => 7,
        ]);
        $this->assertDatabaseHas('customer_notifications', [
            'customer_id' => $customer->getKey(),
            'title' => 'Order Completed.',
            'description' => 'Mina Customer, your order #'.$order->getKey().' has been completed.',
        ]);
    }

    public function test_store_cannot_complete_non_self_pickup_order(): void
    {
        [$store, $token] = $this->storeToken();
        $order = Order::factory()->create([
            'store_id' => $store->getKey(),
            'order_type' => 'Delivery',
            'status' => 'Processing',
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/store/orders/'.$order->getKey().'/complete')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Only self pickup orders can be completed by the store.');
    }

    public function test_store_cannot_complete_another_stores_order(): void
    {
        [, $token] = $this->storeToken();
        $otherOrder = Order::factory()->create([
            'store_id' => Store::factory(),
            'order_type' => 'Self Pickup',
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/store/orders/'.$otherOrder->getKey().'/complete')
            ->assertNotFound()
            ->assertJsonPath('message', 'Order was not found.');
    }

    public function test_store_order_completion_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('orders.update-status');
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/store/orders/1/complete')
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
