<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreCategory;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_manage_own_products(): void
    {
        $token = $this->storeToken();
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();
        $storeCategory = StoreCategory::factory()->for($store)->create();
        $product = Product::factory()->for($store)->for($storeCategory)->create(['title' => 'Cow Milk']);

        $this->withToken($token)
            ->getJson('/api/v1/store/products')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Cow Milk')
            ->assertJsonPath('data.0.store_category.id', $storeCategory->id);

        $this->withToken($token)
            ->getJson("/api/v1/store/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.title', 'Cow Milk');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/store/products', [
                'store_category_id' => $storeCategory->id,
                'title' => 'Buffalo Milk',
                'image_path' => 'products/buffalo-milk.png',
                'description' => 'Fresh buffalo milk',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Product created successfully.')
            ->assertJsonPath('data.store_id', $store->id)
            ->assertJsonPath('data.title', 'Buffalo Milk')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/store/products/{$createdId}", [
                'title' => 'Fresh Buffalo Milk',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Product updated successfully.')
            ->assertJsonPath('data.title', 'Fresh Buffalo Milk')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/store/products/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Product deleted successfully.');

        $this->assertSoftDeleted('products', ['id' => $createdId]);
    }

    public function test_store_product_list_is_paginated_and_searchable(): void
    {
        $token = $this->storeToken();
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();

        Product::factory()->for($store)->create(['title' => 'Cow Milk']);
        Product::factory()->for($store)->create(['title' => 'Buffalo Milk']);
        Product::factory()->for($store)->create(['title' => 'Curd']);
        Product::factory()->create(['title' => 'Goat Milk']);

        $this->withToken($token)
            ->getJson('/api/v1/store/products?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Buffalo Milk')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_store_cannot_use_another_stores_category_or_product(): void
    {
        $token = $this->storeToken();
        $otherStore = Store::factory()->create();
        $otherCategory = StoreCategory::factory()->for($otherStore)->create();
        $otherProduct = Product::factory()->for($otherStore)->for($otherCategory)->create();

        $this->withToken($token)
            ->postJson('/api/v1/store/products', [
                'store_category_id' => $otherCategory->id,
                'title' => 'Wrong Store Milk',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_category_id']);

        $this->withToken($token)
            ->getJson("/api/v1/store/products/{$otherProduct->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Product was not found.');
    }

    public function test_store_product_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/store/products')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    private function storeToken(): string
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $store = Store::factory()->create([
            'email' => 'store@example.test',
            'password' => 'secret-password',
        ]);
        $store->assignRole('store-owner');

        return $this->postJson('/api/v1/store/auth/login', [
            'email' => 'store@example.test',
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
