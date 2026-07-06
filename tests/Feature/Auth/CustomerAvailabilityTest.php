<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_email_availability_returns_available_when_email_is_new(): void
    {
        $this->postJson('/api/v1/customer/auth/email-availability', [
            'email' => 'new@example.test',
        ])
            ->assertOk()
            ->assertJsonPath('data.field', 'email')
            ->assertJsonPath('data.available', true)
            ->assertJsonPath('message', 'New email address.');
    }

    public function test_customer_email_availability_returns_unavailable_when_email_exists(): void
    {
        Customer::factory()->create(['email' => 'used@example.test']);

        $this->postJson('/api/v1/customer/auth/email-availability', [
            'email' => 'used@example.test',
        ])
            ->assertOk()
            ->assertJsonPath('data.field', 'email')
            ->assertJsonPath('data.available', false)
            ->assertJsonPath('message', 'Email address already exists.');
    }

    public function test_customer_mobile_availability_checks_country_code_and_mobile_together(): void
    {
        Customer::factory()->create([
            'country_code' => '+91',
            'mobile' => '9999999999',
        ]);

        $this->postJson('/api/v1/customer/auth/mobile-availability', [
            'country_code' => '+91',
            'mobile' => '9999999999',
        ])
            ->assertOk()
            ->assertJsonPath('data.field', 'mobile')
            ->assertJsonPath('data.available', false)
            ->assertJsonPath('message', 'Mobile number already exists.');

        $this->postJson('/api/v1/customer/auth/mobile-availability', [
            'country_code' => '+1',
            'mobile' => '9999999999',
        ])
            ->assertOk()
            ->assertJsonPath('data.field', 'mobile')
            ->assertJsonPath('data.available', true)
            ->assertJsonPath('message', 'New mobile number.');
    }

    public function test_customer_availability_validates_required_fields(): void
    {
        $this->postJson('/api/v1/customer/auth/email-availability')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->postJson('/api/v1/customer/auth/mobile-availability', [
            'mobile' => '9999999999',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['country_code']);
    }
}
