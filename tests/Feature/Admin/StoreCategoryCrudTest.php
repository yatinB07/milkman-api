<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Store;
use App\Models\StoreCategory;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StoreCategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_store_categories(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');
        $store = Store::factory()->create();
        $category = StoreCategory::factory()->create([
            'store_id' => $store->id,
            'title' => 'Daily Milk',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/store-categories')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Daily Milk')
            ->assertJsonPath('data.0.store.id', $store->id);

        $this->withToken($token)
            ->getJson("/api/v1/admin/store-categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.title', 'Daily Milk');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/store-categories', [
                'store_id' => $store->id,
                'title' => 'Curd',
                'image_path' => 'store-categories/curd.png',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Store category created successfully.')
            ->assertJsonPath('data.title', 'Curd')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/store-categories/{$createdId}", [
                'title' => 'Fresh Curd',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Store category updated successfully.')
            ->assertJsonPath('data.title', 'Fresh Curd')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/store-categories/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Store category deleted successfully.');

        $this->assertSoftDeleted('store_categories', ['id' => $createdId]);
    }

    public function test_admin_store_category_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');

        StoreCategory::factory()->create(['title' => 'Cow Milk']);
        StoreCategory::factory()->create(['title' => 'Buffalo Milk']);
        StoreCategory::factory()->create(['title' => 'Curd']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/store-categories?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Buffalo Milk')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_store_category_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/store-categories', [
                'store_id' => null,
                'title' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id', 'title']);
    }

    public function test_admin_store_category_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/store-categories')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_store_category_routes_require_products_manage_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/store-categories')
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
