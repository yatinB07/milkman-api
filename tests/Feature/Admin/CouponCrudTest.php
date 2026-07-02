<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CouponCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_coupons(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');
        $store = Store::factory()->create(['title' => 'Fresh Dairy']);
        $coupon = Coupon::factory()->create([
            'store_id' => $store->id,
            'title' => 'Milk Saver',
            'code' => 'MILK10',
            'minimum_amount' => 100,
            'value' => 10,
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/coupons')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Milk Saver')
            ->assertJsonPath('data.0.code', 'MILK10')
            ->assertJsonPath('data.0.store.id', $store->id);

        $this->withToken($token)
            ->getJson("/api/v1/admin/coupons/{$coupon->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $coupon->id)
            ->assertJsonPath('data.code', 'MILK10');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/coupons', [
                'store_id' => $store->id,
                'image_path' => 'coupons/summer.png',
                'title' => 'Summer Milk',
                'code' => 'SUMMER20',
                'subtitle' => 'Save on fresh milk',
                'expires_at' => now()->addWeek()->toDateString(),
                'minimum_amount' => 150,
                'value' => 20,
                'description' => 'Summer campaign coupon.',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Coupon created successfully.')
            ->assertJsonPath('data.code', 'SUMMER20')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/coupons/{$createdId}", [
                'title' => 'Summer Milk Plus',
                'code' => 'SUMMER25',
                'minimum_amount' => 200,
                'value' => 25,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Coupon updated successfully.')
            ->assertJsonPath('data.title', 'Summer Milk Plus')
            ->assertJsonPath('data.code', 'SUMMER25')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/coupons/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Coupon deleted successfully.');

        $this->assertSoftDeleted('coupons', ['id' => $createdId]);
    }

    public function test_admin_coupon_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');

        $freshStore = Store::factory()->create(['title' => 'Fresh Dairy']);
        $bakeryStore = Store::factory()->create(['title' => 'Bakery']);

        Coupon::factory()->create(['store_id' => $freshStore->id, 'title' => 'Milk Saver', 'code' => 'MILK10']);
        Coupon::factory()->create(['store_id' => $freshStore->id, 'title' => 'Milk Bonus', 'code' => 'MILK20']);
        Coupon::factory()->create(['store_id' => $bakeryStore->id, 'title' => 'Bread Saver', 'code' => 'BREAD10']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/coupons?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.store.title', 'Fresh Dairy')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_coupon_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/coupons', [
                'store_id' => null,
                'title' => '',
                'code' => '',
                'expires_at' => 'not-a-date',
                'minimum_amount' => -1,
                'value' => -1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id', 'title', 'code', 'expires_at', 'minimum_amount', 'value']);
    }

    public function test_admin_coupon_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/coupons')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_coupon_routes_require_stores_manage_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/coupons')
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');
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
