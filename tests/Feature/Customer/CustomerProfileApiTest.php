<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Setting;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_profile_and_referral_summary(): void
    {
        $token = $this->customerToken();
        Setting::factory()->create([
            'signup_credit' => 25,
            'referral_credit' => 15,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/customer/profile')
            ->assertOk()
            ->assertJsonPath('data.name', 'Customer Demo')
            ->assertJsonPath('data.email', 'customer@example.test')
            ->assertJsonPath('data.referral_code', 'REF123')
            ->assertJsonPath('referral.signup_credit', '25.00')
            ->assertJsonPath('referral.referral_credit', '15.00');
    }

    public function test_customer_can_update_profile_name_and_password(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();

        $this->withToken($token)
            ->putJson('/api/v1/customer/profile', [
                'name' => 'Updated Customer',
                'password' => 'new-secret-password',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Profile updated successfully.')
            ->assertJsonPath('data.name', 'Updated Customer')
            ->assertJsonPath('data.email', 'customer@example.test');

        $customer->refresh();

        $this->assertSame('Updated Customer', $customer->name);
        $this->assertTrue(Hash::check('new-secret-password', $customer->password));
    }

    public function test_customer_profile_update_validates_payload(): void
    {
        $token = $this->customerToken();

        $this->withToken($token)
            ->putJson('/api/v1/customer/profile', [
                'name' => '',
                'password' => 'short',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'password']);
    }

    public function test_customer_profile_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/customer/profile')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    private function customerToken(): string
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create([
            'name' => 'Customer Demo',
            'email' => 'customer@example.test',
            'password' => 'secret-password',
            'referral_code' => 'REF123',
        ]);
        $customer->assignRole('customer');

        return $this->postJson('/api/v1/customer/auth/login', [
            'email' => 'customer@example.test',
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
