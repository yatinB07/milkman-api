<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\CustomerNotification;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CustomerNotificationCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_customer_notifications(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');
        $customer = Customer::factory()->create(['name' => 'Aarav Customer']);

        $notification = CustomerNotification::factory()->for($customer)->create([
            'title' => 'Order Received',
            'description' => 'Aarav Customer, Your Order #10 Has Been Received.',
            'notified_at' => now()->addMinute(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/customer-notifications')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Order Received')
            ->assertJsonPath('data.0.customer.name', 'Aarav Customer');

        $this->withToken($token)
            ->getJson("/api/v1/admin/customer-notifications/{$notification->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath('data.description', 'Aarav Customer, Your Order #10 Has Been Received.');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/customer-notifications', [
                'customer_id' => $customer->id,
                'notified_at' => now()->toDateTimeString(),
                'title' => 'Order Completed',
                'description' => 'Aarav Customer, Your Order #10 Has Been Completed.',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Customer notification created successfully.')
            ->assertJsonPath('data.title', 'Order Completed')
            ->assertJsonPath('data.customer.id', $customer->id)
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/customer-notifications/{$createdId}", [
                'title' => 'Order Completed Updated',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Customer notification updated successfully.')
            ->assertJsonPath('data.title', 'Order Completed Updated');

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/customer-notifications/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Customer notification deleted successfully.');

        $this->assertSoftDeleted('customer_notifications', ['id' => $createdId]);
    }

    public function test_admin_customer_notification_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');
        $customer = Customer::factory()->create(['name' => 'Aarav Customer']);

        CustomerNotification::factory()->for($customer)->create([
            'title' => 'Order Received',
            'description' => 'Your order has been received.',
            'notified_at' => now()->addMinutes(2),
        ]);
        CustomerNotification::factory()->create([
            'title' => 'Order Completed',
            'description' => 'Your order has been completed.',
            'notified_at' => now()->addMinute(),
        ]);
        CustomerNotification::factory()->create([
            'title' => 'Wallet Credit',
            'description' => 'Wallet balance added.',
            'notified_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/customer-notifications?search=order&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Order Received')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_customer_notification_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/customer-notifications', [
                'customer_id' => 999,
                'notified_at' => 'not-a-date',
                'title' => '',
                'description' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['customer_id', 'notified_at', 'title', 'description']);
    }

    public function test_admin_customer_notification_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/customer-notifications')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_customer_notification_routes_require_user_management_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/customer-notifications')
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
