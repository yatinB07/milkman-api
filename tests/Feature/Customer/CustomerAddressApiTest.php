<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAddressApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_manage_own_addresses(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();

        $address = CustomerAddress::factory()->for($customer)->create([
            'address' => '123 Milk Street',
            'landmark' => 'Near Market',
            'type' => 'Home',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/customer/addresses')
            ->assertOk()
            ->assertJsonPath('data.0.address', '123 Milk Street')
            ->assertJsonPath('data.0.type', 'Home');

        $this->withToken($token)
            ->getJson("/api/v1/customer/addresses/{$address->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $address->id)
            ->assertJsonPath('data.landmark', 'Near Market');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/customer/addresses', [
                'address' => '456 Dairy Road',
                'landmark' => 'Opposite Park',
                'rider_instruction' => 'Leave at reception.',
                'type' => 'Office',
                'latitude' => 23.022505,
                'longitude' => 72.571365,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Address saved successfully.')
            ->assertJsonPath('data.address', '456 Dairy Road')
            ->assertJsonPath('data.type', 'Office')
            ->json('data.id');

        $this->assertDatabaseHas('customer_addresses', [
            'id' => $createdId,
            'customer_id' => $customer->id,
            'address' => '456 Dairy Road',
        ]);

        $this->withToken($token)
            ->putJson("/api/v1/customer/addresses/{$createdId}", [
                'address' => '789 Updated Avenue',
                'landmark' => 'Behind School',
                'rider_instruction' => 'Call before delivery.',
                'type' => 'Home',
                'latitude' => 21.170240,
                'longitude' => 72.831062,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Address updated successfully.')
            ->assertJsonPath('data.address', '789 Updated Avenue')
            ->assertJsonPath('data.type', 'Home');

        $this->withToken($token)
            ->deleteJson("/api/v1/customer/addresses/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Address deleted successfully.');

        $this->assertSoftDeleted('customer_addresses', ['id' => $createdId]);
    }

    public function test_customer_address_list_is_paginated_searchable_and_scoped_to_customer(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $otherCustomer = Customer::factory()->create();

        CustomerAddress::factory()->for($customer)->create([
            'address' => 'Home Milk Street',
            'created_at' => now()->subMinutes(2),
        ]);
        CustomerAddress::factory()->for($customer)->create([
            'address' => 'Office Milk Plaza',
            'created_at' => now()->subMinute(),
        ]);
        CustomerAddress::factory()->for($otherCustomer)->create([
            'address' => 'Other Milk Street',
            'created_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/customer/addresses?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.address', 'Office Milk Plaza')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_customer_cannot_access_another_customer_address(): void
    {
        $token = $this->customerToken();
        $otherAddress = CustomerAddress::factory()->create();

        $this->withToken($token)
            ->getJson("/api/v1/customer/addresses/{$otherAddress->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Customer address was not found.');
    }

    public function test_customer_address_create_validates_payload(): void
    {
        $token = $this->customerToken();

        $this->withToken($token)
            ->postJson('/api/v1/customer/addresses', [
                'address' => '',
                'type' => '',
                'latitude' => 'not-a-number',
                'longitude' => 'not-a-number',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['address', 'type', 'latitude', 'longitude']);
    }

    public function test_customer_address_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/customer/addresses')
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
