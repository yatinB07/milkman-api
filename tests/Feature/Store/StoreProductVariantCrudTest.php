<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\StoreCategory;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreProductVariantCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_manage_own_product_variants(): void
    {
        $token = $this->storeToken();
        [$store, $product] = $this->storeAndProduct();
        $variant = ProductVariant::factory()->for($store)->for($product)->create(['title' => '1 Litre']);

        $this->withToken($token)
            ->getJson('/api/v1/store/product-variants')
            ->assertOk()
            ->assertJsonPath('data.0.title', '1 Litre')
            ->assertJsonPath('data.0.product.id', $product->id);

        $this->withToken($token)
            ->getJson("/api/v1/store/product-variants/{$variant->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $variant->id)
            ->assertJsonPath('data.title', '1 Litre');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/store/product-variants', [
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
            ->assertJsonPath('data.store_id', $store->id)
            ->assertJsonPath('data.title', '500 ml')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/store/product-variants/{$createdId}", [
                'title' => 'Half Litre',
                'is_out_of_stock' => true,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Product variant updated successfully.')
            ->assertJsonPath('data.title', 'Half Litre')
            ->assertJsonPath('data.is_out_of_stock', true);

        $this->withToken($token)
            ->deleteJson("/api/v1/store/product-variants/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Product variant deleted successfully.');

        $this->assertSoftDeleted('product_variants', ['id' => $createdId]);
    }

    public function test_store_product_variant_list_is_paginated_and_searchable(): void
    {
        $token = $this->storeToken();
        [$store] = $this->storeAndProduct();

        ProductVariant::factory()->for($store)->create(['title' => '500 ml']);
        ProductVariant::factory()->for($store)->create(['title' => '1 Litre']);
        ProductVariant::factory()->for($store)->create(['title' => '2 Litres']);
        ProductVariant::factory()->create(['title' => '3 Litres']);

        $this->withToken($token)
            ->getJson('/api/v1/store/product-variants?search=litre&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', '1 Litre')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_store_cannot_use_another_stores_product_or_variant(): void
    {
        $token = $this->storeToken();
        $otherStore = Store::factory()->create();
        $otherProduct = Product::factory()->for($otherStore)->create();
        $otherVariant = ProductVariant::factory()->for($otherStore)->for($otherProduct)->create();

        $this->withToken($token)
            ->postJson('/api/v1/store/product-variants', [
                'product_id' => $otherProduct->id,
                'title' => 'Wrong Store Variant',
                'normal_price' => 35,
                'subscribe_price' => 32,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['product_id']);

        $this->withToken($token)
            ->getJson("/api/v1/store/product-variants/{$otherVariant->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Product variant was not found.');
    }

    public function test_store_product_variant_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/store/product-variants')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    /** @return array{0: Store, 1: Product} */
    private function storeAndProduct(): array
    {
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();
        $category = StoreCategory::factory()->for($store)->create();
        $product = Product::factory()->for($store)->for($category, 'storeCategory')->create();

        return [$store, $product];
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
