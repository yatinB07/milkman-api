<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Store;
use App\Models\StoreNotification;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreNotificationReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_view_own_notifications(): void
    {
        [$store, $token] = $this->storeToken();
        $notification = StoreNotification::factory()->create([
            'store_id' => $store->getKey(),
            'title' => 'Order assigned',
            'description' => 'A new order is ready for review.',
            'notified_at' => now()->subMinute(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Order assigned');

        $this->withToken($token)
            ->getJson('/api/v1/store/notifications/'.$notification->getKey())
            ->assertOk()
            ->assertJsonPath('data.id', $notification->getKey())
            ->assertJsonPath('data.store_id', $store->getKey());
    }

    public function test_store_notification_list_is_paginated_and_searchable(): void
    {
        [$store, $token] = $this->storeToken();

        StoreNotification::factory()->create([
            'store_id' => $store->getKey(),
            'title' => 'Milk order created',
            'notified_at' => now()->subMinutes(2),
        ]);
        StoreNotification::factory()->create([
            'store_id' => $store->getKey(),
            'title' => 'Milk payout approved',
            'notified_at' => now()->subMinute(),
        ]);
        StoreNotification::factory()->create([
            'store_id' => $store->getKey(),
            'title' => 'Curd order created',
            'notified_at' => now(),
        ]);
        StoreNotification::factory()->create([
            'store_id' => Store::factory(),
            'title' => 'Milk order for another store',
            'notified_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/notifications?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Milk payout approved')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_store_cannot_view_another_stores_notification(): void
    {
        [, $token] = $this->storeToken();
        $otherNotification = StoreNotification::factory()->create(['store_id' => Store::factory()]);

        $this->withToken($token)
            ->getJson('/api/v1/store/notifications/'.$otherNotification->getKey())
            ->assertNotFound()
            ->assertJsonPath('message', 'Store notification was not found.');
    }

    public function test_store_notification_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('stores.view');
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/store/notifications')
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
