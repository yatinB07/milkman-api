<?php

namespace Tests\Feature\Catalog;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\DeliveryOption;
use App\Models\Faq;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\StoreGalleryImage;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_categories_endpoint_returns_only_active_categories_with_pagination_and_search(): void
    {
        Category::factory()->create(['title' => 'Cow Milk', 'is_active' => true]);
        Category::factory()->create(['title' => 'Buffalo Milk', 'is_active' => true]);
        Category::factory()->create(['title' => 'Curd', 'is_active' => true]);
        Category::factory()->create(['title' => 'Hidden', 'is_active' => false]);

        $this->getJson('/api/v1/public/categories?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Buffalo Milk')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonMissing(['title' => 'Hidden']);
    }

    public function test_public_stores_endpoint_returns_active_stores_and_supports_search_with_pagination(): void
    {
        Store::factory()->create(['title' => 'Fresh Dairy', 'is_active' => true]);
        Store::factory()->create(['title' => 'Daily Dairy', 'is_active' => true]);
        Store::factory()->create(['title' => 'Vegetable Shop', 'is_active' => true]);
        Store::factory()->create(['title' => 'Closed Dairy', 'is_active' => false]);

        $this->getJson('/api/v1/public/stores?search=dairy&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.title', 'Daily Dairy')
            ->assertJsonMissing(['title' => 'Closed Dairy']);
    }

    public function test_public_store_detail_endpoint_returns_catalog_support_data(): void
    {
        $store = Store::factory()->create(['title' => 'Fresh Dairy', 'is_active' => true]);
        StoreGalleryImage::factory()->create(['store_id' => $store->id]);
        DeliveryOption::factory()->create(['store_id' => $store->id, 'title' => 'Morning Delivery']);
        TimeSlot::factory()->create(['store_id' => $store->id, 'starts_at' => '06:00:00', 'ends_at' => '09:00:00']);
        Coupon::factory()->create(['store_id' => $store->id, 'code' => 'MILK10']);
        Faq::factory()->create(['store_id' => $store->id, 'question' => 'When do you deliver?']);

        $this->getJson("/api/v1/public/stores/{$store->id}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Fresh Dairy')
            ->assertJsonPath('data.delivery_options.0.title', 'Morning Delivery')
            ->assertJsonPath('data.time_slots.0.starts_at', '06:00:00')
            ->assertJsonPath('data.coupons.0.code', 'MILK10')
            ->assertJsonPath('data.faqs.0.question', 'When do you deliver?')
            ->assertJsonCount(1, 'data.gallery_images');
    }

    public function test_public_store_products_endpoint_returns_active_products_with_variants_and_images_with_pagination_and_search(): void
    {
        $store = Store::factory()->create(['is_active' => true]);
        Product::factory()->create(['store_id' => $store->id, 'title' => 'Cow Milk', 'is_active' => true]);
        $product = Product::factory()->create(['store_id' => $store->id, 'title' => 'Buffalo Milk', 'is_active' => true]);
        Product::factory()->create(['store_id' => $store->id, 'title' => 'Curd', 'is_active' => true]);
        Product::factory()->create(['store_id' => $store->id, 'title' => 'Hidden Milk', 'is_active' => false]);
        ProductVariant::factory()->create(['store_id' => $store->id, 'product_id' => $product->id, 'title' => '1 Litre']);
        ProductImage::factory()->create(['store_id' => $store->id, 'product_id' => $product->id]);

        $this->getJson("/api/v1/public/stores/{$store->id}/products?search=milk&per_page=1")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.title', 'Buffalo Milk')
            ->assertJsonPath('data.0.variants.0.title', '1 Litre')
            ->assertJsonCount(1, 'data.0.images')
            ->assertJsonMissing(['title' => 'Hidden Milk']);
    }

    public function test_public_catalog_returns_localized_error_for_missing_store(): void
    {
        $this->getJson('/api/v1/public/stores/999/products')
            ->assertNotFound()
            ->assertJsonPath('message', 'Store was not found.');
    }
}
