<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Store;
use App\Models\StoreNotification;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StoreNotificationCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_store_notifications(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');
        $store = Store::factory()->create(['title' => 'Milky Way Central']);

        $notification = StoreNotification::factory()->for($store)->create([
            'title' => 'New Order',
            'description' => 'New Order #10 Has Been Received.',
            'notified_at' => now()->addMinute(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/store-notifications')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'New Order')
            ->assertJsonPath('data.0.store.title', 'Milky Way Central');

        $this->withToken($token)
            ->getJson("/api/v1/admin/store-notifications/{$notification->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath('data.description', 'New Order #10 Has Been Received.');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/store-notifications', [
                'store_id' => $store->id,
                'notified_at' => now()->toDateTimeString(),
                'title' => 'Order Completed',
                'description' => 'Order #10 Has Been Completed.',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Store notification created successfully.')
            ->assertJsonPath('data.title', 'Order Completed')
            ->assertJsonPath('data.store.id', $store->id)
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/store-notifications/{$createdId}", [
                'title' => 'Order Completed Updated',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Store notification updated successfully.')
            ->assertJsonPath('data.title', 'Order Completed Updated');

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/store-notifications/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Store notification deleted successfully.');

        $this->assertSoftDeleted('store_notifications', ['id' => $createdId]);
    }

    public function test_admin_store_notification_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');
        $store = Store::factory()->create(['title' => 'Milky Way Central']);

        StoreNotification::factory()->for($store)->create([
            'title' => 'New Order',
            'description' => 'New order has been received.',
            'notified_at' => now()->addMinutes(2),
        ]);
        StoreNotification::factory()->create([
            'title' => 'Order Completed',
            'description' => 'Order completed.',
            'notified_at' => now()->addMinute(),
        ]);
        StoreNotification::factory()->create([
            'title' => 'Payout Requested',
            'description' => 'Payout request received.',
            'notified_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/store-notifications?search=order&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'New Order')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_store_notification_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/store-notifications', [
                'store_id' => 999,
                'notified_at' => 'not-a-date',
                'title' => '',
                'description' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id', 'notified_at', 'title', 'description']);
    }

    public function test_admin_store_notification_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/store-notifications')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_store_notification_routes_require_store_management_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/store-notifications')
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
