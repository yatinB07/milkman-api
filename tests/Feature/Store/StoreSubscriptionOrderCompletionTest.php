<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreSubscriptionOrderCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_complete_own_self_pickup_subscription_order(): void
    {
        [$store, $token] = $this->storeToken();
        $customer = Customer::factory()->create(['name' => 'Mina Customer']);
        $order = SubscriptionOrder::factory()->create([
            'store_id' => $store->getKey(),
            'customer_id' => $customer->getKey(),
            'customer_name' => 'Mina Customer',
            'order_type' => 'Self Pickup',
            'status' => 'Processing',
            'internal_status' => 3,
            'rider_id' => null,
            'coupon_id' => null,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/store/subscription-orders/'.$order->getKey().'/complete')
            ->assertOk()
            ->assertJsonPath('message', 'Subscription order completed successfully.')
            ->assertJsonPath('data.status', 'Completed')
            ->assertJsonPath('data.internal_status', 10);

        $this->assertDatabaseHas('subscription_orders', [
            'id' => $order->getKey(),
            'status' => 'Completed',
            'internal_status' => 10,
        ]);
        $this->assertDatabaseHas('customer_notifications', [
            'customer_id' => $customer->getKey(),
            'title' => 'Subscription Order Completed!!',
            'description' => 'Mina Customer, Your Subscription Order #'.$order->getKey().' Has Been Completed.',
        ]);
    }

    public function test_store_cannot_complete_non_self_pickup_subscription_order(): void
    {
        [$store, $token] = $this->storeToken();
        $order = SubscriptionOrder::factory()->create([
            'store_id' => $store->getKey(),
            'order_type' => 'Delivery',
            'status' => 'Processing',
            'rider_id' => null,
            'coupon_id' => null,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/store/subscription-orders/'.$order->getKey().'/complete')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Only self pickup orders can be completed by the store.');
    }

    public function test_store_cannot_complete_another_stores_subscription_order(): void
    {
        [, $token] = $this->storeToken();
        $otherOrder = SubscriptionOrder::factory()->create([
            'store_id' => Store::factory(),
            'order_type' => 'Self Pickup',
            'rider_id' => null,
            'coupon_id' => null,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/store/subscription-orders/'.$otherOrder->getKey().'/complete')
            ->assertNotFound()
            ->assertJsonPath('message', 'Subscription order was not found.');
    }

    public function test_store_subscription_order_completion_rejects_other_identity_tokens(): void
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
            ->postJson('/api/v1/store/subscription-orders/1/complete')
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
