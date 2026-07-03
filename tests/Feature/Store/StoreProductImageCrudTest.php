<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreProductImageCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_manage_own_product_images(): void
    {
        [$store, $token] = $this->storeToken();
        $product = Product::factory()->create(['store_id' => $store->getKey(), 'title' => 'Morning milk']);
        $image = ProductImage::factory()->create([
            'store_id' => $store->getKey(),
            'product_id' => $product->getKey(),
            'image_path' => 'products/gallery/morning.png',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/product-images')
            ->assertOk()
            ->assertJsonPath('data.0.image_path', 'products/gallery/morning.png');

        $this->withToken($token)
            ->getJson('/api/v1/store/product-images/'.$image->getKey())
            ->assertOk()
            ->assertJsonPath('data.id', $image->getKey());

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/store/product-images', [
                'product_id' => $product->getKey(),
                'image_path' => 'products/gallery/fresh.png',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Product image created successfully.')
            ->assertJsonPath('data.store_id', $store->getKey())
            ->assertJsonPath('data.product_id', $product->getKey())
            ->assertJsonPath('data.image_path', 'products/gallery/fresh.png')
            ->json('data.id');

        $this->withToken($token)
            ->putJson('/api/v1/store/product-images/'.$createdId, [
                'product_id' => $product->getKey(),
                'image_path' => 'products/gallery/updated.png',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Product image updated successfully.')
            ->assertJsonPath('data.image_path', 'products/gallery/updated.png')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson('/api/v1/store/product-images/'.$createdId)
            ->assertOk()
            ->assertJsonPath('message', 'Product image deleted successfully.');

        $this->assertSoftDeleted('product_images', ['id' => $createdId]);
    }

    public function test_store_product_image_list_is_paginated_and_searchable(): void
    {
        [$store, $token] = $this->storeToken();
        $product = Product::factory()->create(['store_id' => $store->getKey(), 'title' => 'Dairy bottle']);
        $otherProduct = Product::factory()->create(['store_id' => Store::factory(), 'title' => 'Other milk']);

        ProductImage::factory()->create([
            'store_id' => $store->getKey(),
            'product_id' => $product->getKey(),
            'image_path' => 'products/gallery/milk-front.png',
        ]);
        ProductImage::factory()->create([
            'store_id' => $store->getKey(),
            'product_id' => $product->getKey(),
            'image_path' => 'products/gallery/milk-side.png',
        ]);
        ProductImage::factory()->create([
            'store_id' => $store->getKey(),
            'product_id' => $product->getKey(),
            'image_path' => 'products/gallery/curd.png',
        ]);
        ProductImage::factory()->create([
            'store_id' => $otherProduct->getAttribute('store_id'),
            'product_id' => $otherProduct->getKey(),
            'image_path' => 'products/gallery/milk-other.png',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/product-images?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.image_path', 'products/gallery/milk-front.png')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_store_cannot_manage_another_stores_product_image_or_product(): void
    {
        [$store, $token] = $this->storeToken();
        $ownProduct = Product::factory()->create(['store_id' => $store->getKey()]);
        $otherProduct = Product::factory()->create(['store_id' => Store::factory()]);
        $otherImage = ProductImage::factory()->create([
            'store_id' => $otherProduct->getAttribute('store_id'),
            'product_id' => $otherProduct->getKey(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/product-images/'.$otherImage->getKey())
            ->assertNotFound()
            ->assertJsonPath('message', 'Product image was not found.');

        $this->withToken($token)
            ->postJson('/api/v1/store/product-images', [
                'product_id' => $otherProduct->getKey(),
                'image_path' => 'products/gallery/wrong-store.png',
                'is_active' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product_id');

        $this->withToken($token)
            ->postJson('/api/v1/store/product-images', [
                'product_id' => $ownProduct->getKey(),
                'image_path' => 'products/gallery/own-store.png',
                'is_active' => true,
            ])
            ->assertCreated();
    }

    public function test_store_product_image_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('products.manage');
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/store/product-images')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    /**
     * @return array{0: Store, 1: string}
     */
    private function storeToken(): array
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $store = Store::factory()->create([
            'email' => 'store@example.test',
            'password' => Hash::make('password'),
        ]);
        $store->assignRole('store-owner');

        $token = $this->postJson('/api/v1/store/auth/login', [
            'email' => 'store@example.test',
            'password' => 'password',
        ])
            ->assertOk()
            ->json('data.token');

        return [$store, $token];
    }
}
