<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Coupon;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCouponCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_manage_own_coupons(): void
    {
        $token = $this->storeToken();
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();
        $coupon = Coupon::factory()->for($store)->create(['title' => 'Milk Saver', 'code' => 'MILK10']);

        $this->withToken($token)
            ->getJson('/api/v1/store/coupons')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Milk Saver');

        $this->withToken($token)
            ->getJson("/api/v1/store/coupons/{$coupon->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $coupon->id)
            ->assertJsonPath('data.code', 'MILK10');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/store/coupons', [
                'title' => 'Fresh Milk',
                'code' => 'FRESH20',
                'subtitle' => 'Save on milk',
                'expires_at' => now()->addMonth()->toDateString(),
                'minimum_amount' => 100,
                'value' => 20,
                'description' => 'Fresh milk coupon',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Coupon created successfully.')
            ->assertJsonPath('data.store_id', $store->id)
            ->assertJsonPath('data.code', 'FRESH20')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/store/coupons/{$createdId}", [
                'title' => 'Fresh Milk Updated',
                'value' => 25,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Coupon updated successfully.')
            ->assertJsonPath('data.title', 'Fresh Milk Updated')
            ->assertJsonPath('data.value', '25.00')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/store/coupons/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Coupon deleted successfully.');

        $this->assertSoftDeleted('coupons', ['id' => $createdId]);
    }

    public function test_store_coupon_list_is_paginated_and_searchable(): void
    {
        $token = $this->storeToken();
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();

        Coupon::factory()->for($store)->create(['title' => 'Morning Milk', 'code' => 'MORNING10']);
        Coupon::factory()->for($store)->create(['title' => 'Evening Milk', 'code' => 'EVENING10']);
        Coupon::factory()->for($store)->create(['title' => 'Curd Deal', 'code' => 'CURD10']);
        Coupon::factory()->create(['title' => 'Other Milk', 'code' => 'OTHER10']);

        $this->withToken($token)
            ->getJson('/api/v1/store/coupons?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Evening Milk')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_store_cannot_view_another_stores_coupon(): void
    {
        $token = $this->storeToken();
        $coupon = Coupon::factory()->create();

        $this->withToken($token)
            ->getJson("/api/v1/store/coupons/{$coupon->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Coupon was not found.');
    }

    public function test_store_coupon_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/store/coupons')
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
