<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CustomerAddressCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_customer_addresses(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');
        $customer = Customer::factory()->create(['name' => 'Aarav Customer']);

        $address = CustomerAddress::factory()->for($customer)->create([
            'address' => 'CG Road, Ahmedabad',
            'landmark' => 'Near market',
            'type' => 'Home',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/customer-addresses')
            ->assertOk()
            ->assertJsonPath('data.0.address', 'CG Road, Ahmedabad')
            ->assertJsonPath('data.0.customer.name', 'Aarav Customer');

        $this->withToken($token)
            ->getJson("/api/v1/admin/customer-addresses/{$address->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $address->id)
            ->assertJsonPath('data.landmark', 'Near market');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/customer-addresses', [
                'customer_id' => $customer->id,
                'address' => 'Satellite Road, Ahmedabad',
                'landmark' => 'Near school',
                'rider_instruction' => 'Call before delivery.',
                'type' => 'Office',
                'latitude' => 23.022505,
                'longitude' => 72.571365,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Customer address created successfully.')
            ->assertJsonPath('data.address', 'Satellite Road, Ahmedabad')
            ->assertJsonPath('data.customer.id', $customer->id)
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/customer-addresses/{$createdId}", [
                'address' => 'Satellite Road Updated',
                'type' => 'Home',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Customer address updated successfully.')
            ->assertJsonPath('data.address', 'Satellite Road Updated')
            ->assertJsonPath('data.type', 'Home');

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/customer-addresses/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Customer address deleted successfully.');

        $this->assertSoftDeleted('customer_addresses', ['id' => $createdId]);
    }

    public function test_admin_customer_address_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');
        $customer = Customer::factory()->create(['name' => 'Aarav Customer']);

        CustomerAddress::factory()->for($customer)->create(['address' => 'CG Road, Ahmedabad', 'type' => 'Home']);
        CustomerAddress::factory()->create(['address' => 'Satellite Road, Ahmedabad', 'type' => 'Office']);
        CustomerAddress::factory()->create(['address' => 'Ring Road, Surat', 'type' => 'Home']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/customer-addresses?search=ahmedabad&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.address', 'CG Road, Ahmedabad')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_customer_address_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/customer-addresses', [
                'customer_id' => 999,
                'address' => '',
                'type' => '',
                'latitude' => 100,
                'longitude' => 200,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['customer_id', 'address', 'type', 'latitude', 'longitude']);
    }

    public function test_admin_customer_address_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/customer-addresses')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_customer_address_routes_require_user_management_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/customer-addresses')
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');
    }

    private function adminTokenWithPermission(string $permission): string
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = Admin::factory()->create(['password' => 'secret-password']);
        $admin->givePermissionTo(Permission::findByName($permission, 'sanctum'));

        return $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
