<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Rider;
use App\Models\RiderNotification;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RiderNotificationCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_rider_notifications(): void
    {
        $token = $this->adminTokenWithPermission('riders.manage');
        $rider = Rider::factory()->create(['name' => 'Ravi Runner']);

        $notification = RiderNotification::factory()->for($rider)->create([
            'title' => 'Assigned Order',
            'message' => 'Order #10 Has Been Assigned.',
            'notified_at' => now()->addMinute(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/rider-notifications')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Assigned Order')
            ->assertJsonPath('data.0.rider.name', 'Ravi Runner');

        $this->withToken($token)
            ->getJson("/api/v1/admin/rider-notifications/{$notification->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath('data.message', 'Order #10 Has Been Assigned.');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/rider-notifications', [
                'rider_id' => $rider->id,
                'notified_at' => now()->toDateTimeString(),
                'title' => 'Order Completed',
                'message' => 'Order #10 Has Been Completed.',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Rider notification created successfully.')
            ->assertJsonPath('data.title', 'Order Completed')
            ->assertJsonPath('data.rider.id', $rider->id)
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/rider-notifications/{$createdId}", [
                'title' => 'Order Completed Updated',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Rider notification updated successfully.')
            ->assertJsonPath('data.title', 'Order Completed Updated');

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/rider-notifications/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Rider notification deleted successfully.');

        $this->assertSoftDeleted('rider_notifications', ['id' => $createdId]);
    }

    public function test_admin_rider_notification_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('riders.manage');
        $rider = Rider::factory()->create(['name' => 'Ravi Runner']);

        RiderNotification::factory()->for($rider)->create([
            'title' => 'Assigned Order',
            'message' => 'New order has been assigned.',
            'notified_at' => now()->addMinutes(2),
        ]);
        RiderNotification::factory()->create([
            'title' => 'Order Completed',
            'message' => 'Order completed.',
            'notified_at' => now()->addMinute(),
        ]);
        RiderNotification::factory()->create([
            'title' => 'Cash Reminder',
            'message' => 'Cash collection reminder.',
            'notified_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/rider-notifications?search=order&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Assigned Order')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_rider_notification_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('riders.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/rider-notifications', [
                'rider_id' => 999,
                'notified_at' => 'not-a-date',
                'title' => '',
                'message' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rider_id', 'notified_at', 'title', 'message']);
    }

    public function test_admin_rider_notification_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/rider-notifications')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_rider_notification_routes_require_rider_management_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/rider-notifications')
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
