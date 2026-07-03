<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Store;
use App\Models\StoreCategory;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_manage_own_categories(): void
    {
        $token = $this->storeToken();
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();
        $category = StoreCategory::factory()->for($store)->create(['title' => 'Daily Milk']);

        $this->withToken($token)
            ->getJson('/api/v1/store/categories')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Daily Milk');

        $this->withToken($token)
            ->getJson("/api/v1/store/categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.title', 'Daily Milk');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/store/categories', [
                'title' => 'Curd',
                'image_path' => 'store-categories/curd.png',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Store category created successfully.')
            ->assertJsonPath('data.store_id', $store->id)
            ->assertJsonPath('data.title', 'Curd')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/store/categories/{$createdId}", [
                'title' => 'Fresh Curd',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Store category updated successfully.')
            ->assertJsonPath('data.title', 'Fresh Curd')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/store/categories/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Store category deleted successfully.');

        $this->assertSoftDeleted('store_categories', ['id' => $createdId]);
    }

    public function test_store_category_list_is_paginated_and_searchable(): void
    {
        $token = $this->storeToken();
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();

        StoreCategory::factory()->for($store)->create(['title' => 'Cow Milk']);
        StoreCategory::factory()->for($store)->create(['title' => 'Buffalo Milk']);
        StoreCategory::factory()->for($store)->create(['title' => 'Curd']);
        StoreCategory::factory()->create(['title' => 'Goat Milk']);

        $this->withToken($token)
            ->getJson('/api/v1/store/categories?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Buffalo Milk')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_store_cannot_view_another_stores_category(): void
    {
        $token = $this->storeToken();
        $category = StoreCategory::factory()->create();

        $this->withToken($token)
            ->getJson("/api/v1/store/categories/{$category->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Store category was not found.');
    }

    public function test_store_category_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/store/categories')
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
