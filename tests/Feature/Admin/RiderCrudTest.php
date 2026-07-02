<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Rider;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RiderCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_riders(): void
    {
        $token = $this->adminTokenWithPermission('riders.manage');
        $store = Store::factory()->create(['title' => 'Milky Way Central']);

        $rider = Rider::factory()->for($store)->create([
            'name' => 'Arjun Rider',
            'email' => 'arjun@example.test',
            'mobile' => '+919999999991',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/riders')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Arjun Rider')
            ->assertJsonPath('data.0.store.title', 'Milky Way Central');

        $this->withToken($token)
            ->getJson("/api/v1/admin/riders/{$rider->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $rider->id)
            ->assertJsonPath('data.email', 'arjun@example.test');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/riders', $this->riderPayload($store, [
                'name' => 'Milan Rider',
                'email' => 'milan@example.test',
            ]))
            ->assertCreated()
            ->assertJsonPath('message', 'Rider created successfully.')
            ->assertJsonPath('data.name', 'Milan Rider')
            ->assertJsonPath('data.store.id', $store->id)
            ->json('data.id');

        $this->assertTrue(Hash::check('secret-password', Rider::findOrFail($createdId)->password));

        $this->withToken($token)
            ->putJson("/api/v1/admin/riders/{$createdId}", [
                'name' => 'Milan Rider Updated',
                'is_active' => false,
                'password' => 'changed-password',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Rider updated successfully.')
            ->assertJsonPath('data.name', 'Milan Rider Updated')
            ->assertJsonPath('data.is_active', false);

        $this->assertTrue(Hash::check('changed-password', Rider::findOrFail($createdId)->password));

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/riders/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Rider deleted successfully.');

        $this->assertSoftDeleted('riders', ['id' => $createdId]);
    }

    public function test_admin_rider_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('riders.manage');
        $store = Store::factory()->create(['title' => 'Milky Way Central']);

        Rider::factory()->for($store)->create(['name' => 'Arjun Rider', 'email' => 'arjun@example.test']);
        Rider::factory()->create(['name' => 'Milan Rider', 'email' => 'milan@example.test']);
        Rider::factory()->create(['name' => 'Surat Courier', 'email' => 'surat@example.test']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/riders?search=rider&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Arjun Rider')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_rider_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('riders.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/riders', [
                'store_id' => 999,
                'name' => '',
                'email' => 'not-an-email',
                'password' => 'short',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id', 'name', 'email', 'password']);
    }

    public function test_admin_rider_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/riders')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_rider_routes_require_rider_management_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/riders')
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');
    }

    /** @param array<string, mixed> $overrides */
    private function riderPayload(Store $store, array $overrides = []): array
    {
        return array_merge([
            'store_id' => $store->id,
            'image_path' => 'images/rider/rider.png',
            'name' => 'Rider One',
            'email' => 'rider@example.test',
            'country_code' => '+91',
            'mobile' => '+919999999990',
            'password' => 'secret-password',
            'is_active' => true,
            'joined_at' => now()->toDateString(),
        ], $overrides);
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
