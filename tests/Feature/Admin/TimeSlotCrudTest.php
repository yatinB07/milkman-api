<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Store;
use App\Models\TimeSlot;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TimeSlotCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_time_slots(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');
        $store = Store::factory()->create(['title' => 'Fresh Dairy']);
        $slot = TimeSlot::factory()->create([
            'store_id' => $store->id,
            'starts_at' => '06:00:00',
            'ends_at' => '09:00:00',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/time-slots')
            ->assertOk()
            ->assertJsonPath('data.0.starts_at', '06:00:00')
            ->assertJsonPath('data.0.ends_at', '09:00:00')
            ->assertJsonPath('data.0.store.id', $store->id);

        $this->withToken($token)
            ->getJson("/api/v1/admin/time-slots/{$slot->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $slot->id)
            ->assertJsonPath('data.starts_at', '06:00:00');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/time-slots', [
                'store_id' => $store->id,
                'starts_at' => '10:00:00',
                'ends_at' => '12:00:00',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Time slot created successfully.')
            ->assertJsonPath('data.starts_at', '10:00:00')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/time-slots/{$createdId}", [
                'starts_at' => '13:00:00',
                'ends_at' => '15:00:00',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Time slot updated successfully.')
            ->assertJsonPath('data.starts_at', '13:00:00')
            ->assertJsonPath('data.ends_at', '15:00:00')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/time-slots/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Time slot deleted successfully.');

        $this->assertSoftDeleted('time_slots', ['id' => $createdId]);
    }

    public function test_admin_time_slot_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');

        $freshStore = Store::factory()->create(['title' => 'Fresh Dairy']);
        $bakeryStore = Store::factory()->create(['title' => 'Bakery']);

        TimeSlot::factory()->create(['store_id' => $freshStore->id, 'starts_at' => '06:00:00', 'ends_at' => '09:00:00']);
        TimeSlot::factory()->create(['store_id' => $freshStore->id, 'starts_at' => '10:00:00', 'ends_at' => '12:00:00']);
        TimeSlot::factory()->create(['store_id' => $bakeryStore->id, 'starts_at' => '18:00:00', 'ends_at' => '20:00:00']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/time-slots?search=fresh&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.store.title', 'Fresh Dairy')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_time_slot_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/time-slots', [
                'store_id' => null,
                'starts_at' => '',
                'ends_at' => 'not-a-time',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id', 'starts_at', 'ends_at']);
    }

    public function test_admin_time_slot_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/time-slots')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_time_slot_routes_require_stores_manage_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/time-slots')
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
