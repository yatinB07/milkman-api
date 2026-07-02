<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Faq;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreGalleryImage;
use App\Models\SubscriptionOrder;
use App\Models\Zone;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerStoreDetailApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_store_detail(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $reviewer = Customer::factory()->create(['name' => 'Review Customer', 'profile_image_path' => 'customers/review.png']);
        $zone = Zone::factory()->create(['title' => 'Ahmedabad Zone']);
        $store = Store::factory()->for($zone)->create([
            'title' => 'Aarav Dairy',
            'latitude' => 23.0225,
            'longitude' => 72.5714,
            'is_active' => true,
        ]);
        Favorite::factory()->for($customer)->for($store)->for($zone)->create();
        Favorite::factory()->for($store)->for($zone)->create();

        StoreGalleryImage::factory()->for($store)->create(['image_path' => 'stores/gallery/front.png', 'is_active' => true]);
        StoreGalleryImage::factory()->for($store)->create(['image_path' => 'stores/gallery/hidden.png', 'is_active' => false]);
        Faq::factory()->for($store)->create(['question' => 'Do you deliver daily?', 'is_active' => true]);
        Faq::factory()->for($store)->create(['question' => 'Hidden FAQ?', 'is_active' => false]);

        $category = StoreCategory::factory()->for($store)->create(['title' => 'Milk Packs', 'is_active' => true]);
        $product = Product::factory()->for($store)->for($category, 'storeCategory')->create(['title' => 'Cow Milk 1L']);
        ProductVariant::factory()->for($store)->for($product)->create(['title' => '1 Litre', 'normal_price' => 60]);

        Order::factory()->for($store)->for($reviewer, 'customer')->create([
            'status' => 'Completed',
            'is_rated' => true,
            'total_rating' => 5,
            'rating_text' => 'Fresh and on time',
            'reviewed_at' => now()->subDay(),
        ]);
        SubscriptionOrder::factory()->for($store)->for($reviewer, 'customer')->create([
            'status' => 'Completed',
            'is_rated' => true,
            'total_rating' => 4,
            'rating_text' => 'Good subscription',
            'reviewed_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson("/api/v1/customer/stores/{$store->id}?latitude=23.0225&longitude=72.5714")
            ->assertOk()
            ->assertJsonPath('data.title', 'Aarav Dairy')
            ->assertJsonPath('data.is_favorite', true)
            ->assertJsonPath('data.total_favorites', 2)
            ->assertJsonPath('data.distance_km', '0.00')
            ->assertJsonCount(1, 'data.gallery_images')
            ->assertJsonPath('data.gallery_images.0.image_path', 'stores/gallery/front.png')
            ->assertJsonCount(1, 'data.faqs')
            ->assertJsonPath('data.faqs.0.question', 'Do you deliver daily?')
            ->assertJsonPath('data.categories.0.title', 'Milk Packs')
            ->assertJsonPath('data.categories.0.products.0.title', 'Cow Milk 1L')
            ->assertJsonPath('data.categories.0.products.0.variants.0.title', '1 Litre')
            ->assertJsonCount(2, 'data.reviews')
            ->assertJsonPath('data.reviews.0.rating', 4)
            ->assertJsonPath('data.reviews.0.customer.name', 'Review Customer');
    }

    public function test_customer_store_detail_rejects_other_identity_tokens(): void
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
            ->getJson("/api/v1/customer/stores/{$store->id}?latitude=23.0225&longitude=72.5714")
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
