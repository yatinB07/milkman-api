<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Store;
use App\Models\TimeSlot;
use App\Models\Zone;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCartDataApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_cart_data_for_store(): void
    {
        $token = $this->customerToken();
        $zone = Zone::factory()->create(['title' => 'Ahmedabad Zone']);
        $store = Store::factory()->for($zone)->create([
            'title' => 'Aarav Dairy',
            'latitude' => 23.0225,
            'longitude' => 72.5714,
            'delivery_charge' => 12.50,
            'store_charge' => 5,
            'minimum_order_amount' => 100,
            'charge_type' => 0,
            'is_pickup_enabled' => true,
            'is_active' => true,
        ]);

        Coupon::factory()->for($store)->create([
            'title' => 'Fresh Milk Offer',
            'expires_at' => now()->addDay(),
            'is_active' => true,
        ]);
        Coupon::factory()->for($store)->create([
            'title' => 'Expired Offer',
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);
        Coupon::factory()->for($store)->create([
            'title' => 'Inactive Offer',
            'expires_at' => now()->addDay(),
            'is_active' => false,
        ]);

        PaymentMethod::factory()->create(['title' => 'Cash', 'is_visible' => true, 'is_active' => true]);
        PaymentMethod::factory()->create(['title' => 'Hidden Card', 'is_visible' => false, 'is_active' => true]);
        PaymentMethod::factory()->create(['title' => 'Inactive UPI', 'is_visible' => true, 'is_active' => false]);

        TimeSlot::factory()->for($store)->create(['starts_at' => '06:00:00', 'ends_at' => '09:00:00', 'is_active' => true]);
        TimeSlot::factory()->for($store)->create(['starts_at' => '18:00:00', 'ends_at' => '21:00:00', 'is_active' => false]);

        $this->withToken($token)
            ->getJson("/api/v1/customer/stores/{$store->id}/cart-data?latitude=23.0225&longitude=72.5714")
            ->assertOk()
            ->assertJsonPath('data.store.id', $store->id)
            ->assertJsonPath('data.store.title', 'Aarav Dairy')
            ->assertJsonPath('data.store.delivery_charge', '12.50')
            ->assertJsonPath('data.store.is_pickup_enabled', true)
            ->assertJsonCount(1, 'data.coupons')
            ->assertJsonPath('data.coupons.0.title', 'Fresh Milk Offer')
            ->assertJsonCount(1, 'data.payment_methods')
            ->assertJsonPath('data.payment_methods.0.title', 'Cash')
            ->assertJsonCount(1, 'data.time_slots')
            ->assertJsonPath('data.time_slots.0.starts_at', '06:00:00');
    }

    public function test_customer_cart_data_rejects_other_identity_tokens(): void
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
            ->getJson("/api/v1/customer/stores/{$store->id}/cart-data?latitude=23.0225&longitude=72.5714")
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
