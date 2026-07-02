<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\PaymentMethod;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPaymentMethodApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_visible_active_payment_methods(): void
    {
        $token = $this->customerToken();

        PaymentMethod::factory()->create([
            'title' => 'Cash on Delivery',
            'subtitle' => 'Pay when milk arrives',
            'is_visible' => true,
            'is_active' => true,
        ]);
        PaymentMethod::factory()->create([
            'title' => 'Wallet',
            'subtitle' => 'Pay using wallet balance',
            'is_visible' => true,
            'is_active' => true,
        ]);
        PaymentMethod::factory()->create([
            'title' => 'Hidden Card',
            'is_visible' => false,
            'is_active' => true,
        ]);
        PaymentMethod::factory()->create([
            'title' => 'Inactive UPI',
            'is_visible' => true,
            'is_active' => false,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/customer/payment-methods?search=pay&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Cash on Delivery')
            ->assertJsonPath('data.0.is_visible', true)
            ->assertJsonPath('data.0.is_active', true)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_customer_payment_method_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/customer/payment-methods')
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
