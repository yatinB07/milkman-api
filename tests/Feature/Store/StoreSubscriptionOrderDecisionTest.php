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

class StoreSubscriptionOrderDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_accept_own_subscription_order(): void
    {
        [$store, $token] = $this->storeToken();
        $customer = Customer::factory()->create(['name' => 'Mina Customer']);
        $order = SubscriptionOrder::factory()->create([
            'store_id' => $store->getKey(),
            'customer_id' => $customer->getKey(),
            'customer_name' => 'Mina Customer',
            'status' => 'Pending',
            'admin_status' => 0,
            'internal_status' => 0,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/store/subscription-orders/'.$order->getKey().'/decision', [
                'decision' => 'accepted',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Subscription order decision updated successfully.')
            ->assertJsonPath('data.status', 'Processing')
            ->assertJsonPath('data.admin_status', 1)
            ->assertJsonPath('data.internal_status', 1);

        $this->assertDatabaseHas('subscription_orders', [
            'id' => $order->getKey(),
            'status' => 'Processing',
            'admin_status' => 1,
            'internal_status' => 1,
            'rejection_comment' => null,
        ]);
        $this->assertDatabaseHas('customer_notifications', [
            'customer_id' => $customer->getKey(),
            'title' => 'Subscription Order Confirmed.',
            'description' => 'Mina Customer, your subscription order #'.$order->getKey().' has been confirmed.',
        ]);
    }

    public function test_store_can_reject_own_subscription_order_with_comment(): void
    {
        [$store, $token] = $this->storeToken();
        $customer = Customer::factory()->create(['name' => 'Mina Customer']);
        $order = SubscriptionOrder::factory()->create([
            'store_id' => $store->getKey(),
            'customer_id' => $customer->getKey(),
            'customer_name' => 'Mina Customer',
            'status' => 'Pending',
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/store/subscription-orders/'.$order->getKey().'/decision', [
                'decision' => 'rejected',
                'rejection_comment' => 'Subscription cannot be fulfilled.',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Subscription order decision updated successfully.')
            ->assertJsonPath('data.status', 'Cancelled')
            ->assertJsonPath('data.admin_status', 2)
            ->assertJsonPath('data.internal_status', 2)
            ->assertJsonPath('data.rejection_comment', 'Subscription cannot be fulfilled.');

        $this->assertDatabaseHas('customer_notifications', [
            'customer_id' => $customer->getKey(),
            'title' => 'Subscription cannot be fulfilled.',
            'description' => 'Mina Customer, your subscription order #'.$order->getKey().' has been rejected.',
        ]);
    }

    public function test_store_must_send_rejection_comment_when_rejecting_subscription_order(): void
    {
        [$store, $token] = $this->storeToken();
        $order = SubscriptionOrder::factory()->create(['store_id' => $store->getKey()]);

        $this->withToken($token)
            ->postJson('/api/v1/store/subscription-orders/'.$order->getKey().'/decision', [
                'decision' => 'rejected',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rejection_comment');
    }

    public function test_store_cannot_decide_another_stores_subscription_order(): void
    {
        [, $token] = $this->storeToken();
        $otherOrder = SubscriptionOrder::factory()->create(['store_id' => Store::factory()]);

        $this->withToken($token)
            ->postJson('/api/v1/store/subscription-orders/'.$otherOrder->getKey().'/decision', [
                'decision' => 'accepted',
            ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Subscription order was not found.');
    }

    public function test_store_subscription_order_decision_rejects_other_identity_tokens(): void
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
            ->postJson('/api/v1/store/subscription-orders/1/decision', [
                'decision' => 'accepted',
            ])
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
