<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\DeliveryOption;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreDeliveryOptionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_manage_own_delivery_options(): void
    {
        $token = $this->storeToken();
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();
        $option = DeliveryOption::factory()->for($store)->create(['title' => 'Morning Delivery']);

        $this->withToken($token)
            ->getJson('/api/v1/store/delivery-options')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Morning Delivery');

        $this->withToken($token)
            ->getJson("/api/v1/store/delivery-options/{$option->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $option->id)
            ->assertJsonPath('data.title', 'Morning Delivery');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/store/delivery-options', [
                'title' => 'Evening Delivery',
                'delivery_days' => 2,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Delivery option created successfully.')
            ->assertJsonPath('data.store_id', $store->id)
            ->assertJsonPath('data.title', 'Evening Delivery')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/store/delivery-options/{$createdId}", [
                'title' => 'Late Evening Delivery',
                'delivery_days' => 3,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Delivery option updated successfully.')
            ->assertJsonPath('data.title', 'Late Evening Delivery')
            ->assertJsonPath('data.delivery_days', 3)
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/store/delivery-options/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Delivery option deleted successfully.');

        $this->assertSoftDeleted('delivery_options', ['id' => $createdId]);
    }

    public function test_store_delivery_option_list_is_paginated_and_searchable(): void
    {
        $token = $this->storeToken();
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();

        DeliveryOption::factory()->for($store)->create(['title' => 'Morning Delivery']);
        DeliveryOption::factory()->for($store)->create(['title' => 'Evening Delivery']);
        DeliveryOption::factory()->for($store)->create(['title' => 'Pickup']);
        DeliveryOption::factory()->create(['title' => 'Night Delivery']);

        $this->withToken($token)
            ->getJson('/api/v1/store/delivery-options?search=delivery&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Evening Delivery')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_store_cannot_view_another_stores_delivery_option(): void
    {
        $token = $this->storeToken();
        $option = DeliveryOption::factory()->create();

        $this->withToken($token)
            ->getJson("/api/v1/store/delivery-options/{$option->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Delivery option was not found.');
    }

    public function test_store_delivery_option_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/store/delivery-options')
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
