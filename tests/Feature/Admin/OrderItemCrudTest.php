<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderItemCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_order_items(): void
    {
        $token = $this->adminTokenWithPermission('orders.update-status');
        $order = Order::factory()->create(['transaction_id' => 'ORDER-1001']);

        $item = OrderItem::factory()->for($order)->create([
            'product_title' => 'Fresh Milk',
            'quantity' => 2,
            'price' => 60,
            'variant_title' => '1 Litre',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/order-items')
            ->assertOk()
            ->assertJsonPath('data.0.product_title', 'Fresh Milk')
            ->assertJsonPath('data.0.order.transaction_id', 'ORDER-1001');

        $this->withToken($token)
            ->getJson("/api/v1/admin/order-items/{$item->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $item->id)
            ->assertJsonPath('data.price', '60.00');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/order-items', [
                'order_id' => $order->id,
                'quantity' => 3,
                'product_title' => 'Curd Cup',
                'discount' => 5,
                'image_path' => 'products/curd.png',
                'price' => 45,
                'variant_title' => '500 Gram',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Order item created successfully.')
            ->assertJsonPath('data.product_title', 'Curd Cup')
            ->assertJsonPath('data.order.id', $order->id)
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/order-items/{$createdId}", [
                'quantity' => 4,
                'price' => 50,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Order item updated successfully.')
            ->assertJsonPath('data.quantity', 4)
            ->assertJsonPath('data.price', '50.00');

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/order-items/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Order item deleted successfully.');

        $this->assertSoftDeleted('order_items', ['id' => $createdId]);
    }

    public function test_admin_order_item_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('orders.update-status');
        $order = Order::factory()->create(['transaction_id' => 'ORDER-SEARCH-1']);

        OrderItem::factory()->for($order)->create(['product_title' => 'Fresh Milk', 'created_at' => now()->subMinutes(2)]);
        OrderItem::factory()->create(['product_title' => 'Fresh Curd', 'created_at' => now()->subMinute()]);
        OrderItem::factory()->create(['product_title' => 'Butter', 'created_at' => now()]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/order-items?search=fresh&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.product_title', 'Fresh Curd')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_order_item_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('orders.update-status');

        $this->withToken($token)
            ->postJson('/api/v1/admin/order-items', [
                'order_id' => 999,
                'quantity' => 0,
                'product_title' => '',
                'discount' => -1,
                'price' => -1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['order_id', 'quantity', 'product_title', 'discount', 'price']);
    }

    public function test_admin_order_item_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/order-items')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_order_item_routes_require_order_update_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/order-items')
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
