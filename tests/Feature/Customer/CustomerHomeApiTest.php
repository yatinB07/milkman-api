<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Favorite;
use App\Models\Setting;
use App\Models\Store;
use App\Models\Zone;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerHomeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_home_data(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $customer->update(['wallet_balance' => 125.50]);
        Setting::factory()->create(['currency' => 'INR']);

        $zone = Zone::factory()->create(['title' => 'Ahmedabad Zone']);
        Banner::factory()->create(['title' => 'Morning Milk', 'is_active' => true]);
        Banner::factory()->create(['title' => 'Inactive Banner', 'is_active' => false]);
        Category::factory()->create(['title' => 'Cow Milk', 'is_active' => true]);
        Category::factory()->create(['title' => 'Inactive Category', 'is_active' => false]);

        $favoriteStore = Store::factory()->for($zone)->create([
            'title' => 'Favorite Dairy',
            'rating' => 4.75,
            'is_active' => true,
        ]);
        Favorite::factory()->for($customer)->for($favoriteStore)->for($zone)->create();

        Store::factory()->for($zone)->create([
            'title' => 'Spotlight Dairy',
            'rating' => 4.25,
            'is_active' => true,
        ]);
        Store::factory()->for($zone)->create([
            'title' => 'Closed Dairy',
            'is_active' => false,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/customer/home?latitude=23.0225&longitude=72.5714&per_page=2')
            ->assertOk()
            ->assertJsonPath('data.currency', 'INR')
            ->assertJsonPath('data.wallet_balance', '125.50')
            ->assertJsonCount(1, 'data.banners')
            ->assertJsonPath('data.banners.0.title', 'Morning Milk')
            ->assertJsonCount(1, 'data.categories')
            ->assertJsonPath('data.categories.0.title', 'Cow Milk')
            ->assertJsonCount(1, 'data.favorite_stores')
            ->assertJsonPath('data.favorite_stores.0.title', 'Favorite Dairy')
            ->assertJsonCount(2, 'data.spotlight_stores')
            ->assertJsonPath('data.top_stores.0.title', 'Favorite Dairy');
    }

    public function test_customer_home_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/customer/home?latitude=23.0225&longitude=72.5714')
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
