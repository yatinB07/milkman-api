<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class CustomerMobileLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_login_with_country_code_mobile_and_password(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create([
            'name' => 'Milk Buyer',
            'email' => 'customer@example.test',
            'country_code' => '+91',
            'mobile' => '9999999999',
            'password' => 'secret-password',
        ]);
        $customer->assignRole('customer');

        $response = $this->postJson('/api/v1/customer/auth/mobile-login', [
            'country_code' => '+91',
            'mobile' => '9999999999',
            'password' => 'secret-password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.user.type', 'customer')
            ->assertJsonPath('data.user.id', $customer->getKey())
            ->assertJsonPath('data.user.email', 'customer@example.test')
            ->assertJsonPath('data.user.roles.0', 'customer')
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'type',
                        'name',
                        'email',
                        'roles',
                        'permissions',
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertSame(1, PersonalAccessToken::query()->count());
    }

    public function test_customer_mobile_login_accepts_email_as_the_legacy_mobile_identifier(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'customer@example.test',
            'country_code' => '+91',
            'mobile' => '9999999999',
            'password' => 'secret-password',
        ]);

        $this->postJson('/api/v1/customer/auth/mobile-login', [
            'country_code' => '+91',
            'mobile' => 'customer@example.test',
            'password' => 'secret-password',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.id', $customer->getKey());
    }

    public function test_customer_mobile_login_rejects_invalid_credentials(): void
    {
        Customer::factory()->create([
            'country_code' => '+91',
            'mobile' => '9999999999',
            'password' => 'secret-password',
        ]);

        $this->postJson('/api/v1/customer/auth/mobile-login', [
            'country_code' => '+91',
            'mobile' => '9999999999',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid credentials.');
    }

    public function test_customer_mobile_login_rejects_inactive_customer(): void
    {
        Customer::factory()->create([
            'country_code' => '+91',
            'mobile' => '9999999999',
            'password' => 'secret-password',
            'is_active' => false,
        ]);

        $this->postJson('/api/v1/customer/auth/mobile-login', [
            'country_code' => '+91',
            'mobile' => '9999999999',
            'password' => 'secret-password',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'This account is inactive.');
    }

    public function test_customer_mobile_login_validates_required_fields(): void
    {
        $this->postJson('/api/v1/customer/auth/mobile-login', [
            'password' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['country_code', 'mobile', 'password']);
    }
}
