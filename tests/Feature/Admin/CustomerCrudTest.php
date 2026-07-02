<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CustomerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_customers(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');

        $customer = Customer::factory()->create([
            'name' => 'Aarav Customer',
            'email' => 'aarav@example.test',
            'mobile' => '+919999999991',
            'wallet_balance' => 12.5,
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/customers')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Aarav Customer')
            ->assertJsonPath('data.0.wallet_balance', '12.50');

        $this->withToken($token)
            ->getJson("/api/v1/admin/customers/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $customer->id)
            ->assertJsonPath('data.email', 'aarav@example.test');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/customers', $this->customerPayload([
                'name' => 'Mira Customer',
                'email' => 'mira@example.test',
            ]))
            ->assertCreated()
            ->assertJsonPath('message', 'Customer created successfully.')
            ->assertJsonPath('data.name', 'Mira Customer')
            ->assertJsonPath('data.profile_image_path', 'images/users/mira.png')
            ->json('data.id');

        $this->assertTrue(Hash::check('secret-password', Customer::findOrFail($createdId)->password));

        $this->withToken($token)
            ->putJson("/api/v1/admin/customers/{$createdId}", [
                'name' => 'Mira Customer Updated',
                'is_active' => false,
                'wallet_balance' => 25,
                'password' => 'changed-password',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Customer updated successfully.')
            ->assertJsonPath('data.name', 'Mira Customer Updated')
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.wallet_balance', '25.00');

        $this->assertTrue(Hash::check('changed-password', Customer::findOrFail($createdId)->password));

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/customers/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Customer deleted successfully.');

        $this->assertSoftDeleted('customers', ['id' => $createdId]);
    }

    public function test_admin_customer_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');

        Customer::factory()->create(['name' => 'Aarav Customer', 'email' => 'aarav@example.test']);
        Customer::factory()->create(['name' => 'Mira Customer', 'email' => 'mira@example.test']);
        Customer::factory()->create(['name' => 'Surat Buyer', 'email' => 'surat@example.test']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/customers?search=customer&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Aarav Customer')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_customer_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/customers', [
                'name' => '',
                'email' => 'not-an-email',
                'password' => 'short',
                'wallet_balance' => -1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'wallet_balance']);
    }

    public function test_admin_customer_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/customers')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_customer_routes_require_user_management_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/customers')
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');
    }

    /** @param array<string, mixed> $overrides */
    private function customerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Customer One',
            'profile_image_path' => 'images/users/mira.png',
            'email' => 'customer@example.test',
            'country_code' => '+91',
            'mobile' => '+919999999990',
            'password' => 'secret-password',
            'registered_at' => now()->toDateTimeString(),
            'referral_code' => 'REF123',
            'parent_referral_code' => 'PARENT1',
            'wallet_balance' => 10,
            'is_active' => true,
            'email_verified_at' => now()->toDateTimeString(),
        ], $overrides);
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
