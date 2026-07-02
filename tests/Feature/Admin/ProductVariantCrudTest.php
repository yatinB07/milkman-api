<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\StoreCategory;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductVariantCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_product_variants(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');
        [$store, $product] = $this->storeAndProduct();
        $variant = ProductVariant::factory()->create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'title' => '1 Litre',
            'normal_price' => 60,
            'subscribe_price' => 58,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/product-variants')
            ->assertOk()
            ->assertJsonPath('data.0.title', '1 Litre')
            ->assertJsonPath('data.0.product.id', $product->id)
            ->assertJsonPath('data.0.store.id', $store->id);

        $this->withToken($token)
            ->getJson("/api/v1/admin/product-variants/{$variant->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $variant->id)
            ->assertJsonPath('data.title', '1 Litre');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/product-variants', [
                'store_id' => $store->id,
                'product_id' => $product->id,
                'title' => '500 ml',
                'normal_price' => 35,
                'subscribe_price' => 32,
                'discount' => 3,
                'is_out_of_stock' => false,
                'is_subscription_required' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Product variant created successfully.')
            ->assertJsonPath('data.title', '500 ml')
            ->assertJsonPath('data.is_subscription_required', true)
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/product-variants/{$createdId}", [
                'title' => 'Half Litre',
                'is_out_of_stock' => true,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Product variant updated successfully.')
            ->assertJsonPath('data.title', 'Half Litre')
            ->assertJsonPath('data.is_out_of_stock', true);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/product-variants/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Product variant deleted successfully.');

        $this->assertSoftDeleted('product_variants', ['id' => $createdId]);
    }

    public function test_admin_product_variant_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');

        ProductVariant::factory()->create(['title' => '500 ml']);
        ProductVariant::factory()->create(['title' => '1 Litre']);
        ProductVariant::factory()->create(['title' => '2 Litres']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/product-variants?search=litre&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', '1 Litre')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_product_variant_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/product-variants', [
                'store_id' => null,
                'product_id' => null,
                'title' => '',
                'normal_price' => -1,
                'subscribe_price' => -1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id', 'product_id', 'title', 'normal_price', 'subscribe_price']);
    }

    public function test_admin_product_variant_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/product-variants')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_product_variant_routes_require_products_manage_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/product-variants')
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
