<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Store;
use App\Models\Zone;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StoreCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_stores(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');
        $zone = Zone::factory()->create(['title' => 'Ahmedabad Central']);

        $store = Store::factory()->for($zone)->create([
            'title' => 'Milky Way Central',
            'email' => 'central@example.test',
            'mobile' => '+919999999991',
            'full_address' => 'CG Road, Ahmedabad',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/stores')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Milky Way Central')
            ->assertJsonPath('data.0.zone.title', 'Ahmedabad Central');

        $this->withToken($token)
            ->getJson("/api/v1/admin/stores/{$store->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $store->id)
            ->assertJsonPath('data.email', 'central@example.test');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/stores', $this->storePayload($zone, [
                'title' => 'Milky Way West',
                'email' => 'west@example.test',
            ]))
            ->assertCreated()
            ->assertJsonPath('message', 'Store created successfully.')
            ->assertJsonPath('data.title', 'Milky Way West')
            ->assertJsonPath('data.zone.id', $zone->id)
            ->json('data.id');

        $this->assertTrue(Hash::check('secret-password', Store::findOrFail($createdId)->password));

        $this->withToken($token)
            ->putJson("/api/v1/admin/stores/{$createdId}", [
                'title' => 'Milky Way West Updated',
                'is_active' => false,
                'minimum_order_amount' => 25,
                'password' => 'changed-password',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Store updated successfully.')
            ->assertJsonPath('data.title', 'Milky Way West Updated')
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.minimum_order_amount', '25.00');

        $this->assertTrue(Hash::check('changed-password', Store::findOrFail($createdId)->password));

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/stores/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Store deleted successfully.');

        $this->assertSoftDeleted('stores', ['id' => $createdId]);
    }

    public function test_admin_store_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');
        $zone = Zone::factory()->create(['title' => 'Ahmedabad Central']);

        Store::factory()->for($zone)->create(['title' => 'Milky Way Central', 'email' => 'central@example.test']);
        Store::factory()->create(['title' => 'Milky Way West', 'email' => 'west@example.test']);
        Store::factory()->create(['title' => 'Surat Fresh', 'email' => 'surat@example.test']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/stores?search=milky&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Milky Way Central')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_store_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/stores', [
                'title' => '',
                'email' => 'not-an-email',
                'password' => 'short',
                'zone_id' => 999,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'email', 'password', 'zone_id']);
    }

    public function test_admin_store_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/stores')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_store_routes_require_store_management_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/stores')
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');
    }

    /** @param array<string, mixed> $overrides */
    private function storePayload(Zone $zone, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Milky Way Store',
            'zone_id' => $zone->id,
            'image_path' => 'images/store/logo.png',
            'cover_image_path' => 'images/store/cover.png',
            'rating' => 4.5,
            'slogan' => 'Fresh every day',
            'slogan_title' => 'Daily milk',
            'language_code' => 'en',
            'category_reference' => '1,2',
            'email' => 'store@example.test',
            'password' => 'secret-password',
            'country_code' => '+91',
            'mobile' => '+919999999990',
            'full_address' => 'Main Road, Ahmedabad',
            'pincode' => '380001',
            'landmark' => 'Near market',
            'short_description' => 'milk,curd,ghee',
            'content_description' => 'Fresh milk store',
            'latitude' => 23.022505,
            'longitude' => 72.571365,
            'store_charge' => 5,
            'delivery_charge' => 10,
            'minimum_order_amount' => 20,
            'commission_percent' => 7.5,
            'opens_at' => '08:00:00',
            'closes_at' => '20:00:00',
            'is_pickup_enabled' => true,
            'is_active' => true,
            'registration_status' => 1,
            'charge_type' => 2,
            'unit_kilometers' => 3,
            'unit_price' => 15,
            'additional_price' => 4,
            'bank_name' => 'Milk Bank',
            'ifsc_code' => 'MILK0001',
            'receipt_name' => 'Milky Way',
            'account_number' => '1234567890',
            'paypal_id' => 'paypal@example.test',
            'upi_id' => 'milk@upi',
            'cancel_policy' => 'Cancel before dispatch.',
        ], $overrides);
    }

    private function adminTokenWithPermission(string $permission): string
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = Admin::factory()->create(['password' => 'secret-password']);
        $admin->givePermissionTo(Permission::findByName($permission, 'sanctum'));

        return $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
