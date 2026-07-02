<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\CashCollection;
use App\Models\Customer;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CashCollectionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_cash_collections(): void
    {
        $token = $this->adminTokenWithPermission('payouts.approve');
        $store = Store::factory()->create(['title' => 'Milky Way Central']);

        $collection = CashCollection::factory()->for($store)->create([
            'amount' => 125,
            'message' => 'Received COD cash',
            'collected_at' => now()->addMinute(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/cash-collections')
            ->assertOk()
            ->assertJsonPath('data.0.amount', '125.00')
            ->assertJsonPath('data.0.store.title', 'Milky Way Central');

        $this->withToken($token)
            ->getJson("/api/v1/admin/cash-collections/{$collection->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $collection->id)
            ->assertJsonPath('data.message', 'Received COD cash');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/cash-collections', [
                'store_id' => $store->id,
                'amount' => 300,
                'message' => 'Collected weekend COD cash',
                'collected_at' => now()->toDateTimeString(),
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Cash collection created successfully.')
            ->assertJsonPath('data.amount', '300.00')
            ->assertJsonPath('data.store.id', $store->id)
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/cash-collections/{$createdId}", [
                'message' => 'Collected updated COD cash',
                'amount' => 325,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Cash collection updated successfully.')
            ->assertJsonPath('data.message', 'Collected updated COD cash')
            ->assertJsonPath('data.amount', '325.00');

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/cash-collections/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Cash collection deleted successfully.');

        $this->assertSoftDeleted('cash_collections', ['id' => $createdId]);
    }

    public function test_admin_cash_collection_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('payouts.approve');
        $store = Store::factory()->create(['title' => 'Milky Way Central']);

        CashCollection::factory()->for($store)->create([
            'message' => 'Received COD cash',
            'collected_at' => now()->addMinutes(2),
        ]);
        CashCollection::factory()->create([
            'message' => 'Weekly deposit',
            'collected_at' => now()->addMinute(),
        ]);
        CashCollection::factory()->create([
            'message' => 'Monthly settlement',
            'collected_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/cash-collections?search=cod&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.message', 'Received COD cash')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_admin_cash_collection_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('payouts.approve');

        $this->withToken($token)
            ->postJson('/api/v1/admin/cash-collections', [
                'store_id' => 999,
                'amount' => -1,
                'message' => '',
                'collected_at' => 'not-a-date',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id', 'amount', 'message', 'collected_at']);
    }

    public function test_admin_cash_collection_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/cash-collections')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_cash_collection_routes_require_payout_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/cash-collections')
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
