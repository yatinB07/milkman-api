<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Favorite;
use App\Models\Store;
use App\Models\Zone;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerFavoriteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_own_favorites(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $otherCustomer = Customer::factory()->create();
        $zone = Zone::factory()->create(['title' => 'North Zone']);

        $store = Store::factory()->for($zone)->create(['title' => 'Fresh Milk Store']);
        $secondStore = Store::factory()->create(['title' => 'Daily Dairy']);
        $otherStore = Store::factory()->create(['title' => 'Other Fresh Store']);

        Favorite::factory()->for($customer)->for($store)->for($zone)->create(['created_at' => now()->subMinutes(2)]);
        Favorite::factory()->for($customer)->for($secondStore)->create(['created_at' => now()->subMinute()]);
        Favorite::factory()->for($otherCustomer)->for($otherStore)->create();

        $this->withToken($token)
            ->getJson('/api/v1/customer/favorites?search=fresh&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.store.title', 'Fresh Milk Store')
            ->assertJsonPath('data.0.zone.title', 'North Zone')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_customer_can_toggle_favorite_store(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $zone = Zone::factory()->create();
        $store = Store::factory()->for($zone)->create(['title' => 'Toggle Milk Store']);

        $favoriteId = $this->withToken($token)
            ->postJson('/api/v1/customer/favorites/toggle', [
                'store_id' => $store->id,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Store successfully saved in favourite list.')
            ->assertJsonPath('is_favorite', true)
            ->assertJsonPath('data.store.id', $store->id)
            ->json('data.id');

        $this->assertDatabaseHas('favorites', [
            'id' => $favoriteId,
            'customer_id' => $customer->id,
            'store_id' => $store->id,
            'zone_id' => $zone->id,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/customer/favorites/toggle', [
                'store_id' => $store->id,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Store successfully removed from favourite list.')
            ->assertJsonPath('is_favorite', false)
            ->assertJsonPath('data.id', $favoriteId);

        $this->assertSoftDeleted('favorites', ['id' => $favoriteId]);

        $this->withToken($token)
            ->postJson('/api/v1/customer/favorites/toggle', [
                'store_id' => $store->id,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Store successfully saved in favourite list.')
            ->assertJsonPath('is_favorite', true)
            ->assertJsonPath('data.id', $favoriteId);

        $this->assertDatabaseHas('favorites', [
            'id' => $favoriteId,
            'deleted_at' => null,
        ]);
    }

    public function test_customer_favorite_toggle_validates_store(): void
    {
        $token = $this->customerToken();

        $this->withToken($token)
            ->postJson('/api/v1/customer/favorites/toggle', [
                'store_id' => 999,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id']);
    }

    public function test_customer_favorite_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/customer/favorites')
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
