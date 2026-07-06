<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_reset_password_with_country_code_and_mobile(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'customer@example.test',
            'country_code' => '+91',
            'mobile' => '9999999999',
            'password' => 'old-secret-password',
        ]);

        $this->postJson('/api/v1/customer/auth/password/reset', [
            'country_code' => '+91',
            'mobile' => '9999999999',
            'password' => 'new-secret-password',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Password changed successfully.');

        $customer->refresh();

        $this->assertTrue(Hash::check('new-secret-password', $customer->getAttribute('password')));
        $this->assertFalse(Hash::check('old-secret-password', $customer->getAttribute('password')));

        $this->postJson('/api/v1/customer/auth/login', [
            'email' => 'customer@example.test',
            'password' => 'new-secret-password',
        ])->assertOk();
    }

    public function test_customer_password_reset_requires_matching_country_code_and_mobile(): void
    {
        Customer::factory()->create([
            'country_code' => '+91',
            'mobile' => '9999999999',
            'password' => 'old-secret-password',
        ]);

        $this->postJson('/api/v1/customer/auth/password/reset', [
            'country_code' => '+1',
            'mobile' => '9999999999',
            'password' => 'new-secret-password',
        ])
            ->assertNotFound()
            ->assertJsonPath('message', 'No customer was found for that mobile number.');
    }

    public function test_customer_password_reset_validates_required_fields(): void
    {
        $this->postJson('/api/v1/customer/auth/password/reset', [
            'password' => 'short',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['country_code', 'mobile', 'password']);
    }
}
