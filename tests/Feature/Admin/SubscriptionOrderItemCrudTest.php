<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionOrderItem;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SubscriptionOrderItemCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_subscription_order_items(): void
    {
        $token = $this->adminTokenWithPermission('orders.update-status');
        $order = SubscriptionOrder::factory()->create(['transaction_id' => 'SUB-ITEM-1001']);

        $item = SubscriptionOrderItem::factory()->for($order, 'subscriptionOrder')->create([
            'product_title' => 'Monthly Fresh Milk',
            'quantity' => 2,
            'price' => 60,
            'variant_title' => '1 Litre',
            'time_slot' => '06:00 AM - 09:00 AM',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/subscription-order-items')
            ->assertOk()
            ->assertJsonPath('data.0.product_title', 'Monthly Fresh Milk')
            ->assertJsonPath('data.0.subscription_order.transaction_id', 'SUB-ITEM-1001');

        $this->withToken($token)
            ->getJson("/api/v1/admin/subscription-order-items/{$item->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $item->id)
            ->assertJsonPath('data.price', '60.00')
            ->assertJsonPath('data.time_slot', '06:00 AM - 09:00 AM');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/subscription-order-items', [
                'subscription_order_id' => $order->id,
                'quantity' => 3,
                'product_title' => 'Curd Subscription',
                'discount' => 5,
                'image_path' => 'products/curd-subscription.png',
                'price' => 45,
                'variant_title' => '500 Gram',
                'starts_at' => now()->addDay()->toDateString(),
                'total_deliveries' => 12,
                'total_dates' => '2026-07-03,2026-07-05',
                'completed_dates' => '',
                'selected_days' => '1,3,5',
                'time_slot' => '07:00 AM - 10:00 AM',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Subscription order item created successfully.')
            ->assertJsonPath('data.product_title', 'Curd Subscription')
            ->assertJsonPath('data.subscription_order.id', $order->id)
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/subscription-order-items/{$createdId}", [
                'quantity' => 4,
                'price' => 50,
                'completed_dates' => '2026-07-03',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Subscription order item updated successfully.')
            ->assertJsonPath('data.quantity', 4)
            ->assertJsonPath('data.price', '50.00')
            ->assertJsonPath('data.completed_dates', '2026-07-03');

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/subscription-order-items/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Subscription order item deleted successfully.');

        $this->assertSoftDeleted('subscription_order_items', ['id' => $createdId]);
    }

    public function test_admin_subscription_order_item_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('orders.update-status');
        $order = SubscriptionOrder::factory()->create(['transaction_id' => 'SUB-SEARCH-ITEM-1']);

        SubscriptionOrderItem::factory()->for($order, 'subscriptionOrder')->create([
            'product_title' => 'Monthly Milk',
            'created_at' => now()->subMinutes(2),
        ]);
        SubscriptionOrderItem::factory()->create([
            'product_title' => 'Monthly Curd',
            'created_at' => now()->subMinute(),
        ]);
        SubscriptionOrderItem::factory()->create([
            'product_title' => 'Butter',
            'created_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/subscription-order-items?search=monthly&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.product_title', 'Monthly Curd')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_subscription_order_item_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('orders.update-status');

        $this->withToken($token)
            ->postJson('/api/v1/admin/subscription-order-items', [
                'subscription_order_id' => 999,
                'quantity' => 0,
                'product_title' => '',
                'discount' => -1,
                'price' => -1,
                'starts_at' => 'not-a-date',
                'total_deliveries' => -1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'subscription_order_id',
                'quantity',
                'product_title',
                'discount',
                'price',
                'starts_at',
                'total_deliveries',
            ]);
    }

    public function test_admin_subscription_order_item_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/subscription-order-items')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_subscription_order_item_routes_require_order_update_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/subscription-order-items')
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
