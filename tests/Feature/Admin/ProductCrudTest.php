<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreCategory;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_products(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');
        [$store, $storeCategory] = $this->storeAndCategory();
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'store_category_id' => $storeCategory->id,
            'title' => 'Cow Milk',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/products')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Cow Milk')
            ->assertJsonPath('data.0.store.id', $store->id)
            ->assertJsonPath('data.0.store_category.id', $storeCategory->id);

        $this->withToken($token)
            ->getJson("/api/v1/admin/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.title', 'Cow Milk');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/products', [
                'store_id' => $store->id,
                'store_category_id' => $storeCategory->id,
                'title' => 'Buffalo Milk',
                'image_path' => 'products/buffalo-milk.png',
                'description' => 'Fresh buffalo milk',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Product created successfully.')
            ->assertJsonPath('data.title', 'Buffalo Milk')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/products/{$createdId}", [
                'title' => 'Fresh Buffalo Milk',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Product updated successfully.')
            ->assertJsonPath('data.title', 'Fresh Buffalo Milk')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/products/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Product deleted successfully.');

        $this->assertSoftDeleted('products', ['id' => $createdId]);
    }

    public function test_admin_product_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');

        Product::factory()->create(['title' => 'Cow Milk']);
        Product::factory()->create(['title' => 'Buffalo Milk']);
        Product::factory()->create(['title' => 'Curd']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/products?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Buffalo Milk')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_product_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/products', [
                'store_id' => null,
                'store_category_id' => null,
                'title' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id', 'store_category_id', 'title']);
    }

    public function test_admin_product_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/products')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_product_routes_require_products_manage_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/products')
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');
    }

    /** @return array{0: Store, 1: StoreCategory} */
    private function storeAndCategory(): array
    {
        $store = Store::factory()->create();
        $storeCategory = StoreCategory::factory()->create(['store_id' => $store->id]);

        return [$store, $storeCategory];
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
