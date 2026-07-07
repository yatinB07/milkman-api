<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Zone;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ZoneCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_zones(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        $zone = Zone::factory()->create([
            'title' => 'Ahmedabad Central',
            'coordinates' => 'POLYGON((23.01 72.51,23.02 72.52,23.03 72.51,23.01 72.51))',
            'alias' => '(23.01,72.51),(23.02,72.52),(23.03,72.51)',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/zones')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Ahmedabad Central')
            ->assertJsonPath('data.0.alias', '(23.01,72.51),(23.02,72.52),(23.03,72.51)');

        $this->withToken($token)
            ->getJson("/api/v1/admin/zones/{$zone->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $zone->id)
            ->assertJsonPath('data.title', 'Ahmedabad Central');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/zones', [
                'title' => 'Ahmedabad West',
                'coordinates' => 'POLYGON((23.04 72.50,23.05 72.51,23.06 72.50,23.04 72.50))',
                'alias' => '(23.04,72.50),(23.05,72.51),(23.06,72.50)',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Zone created successfully.')
            ->assertJsonPath('data.title', 'Ahmedabad West')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/zones/{$createdId}", [
                'title' => 'Ahmedabad West Updated',
                'alias' => '(23.04,72.50),(23.05,72.51),(23.06,72.50)',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Zone updated successfully.')
            ->assertJsonPath('data.title', 'Ahmedabad West Updated')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/zones/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Zone deleted successfully.');

        $this->assertSoftDeleted('zones', ['id' => $createdId]);
    }

    public function test_admin_zone_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        Zone::factory()->create(['title' => 'Ahmedabad Central', 'alias' => 'central']);
        Zone::factory()->create(['title' => 'Ahmedabad West', 'alias' => 'west']);
        Zone::factory()->create(['title' => 'Surat East', 'alias' => 'east']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/zones?search=ahmedabad&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Ahmedabad Central')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_zone_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        $this->withToken($token)
            ->postJson('/api/v1/admin/zones', [
                'title' => '',
                'coordinates' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'coordinates']);
    }

    public function test_admin_zone_create_requires_at_least_three_coordinate_points(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        $this->withToken($token)
            ->postJson('/api/v1/admin/zones', [
                'title' => 'Ahmedabad Thin Zone',
                'coordinates' => '(23.04,72.50),(23.05,72.51)',
                'is_active' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['coordinates'])
            ->assertJsonPath('errors.coordinates.0', 'The coordinates field must contain at least 3 coordinate points.');
    }

    public function test_admin_zone_update_rejects_too_few_coordinate_points(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');
        $zone = Zone::factory()->create();

        $this->withToken($token)
            ->putJson("/api/v1/admin/zones/{$zone->id}", [
                'coordinates' => 'POLYGON((23.04 72.50,23.05 72.51))',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['coordinates'])
            ->assertJsonPath('errors.coordinates.0', 'The coordinates field must contain at least 3 coordinate points.');
    }

    public function test_admin_zone_create_validates_active_status_values(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        $this->withToken($token)
            ->postJson('/api/v1/admin/zones', [
                'title' => 'Ahmedabad West',
                'coordinates' => '(23.04,72.50),(23.05,72.51),(23.06,72.50)',
                'is_active' => 'definitely',
                'status' => 'published',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['is_active', 'status']);
    }

    public function test_admin_zone_update_accepts_legacy_status_alias(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');
        $zone = Zone::factory()->create(['is_active' => true]);

        $this->withToken($token)
            ->putJson("/api/v1/admin/zones/{$zone->id}", [
                'status' => 0,
            ])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);
    }

    public function test_admin_zone_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/zones')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_zone_routes_require_settings_update_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/zones')
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
