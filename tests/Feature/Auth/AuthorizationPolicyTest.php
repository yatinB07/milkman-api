<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_identity_can_check_a_granted_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/auth/permissions/settings.update')
            ->assertOk()
            ->assertJsonPath('data.allowed', true)
            ->assertJsonPath('data.permission', 'settings.update');
    }

    public function test_authenticated_identity_cannot_check_a_missing_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/customer/auth/permissions/settings.update')
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');
    }

    public function test_store_policy_allows_store_owner_to_update_only_their_store(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $store = Store::factory()->create();
        $otherStore = Store::factory()->create();
        $admin = Admin::factory()->create();

        $store->assignRole('store-owner');
        $admin->assignRole('admin');

        $this->assertTrue(Gate::forUser($store)->allows('update', $store));
        $this->assertFalse(Gate::forUser($store)->allows('update', $otherStore));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $store));
    }

    public function test_order_policy_enforces_customer_and_store_ownership(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $store = Store::factory()->create();
        $otherStore = Store::factory()->create();
        $customer = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $order = Order::factory()->create([
            'store_id' => $store->id,
            'customer_id' => $customer->id,
        ]);

        $store->assignRole('store-owner');
        $otherStore->assignRole('store-owner');
        $customer->assignRole('customer');
        $otherCustomer->assignRole('customer');

        $this->assertTrue(Gate::forUser($store)->allows('view', $order));
        $this->assertFalse(Gate::forUser($otherStore)->allows('view', $order));
        $this->assertTrue(Gate::forUser($customer)->allows('view', $order));
        $this->assertFalse(Gate::forUser($otherCustomer)->allows('view', $order));
        $this->assertTrue(Gate::forUser($store)->allows('updateStatus', $order));
        $this->assertFalse(Gate::forUser($customer)->allows('updateStatus', $order));
    }
}
