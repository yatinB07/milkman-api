<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
use App\Models\StoreCategory;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductImageCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_product_images(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');
        [$store, $product] = $this->storeAndProduct();
        $image = ProductImage::factory()->create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'image_path' => 'products/gallery/current.png',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/product-images')
            ->assertOk()
            ->assertJsonPath('data.0.image_path', 'products/gallery/current.png')
            ->assertJsonPath('data.0.product.id', $product->id)
            ->assertJsonPath('data.0.store.id', $store->id);

        $this->withToken($token)
            ->getJson("/api/v1/admin/product-images/{$image->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $image->id)
            ->assertJsonPath('data.image_path', 'products/gallery/current.png');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/product-images', [
                'store_id' => $store->id,
                'product_id' => $product->id,
                'image_path' => 'products/gallery/new.png',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Product image created successfully.')
            ->assertJsonPath('data.image_path', 'products/gallery/new.png')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/product-images/{$createdId}", [
                'image_path' => 'products/gallery/updated.png',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Product image updated successfully.')
            ->assertJsonPath('data.image_path', 'products/gallery/updated.png')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/product-images/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Product image deleted successfully.');

        $this->assertSoftDeleted('product_images', ['id' => $createdId]);
    }

    public function test_admin_product_image_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');

        ProductImage::factory()->create(['image_path' => 'products/gallery/milk-one.png']);
        ProductImage::factory()->create(['image_path' => 'products/gallery/milk-two.png']);
        ProductImage::factory()->create(['image_path' => 'products/gallery/curd.png']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/product-images?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.image_path', 'products/gallery/milk-one.png')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_product_image_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/product-images', [
                'store_id' => null,
                'product_id' => null,
                'image_path' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id', 'product_id', 'image_path']);
    }

    public function test_admin_product_image_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/product-images')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_product_image_routes_require_products_manage_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/product-images')
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');
    }

    /** @return array{0: Store, 1: Product} */
    private function storeAndProduct(): array
    {
        $store = Store::factory()->create();
        $category = StoreCategory::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'store_category_id' => $category->id,
        ]);

        return [$store, $product];
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
