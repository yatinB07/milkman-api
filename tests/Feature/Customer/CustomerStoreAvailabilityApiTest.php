<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\DeliveryOption;
use App\Models\Store;
use App\Models\TimeSlot;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerStoreAvailabilityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_store_delivery_options(): void
    {
        $token = $this->customerToken();
        $store = Store::factory()->create(['is_active' => true]);
        $otherStore = Store::factory()->create(['is_active' => true]);

        DeliveryOption::factory()->for($store)->create([
            'title' => 'Morning Delivery',
            'delivery_days' => 1,
            'is_active' => true,
        ]);
        DeliveryOption::factory()->for($store)->create([
            'title' => 'Evening Delivery',
            'delivery_days' => 0,
            'is_active' => true,
        ]);
        DeliveryOption::factory()->for($store)->create([
            'title' => 'Hidden Delivery',
            'is_active' => false,
        ]);
        DeliveryOption::factory()->for($otherStore)->create(['title' => 'Morning Delivery']);

        $this->withToken($token)
            ->getJson("/api/v1/customer/stores/{$store->id}/delivery-options?search=delivery&per_page=1")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Evening Delivery')
            ->assertJsonPath('data.0.is_active', true)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_customer_can_list_store_time_slots(): void
    {
        $token = $this->customerToken();
        $store = Store::factory()->create(['is_active' => true]);
        $otherStore = Store::factory()->create(['is_active' => true]);

        TimeSlot::factory()->for($store)->create([
            'starts_at' => '06:00:00',
            'ends_at' => '09:00:00',
            'is_active' => true,
        ]);
        TimeSlot::factory()->for($store)->create([
            'starts_at' => '18:00:00',
            'ends_at' => '21:00:00',
            'is_active' => true,
        ]);
        TimeSlot::factory()->for($store)->create([
            'starts_at' => '22:00:00',
            'ends_at' => '23:00:00',
            'is_active' => false,
        ]);
        TimeSlot::factory()->for($otherStore)->create(['starts_at' => '06:00:00']);

        $this->withToken($token)
            ->getJson("/api/v1/customer/stores/{$store->id}/time-slots?search=06&per_page=1")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.starts_at', '06:00:00')
            ->assertJsonPath('data.0.ends_at', '09:00:00')
            ->assertJsonPath('data.0.is_active', true)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_customer_store_availability_routes_reject_other_identity_tokens(): void
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
            ->getJson("/api/v1/customer/stores/{$store->id}/delivery-options")
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
