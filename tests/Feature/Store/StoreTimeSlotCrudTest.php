<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Store;
use App\Models\TimeSlot;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTimeSlotCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_manage_own_time_slots(): void
    {
        $token = $this->storeToken();
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();
        $slot = TimeSlot::factory()->for($store)->create(['starts_at' => '06:00:00', 'ends_at' => '09:00:00']);

        $this->withToken($token)
            ->getJson('/api/v1/store/time-slots')
            ->assertOk()
            ->assertJsonPath('data.0.starts_at', '06:00:00');

        $this->withToken($token)
            ->getJson("/api/v1/store/time-slots/{$slot->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $slot->id)
            ->assertJsonPath('data.ends_at', '09:00:00');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/store/time-slots', [
                'starts_at' => '10:00:00',
                'ends_at' => '12:00:00',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Time slot created successfully.')
            ->assertJsonPath('data.store_id', $store->id)
            ->assertJsonPath('data.starts_at', '10:00:00')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/store/time-slots/{$createdId}", [
                'starts_at' => '12:00:00',
                'ends_at' => '14:00:00',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Time slot updated successfully.')
            ->assertJsonPath('data.starts_at', '12:00:00')
            ->assertJsonPath('data.ends_at', '14:00:00')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/store/time-slots/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Time slot deleted successfully.');

        $this->assertSoftDeleted('time_slots', ['id' => $createdId]);
    }

    public function test_store_time_slot_list_is_paginated_and_searchable(): void
    {
        $token = $this->storeToken();
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();

        TimeSlot::factory()->for($store)->create(['starts_at' => '06:00:00', 'ends_at' => '09:00:00']);
        TimeSlot::factory()->for($store)->create(['starts_at' => '10:00:00', 'ends_at' => '12:00:00']);
        TimeSlot::factory()->for($store)->create(['starts_at' => '18:00:00', 'ends_at' => '20:00:00']);
        TimeSlot::factory()->create(['starts_at' => '06:00:00', 'ends_at' => '09:00:00']);

        $this->withToken($token)
            ->getJson('/api/v1/store/time-slots?search=00:00&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.starts_at', '06:00:00')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 3);
    }

    public function test_store_cannot_view_another_stores_time_slot(): void
    {
        $token = $this->storeToken();
        $slot = TimeSlot::factory()->create();

        $this->withToken($token)
            ->getJson("/api/v1/store/time-slots/{$slot->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Time slot was not found.');
    }

    public function test_store_time_slot_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/store/time-slots')
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
