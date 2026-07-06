<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Rider;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreRiderCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_manage_own_riders(): void
    {
        [$store, $token] = $this->storeToken();
        $rider = Rider::factory()->create([
            'store_id' => $store->getKey(),
            'name' => 'Asha Rider',
            'email' => 'asha@example.test',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/riders')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Asha Rider');

        $this->withToken($token)
            ->getJson('/api/v1/store/riders/'.$rider->getKey())
            ->assertOk()
            ->assertJsonPath('data.id', $rider->getKey());

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/store/riders', [
                'image_path' => 'images/rider/raj.png',
                'name' => 'Raj Rider',
                'email' => 'raj@example.test',
                'country_code' => '+91',
                'mobile' => '9000000001',
                'password' => 'secret123',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Rider created successfully.')
            ->assertJsonPath('data.store_id', $store->getKey())
            ->assertJsonPath('data.name', 'Raj Rider')
            ->json('data.id');

        $createdRider = Rider::query()->findOrFail($createdId);
        $this->assertTrue(Hash::check('secret123', $createdRider->getAttribute('password')));
        $this->assertTrue($createdRider->hasRole('rider'));

        $this->withToken($token)
            ->putJson('/api/v1/store/riders/'.$createdId, [
                'name' => 'Raj Updated',
                'email' => 'raj.updated@example.test',
                'country_code' => '+91',
                'mobile' => '9000000002',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Rider updated successfully.')
            ->assertJsonPath('data.name', 'Raj Updated')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson('/api/v1/store/riders/'.$createdId)
            ->assertOk()
            ->assertJsonPath('message', 'Rider deleted successfully.');

        $this->assertSoftDeleted('riders', ['id' => $createdId]);
    }

    public function test_store_rider_list_is_paginated_and_searchable(): void
    {
        [$store, $token] = $this->storeToken();

        Rider::factory()->create(['store_id' => $store->getKey(), 'name' => 'Amit Milk Runner']);
        Rider::factory()->create(['store_id' => $store->getKey(), 'name' => 'Bharat Milk Runner']);
        Rider::factory()->create(['store_id' => $store->getKey(), 'name' => 'Curd Runner']);
        Rider::factory()->create(['store_id' => Store::factory(), 'name' => 'Other Milk Runner']);

        $this->withToken($token)
            ->getJson('/api/v1/store/riders?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Amit Milk Runner')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_store_rider_list_covers_legacy_rider_assignment_dropdown(): void
    {
        [$store, $token] = $this->storeToken();

        $rider = Rider::factory()->create([
            'store_id' => $store->getKey(),
            'name' => 'Assignment Rider',
        ]);
        Rider::factory()->create([
            'store_id' => Store::factory(),
            'name' => 'Other Store Rider',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/riders?search=assignment')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $rider->getKey())
            ->assertJsonPath('data.0.name', 'Assignment Rider')
            ->assertJsonPath('data.0.store_id', $store->getKey());
    }

    public function test_store_cannot_manage_another_stores_rider(): void
    {
        [, $token] = $this->storeToken();
        $otherRider = Rider::factory()->create(['store_id' => Store::factory()]);

        $this->withToken($token)
            ->getJson('/api/v1/store/riders/'.$otherRider->getKey())
            ->assertNotFound()
            ->assertJsonPath('message', 'Rider was not found.');
    }

    public function test_store_rider_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('riders.manage');
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/store/riders')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_store_cannot_create_rider_with_duplicate_email(): void
    {
        [$store, $token] = $this->storeToken();
        Rider::factory()->create(['email' => 'duplicate@example.test']);

        $this->withToken($token)
            ->postJson('/api/v1/store/riders', [
                'name' => 'Duplicate Rider',
                'email' => 'duplicate@example.test',
                'password' => 'secret123',
                'is_active' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
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
