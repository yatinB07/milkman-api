<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Rider;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_orders(): void
    {
        $token = $this->adminTokenWithPermission('orders.update-status');
        $store = Store::factory()->create(['title' => 'Milky Way Central']);
        $customer = Customer::factory()->create(['name' => 'Aarav Customer']);
        $paymentMethod = PaymentMethod::factory()->create(['title' => 'Cash on Delivery']);
        $rider = Rider::factory()->create(['name' => 'Ravi Runner']);

        $order = Order::factory()
            ->for($store)
            ->for($customer)
            ->for($paymentMethod, 'paymentMethod')
            ->for($rider)
            ->create([
                'transaction_id' => 'ORDER-1001',
                'customer_name' => 'Aarav Customer',
                'customer_mobile' => '9999999999',
                'status' => 'pending',
                'ordered_at' => now()->addMinute(),
            ]);
        OrderItem::factory()->for($order)->create(['product_title' => 'Fresh Milk']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/orders')
            ->assertOk()
            ->assertJsonPath('data.0.transaction_id', 'ORDER-1001')
            ->assertJsonPath('data.0.store.title', 'Milky Way Central')
            ->assertJsonPath('data.0.items.0.product_title', 'Fresh Milk');

        $this->withToken($token)
            ->getJson("/api/v1/admin/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.customer.name', 'Aarav Customer');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/orders', [
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'ordered_at' => now()->toDateTimeString(),
                'payment_method_id' => $paymentMethod->id,
                'address' => '123 Milk Street',
                'landmark' => 'Near Market',
                'delivery_charge' => 10,
                'coupon_amount' => 5,
                'total' => 105,
                'subtotal' => 100,
                'transaction_id' => 'ORDER-1002',
                'admin_status' => 1,
                'rider_id' => $rider->id,
                'wallet_amount' => 0,
                'customer_name' => 'Aarav Customer',
                'customer_mobile' => '9999999999',
                'status' => 'pending',
                'time_slot' => '06:00 AM - 09:00 AM',
                'order_type' => 'Delivery',
                'is_rated' => false,
                'total_rating' => 0,
                'commission_percent' => 8,
                'store_charge' => 5,
                'internal_status' => 1,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Order created successfully.')
            ->assertJsonPath('data.transaction_id', 'ORDER-1002')
            ->assertJsonPath('data.store.id', $store->id)
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/orders/{$createdId}", [
                'status' => 'completed',
                'admin_note' => 'Completed from admin panel',
                'total_rating' => 5,
                'is_rated' => true,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Order updated successfully.')
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.admin_note', 'Completed from admin panel');

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/orders/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Order deleted successfully.');

        $this->assertSoftDeleted('orders', ['id' => $createdId]);
    }

    public function test_admin_order_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('orders.update-status');
        $store = Store::factory()->create(['title' => 'Milky Way Central']);

        Order::factory()->for($store)->create([
            'transaction_id' => 'ORDER-SEARCH-1',
            'customer_name' => 'Aarav Customer',
            'ordered_at' => now()->addMinutes(2),
        ]);
        Order::factory()->create([
            'transaction_id' => 'ORDER-SEARCH-2',
            'customer_name' => 'Bhavya Customer',
            'ordered_at' => now()->addMinute(),
        ]);
        Order::factory()->create([
            'transaction_id' => 'OTHER-1',
            'customer_name' => 'Chirag Customer',
            'ordered_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/orders?search=ORDER-SEARCH&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.transaction_id', 'ORDER-SEARCH-1')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_order_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('orders.update-status');

        $this->withToken($token)
            ->postJson('/api/v1/admin/orders', [
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

    public function test_admin_order_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/orders')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_order_routes_require_order_update_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/orders')
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
