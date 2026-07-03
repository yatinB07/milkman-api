<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\StoreCategory;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_search_store_products_with_pagination(): void
    {
        $token = $this->customerToken();
        $store = Store::factory()->create(['is_active' => true]);
        $category = StoreCategory::factory()->for($store)->create(['title' => 'Milk Packs']);

        $cowMilk = Product::factory()->for($store)->for($category, 'storeCategory')->create([
            'title' => 'Cow Milk 1L',
            'is_active' => true,
        ]);
        ProductVariant::factory()->for($store)->for($cowMilk)->create(['title' => '1 Litre']);

        $cowCurd = Product::factory()->for($store)->for($category, 'storeCategory')->create([
            'title' => 'Cow Curd',
            'is_active' => true,
        ]);
        ProductVariant::factory()->for($store)->for($cowCurd)->create(['title' => '500 Gram']);

        $inactiveProduct = Product::factory()->for($store)->for($category, 'storeCategory')->create([
            'title' => 'Cow Butter',
            'is_active' => false,
        ]);
        ProductVariant::factory()->for($store)->for($inactiveProduct)->create();

        Product::factory()->for($store)->for($category, 'storeCategory')->create([
            'title' => 'Cow Cheese Without Variant',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson("/api/v1/customer/stores/{$store->id}/products?search=cow&per_page=1")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Cow Curd')
            ->assertJsonPath('data.0.store_category.title', 'Milk Packs')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_customer_can_view_product_detail(): void
    {
        $token = $this->customerToken();
        $store = Store::factory()->create(['is_active' => true]);
        $category = StoreCategory::factory()->for($store)->create(['title' => 'Milk Packs']);
        $product = Product::factory()->for($store)->for($category, 'storeCategory')->create([
            'title' => 'Cow Milk 1L',
            'image_path' => 'products/cow-milk.png',
            'description' => 'Fresh cow milk',
            'is_active' => true,
        ]);
        ProductVariant::factory()->for($store)->for($product)->create([
            'title' => '1 Litre',
            'normal_price' => 60,
            'subscribe_price' => 55,
            'is_out_of_stock' => false,
        ]);
        ProductImage::factory()->for($store)->for($product)->create([
            'image_path' => 'products/gallery/cow-milk-side.png',
            'is_active' => true,
        ]);
        ProductImage::factory()->for($store)->for($product)->create([
            'image_path' => 'products/gallery/hidden.png',
            'is_active' => false,
        ]);

        $this->withToken($token)
            ->getJson("/api/v1/customer/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Cow Milk 1L')
            ->assertJsonPath('data.description', 'Fresh cow milk')
            ->assertJsonPath('data.store.id', $store->id)
            ->assertJsonPath('data.store_category.title', 'Milk Packs')
            ->assertJsonCount(1, 'data.variants')
            ->assertJsonPath('data.variants.0.title', '1 Litre')
            ->assertJsonCount(2, 'data.images')
            ->assertJsonPath('data.images.0.image_path', 'products/cow-milk.png')
            ->assertJsonPath('data.images.1.image_path', 'products/gallery/cow-milk-side.png');
    }

    public function test_customer_product_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');
        $store = Store::factory()->create();

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson("/api/v1/customer/stores/{$store->id}/products")
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    private function customerToken(): string
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create([
            'email' => 'customer@example.test',
            'password' => 'secret-password',
        ]);
        $customer->assignRole('customer');

        return $this->postJson('/api/v1/customer/auth/login', [
            'email' => 'customer@example.test',
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
