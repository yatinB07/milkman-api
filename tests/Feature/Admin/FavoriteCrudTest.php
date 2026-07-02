<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Favorite;
use App\Models\Store;
use App\Models\Zone;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FavoriteCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_favorites(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');
        $customer = Customer::factory()->create(['name' => 'Aarav Customer']);
        $zone = Zone::factory()->create(['title' => 'Ahmedabad Central']);
        $store = Store::factory()->for($zone)->create(['title' => 'Milky Way Central']);

        $favorite = Favorite::factory()->for($customer)->for($store)->for($zone)->create();

        $this->withToken($token)
            ->getJson('/api/v1/admin/favorites')
            ->assertOk()
            ->assertJsonPath('data.0.customer.name', 'Aarav Customer')
            ->assertJsonPath('data.0.store.title', 'Milky Way Central')
            ->assertJsonPath('data.0.zone.title', 'Ahmedabad Central');

        $this->withToken($token)
            ->getJson("/api/v1/admin/favorites/{$favorite->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $favorite->id)
            ->assertJsonPath('data.customer_id', $customer->id);

        $newStore = Store::factory()->for($zone)->create(['title' => 'Milky Way West']);

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/favorites', [
                'customer_id' => $customer->id,
                'store_id' => $newStore->id,
                'zone_id' => $zone->id,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Favorite created successfully.')
            ->assertJsonPath('data.store.id', $newStore->id)
            ->json('data.id');

        $newZone = Zone::factory()->create(['title' => 'Ahmedabad West']);

        $this->withToken($token)
            ->putJson("/api/v1/admin/favorites/{$createdId}", [
                'zone_id' => $newZone->id,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Favorite updated successfully.')
            ->assertJsonPath('data.zone.id', $newZone->id);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/favorites/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Favorite deleted successfully.');

        $this->assertSoftDeleted('favorites', ['id' => $createdId]);
    }

    public function test_admin_favorite_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');
        $customer = Customer::factory()->create(['name' => 'Aarav Customer']);
        $zone = Zone::factory()->create(['title' => 'Ahmedabad Central']);

        Favorite::factory()->for($customer)->for(Store::factory()->for($zone)->create(['title' => 'Milky Way Central']))->for($zone)->create();
        Favorite::factory()->for(Store::factory()->create(['title' => 'Milky Way West']))->create();
        Favorite::factory()->for(Store::factory()->create(['title' => 'Surat Fresh']))->create();

        $this->withToken($token)
            ->getJson('/api/v1/admin/favorites?search=milky&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.store.title', 'Milky Way Central')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_favorite_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/favorites', [
                'customer_id' => 999,
                'store_id' => 999,
                'zone_id' => 999,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['customer_id', 'store_id', 'zone_id']);
    }

    public function test_admin_favorite_create_rejects_duplicate_customer_store_pair(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');
        $favorite = Favorite::factory()->create();

        $this->withToken($token)
            ->postJson('/api/v1/admin/favorites', [
                'customer_id' => $favorite->customer_id,
                'store_id' => $favorite->store_id,
                'zone_id' => $favorite->zone_id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id']);
    }

    public function test_admin_favorite_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/favorites')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_favorite_routes_require_user_management_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/favorites')
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
