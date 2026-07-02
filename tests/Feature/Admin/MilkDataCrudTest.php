<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\MilkData;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MilkDataCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_milk_data_reference_payloads(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        $payload = MilkData::factory()->create([
            'data' => '{"source":"legacy","note":"Old tbl_milk payload"}',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/milk-data')
            ->assertOk()
            ->assertJsonPath('data.0.data', '{"source":"legacy","note":"Old tbl_milk payload"}');

        $this->withToken($token)
            ->getJson("/api/v1/admin/milk-data/{$payload->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $payload->id)
            ->assertJsonPath('data.data', '{"source":"legacy","note":"Old tbl_milk payload"}');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/milk-data', [
                'data' => '{"source":"admin","note":"Reference payload"}',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Milk data created successfully.')
            ->assertJsonPath('data.data', '{"source":"admin","note":"Reference payload"}')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/milk-data/{$createdId}", [
                'data' => '{"source":"admin","note":"Updated reference payload"}',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Milk data updated successfully.')
            ->assertJsonPath('data.data', '{"source":"admin","note":"Updated reference payload"}');

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/milk-data/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Milk data deleted successfully.');

        $this->assertSoftDeleted('milk_data', ['id' => $createdId]);
    }

    public function test_admin_milk_data_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        MilkData::factory()->create(['data' => '{"note":"Legacy milk payload"}', 'created_at' => now()->subMinutes(2)]);
        MilkData::factory()->create(['data' => '{"note":"Legacy subscription payload"}', 'created_at' => now()->subMinute()]);
        MilkData::factory()->create(['data' => '{"note":"Settings payload"}', 'created_at' => now()]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/milk-data?search=legacy&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.data', '{"note":"Legacy subscription payload"}')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_milk_data_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        $this->withToken($token)
            ->postJson('/api/v1/admin/milk-data', [
                'data' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['data']);
    }

    public function test_admin_milk_data_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/milk-data')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_milk_data_routes_require_settings_update_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/milk-data')
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
