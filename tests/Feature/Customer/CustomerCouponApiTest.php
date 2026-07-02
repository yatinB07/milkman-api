<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCouponApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_active_store_coupons(): void
    {
        $token = $this->customerToken();
        $store = Store::factory()->create(['title' => 'Fresh Milk Store']);
        $otherStore = Store::factory()->create();

        Coupon::factory()->for($store)->create([
            'title' => 'Milk Discount',
            'code' => 'MILK10',
            'expires_at' => now()->addDay(),
            'is_active' => true,
            'created_at' => now()->subMinutes(2),
        ]);
        Coupon::factory()->for($store)->create([
            'title' => 'Curd Discount',
            'code' => 'CURD10',
            'expires_at' => now()->addDay(),
            'is_active' => true,
            'created_at' => now()->subMinute(),
        ]);
        Coupon::factory()->for($store)->create([
            'title' => 'Expired Discount',
            'code' => 'OLD10',
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);
        Coupon::factory()->for($store)->create([
            'title' => 'Inactive Discount',
            'code' => 'OFF10',
            'expires_at' => now()->addDay(),
            'is_active' => false,
        ]);
        Coupon::factory()->for($otherStore)->create([
            'title' => 'Other Milk Discount',
            'code' => 'OTHER10',
            'expires_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson("/api/v1/customer/stores/{$store->id}/coupons?search=discount&per_page=1")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Curd Discount')
            ->assertJsonPath('data.0.store.id', $store->id)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_customer_can_check_active_coupon(): void
    {
        $token = $this->customerToken();
        $coupon = Coupon::factory()->create([
            'title' => 'Milk Discount',
            'code' => 'MILK10',
            'expires_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/customer/coupons/check', [
                'coupon_id' => $coupon->id,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Coupon applied successfully.')
            ->assertJsonPath('data.id', $coupon->id)
            ->assertJsonPath('data.code', 'MILK10');
    }

    public function test_customer_coupon_check_rejects_inactive_or_expired_coupon(): void
    {
        $token = $this->customerToken();
        $coupon = Coupon::factory()->create([
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/customer/coupons/check', [
                'coupon_id' => $coupon->id,
            ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Coupon was not found.');
    }

    public function test_customer_coupon_check_validates_payload(): void
    {
        $token = $this->customerToken();

        $this->withToken($token)
            ->postJson('/api/v1/customer/coupons/check', [
                'coupon_id' => 999,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['coupon_id']);
    }

    public function test_customer_coupon_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');
        $store = Store::factory()->create();

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson("/api/v1/customer/stores/{$store->id}/coupons")
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
