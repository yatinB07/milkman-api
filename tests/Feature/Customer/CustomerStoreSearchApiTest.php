<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Favorite;
use App\Models\Store;
use App\Models\Zone;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerStoreSearchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_search_active_stores_with_pagination_and_category_filter(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $zone = Zone::factory()->create(['title' => 'Ahmedabad Zone']);
        $category = Category::factory()->create(['title' => 'Cow Milk']);

        $favoriteStore = Store::factory()->for($zone)->create([
            'title' => 'Aarav Dairy',
            'category_reference' => 'Cow Milk, Buffalo Milk',
            'is_active' => true,
        ]);
        Favorite::factory()->for($customer)->for($favoriteStore)->for($zone)->create();
        Favorite::factory()->for($favoriteStore)->for($zone)->create();
        Coupon::factory()->for($favoriteStore)->create([
            'title' => 'Fresh Milk Offer',
            'subtitle' => 'Save on morning delivery',
            'is_active' => true,
        ]);

        Store::factory()->for($zone)->create([
            'title' => 'Bhavya Dairy',
            'category_reference' => 'Cow Milk',
            'is_active' => true,
        ]);
        Store::factory()->for($zone)->create([
            'title' => 'Aarav Bakery',
            'category_reference' => 'Bakery',
            'is_active' => true,
        ]);
        Store::factory()->for($zone)->create([
            'title' => 'Aarav Closed Dairy',
            'category_reference' => 'Cow Milk',
            'is_active' => false,
        ]);

        $this->withToken($token)
            ->getJson("/api/v1/customer/stores?latitude=23.0225&longitude=72.5714&search=aarav&category_id={$category->id}&per_page=1")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Aarav Dairy')
            ->assertJsonPath('data.0.is_favorite', true)
            ->assertJsonPath('data.0.total_favorites', 2)
            ->assertJsonPath('data.0.coupon.title', 'Fresh Milk Offer')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_customer_store_search_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/customer/stores?latitude=23.0225&longitude=72.5714')
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
