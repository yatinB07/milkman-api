<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Rider;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionOrderItem;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SubscriptionOrderCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_subscription_orders(): void
    {
        $token = $this->adminTokenWithPermission('orders.update-status');
        $store = Store::factory()->create(['title' => 'Milky Way Central']);
        $customer = Customer::factory()->create(['name' => 'Aarav Customer']);
        $paymentMethod = PaymentMethod::factory()->create(['title' => 'Wallet']);
        $rider = Rider::factory()->create(['name' => 'Ravi Runner']);

        $order = SubscriptionOrder::factory()
            ->for($store)
            ->for($customer)
            ->for($paymentMethod, 'paymentMethod')
            ->for($rider)
            ->create([
                'transaction_id' => 'SUB-1001',
                'customer_name' => 'Aarav Customer',
                'customer_mobile' => '9999999999',
                'status' => 'active',
                'ordered_at' => now()->addMinute(),
            ]);
        SubscriptionOrderItem::factory()->for($order, 'subscriptionOrder')->create(['product_title' => 'Monthly Milk']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/subscription-orders')
            ->assertOk()
            ->assertJsonPath('data.0.transaction_id', 'SUB-1001')
            ->assertJsonPath('data.0.store.title', 'Milky Way Central')
            ->assertJsonPath('data.0.items.0.product_title', 'Monthly Milk');

        $this->withToken($token)
            ->getJson("/api/v1/admin/subscription-orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.customer.name', 'Aarav Customer');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/subscription-orders', [
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'ordered_at' => now()->toDateTimeString(),
                'payment_method_id' => $paymentMethod->id,
                'address' => '123 Milk Street',
                'landmark' => 'Near Market',
                'delivery_charge' => 0,
                'coupon_amount' => 10,
                'total' => 570,
                'subtotal' => 580,
                'transaction_id' => 'SUB-1002',
                'admin_status' => 1,
                'rider_id' => $rider->id,
                'wallet_amount' => 0,
                'customer_name' => 'Aarav Customer',
                'customer_mobile' => '9999999999',
                'status' => 'active',
                'time_slot' => '06:00 AM - 09:00 AM',
                'order_type' => 'Subscription',
                'is_rated' => false,
                'total_rating' => 0,
                'commission_percent' => 8,
                'store_charge' => 5,
                'internal_status' => 1,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Subscription order created successfully.')
            ->assertJsonPath('data.transaction_id', 'SUB-1002')
            ->assertJsonPath('data.store.id', $store->id)
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/subscription-orders/{$createdId}", [
                'status' => 'completed',
                'admin_note' => 'Completed from admin panel',
                'total_rating' => 5,
                'is_rated' => true,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Subscription order updated successfully.')
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.admin_note', 'Completed from admin panel');

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/subscription-orders/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Subscription order deleted successfully.');

        $this->assertSoftDeleted('subscription_orders', ['id' => $createdId]);
    }

    public function test_admin_subscription_order_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('orders.update-status');
        $store = Store::factory()->create(['title' => 'Milky Way Central']);

        SubscriptionOrder::factory()->for($store)->create([
            'transaction_id' => 'SUB-SEARCH-1',
            'customer_name' => 'Aarav Customer',
            'ordered_at' => now()->addMinutes(2),
        ]);
        SubscriptionOrder::factory()->create([
            'transaction_id' => 'SUB-SEARCH-2',
            'customer_name' => 'Bhavya Customer',
            'ordered_at' => now()->addMinute(),
        ]);
        SubscriptionOrder::factory()->create([
            'transaction_id' => 'OTHER-1',
            'customer_name' => 'Chirag Customer',
            'ordered_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/subscription-orders?search=SUB-SEARCH&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.transaction_id', 'SUB-SEARCH-1')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_subscription_order_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('orders.update-status');

        $this->withToken($token)
            ->postJson('/api/v1/admin/subscription-orders', [
                'store_id' => 999,
                'customer_id' => 999,
                'address' => '',
                'total' => -1,
                'subtotal' => -1,
                'customer_name' => '',
                'customer_mobile' => '',
                'status' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'store_id',
                'customer_id',
                'address',
                'total',
                'subtotal',
                'customer_name',
                'customer_mobile',
                'status',
            ]);
    }

    public function test_admin_subscription_order_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/subscription-orders')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_subscription_order_routes_require_order_update_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/subscription-orders')
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');
    }

    private function adminTokenWithPermission(string $permission): string
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = Admin::factory()->create(['password' => 'secret-password']);
        $admin->givePermissionTo(Permission::findByName($permission, 'sanctum'));

        return $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
