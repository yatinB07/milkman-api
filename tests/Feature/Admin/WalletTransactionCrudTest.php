<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\WalletTransaction;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WalletTransactionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_wallet_transactions(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');
        $customer = Customer::factory()->create(['name' => 'Aarav Customer']);

        $transaction = WalletTransaction::factory()->for($customer)->create([
            'message' => 'Wallet Balance Added!!',
            'type' => 'credit',
            'amount' => 50,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/wallet-transactions')
            ->assertOk()
            ->assertJsonPath('data.0.message', 'Wallet Balance Added!!')
            ->assertJsonPath('data.0.customer.name', 'Aarav Customer');

        $this->withToken($token)
            ->getJson("/api/v1/admin/wallet-transactions/{$transaction->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $transaction->id)
            ->assertJsonPath('data.amount', '50.00');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/wallet-transactions', [
                'customer_id' => $customer->id,
                'message' => 'Wallet Used in Order Id#10',
                'type' => 'debit',
                'amount' => 15,
                'transacted_at' => now()->toDateTimeString(),
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Wallet transaction created successfully.')
            ->assertJsonPath('data.message', 'Wallet Used in Order Id#10')
            ->assertJsonPath('data.customer.id', $customer->id)
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/wallet-transactions/{$createdId}", [
                'message' => 'Wallet Used in Order Id#11',
                'amount' => 20,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Wallet transaction updated successfully.')
            ->assertJsonPath('data.message', 'Wallet Used in Order Id#11')
            ->assertJsonPath('data.amount', '20.00');

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/wallet-transactions/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Wallet transaction deleted successfully.');

        $this->assertSoftDeleted('wallet_transactions', ['id' => $createdId]);
    }

    public function test_admin_wallet_transaction_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');
        $customer = Customer::factory()->create(['name' => 'Aarav Customer']);

        WalletTransaction::factory()->for($customer)->create([
            'message' => 'Wallet Balance Added!!',
            'type' => 'credit',
            'transacted_at' => now()->addMinutes(2),
        ]);
        WalletTransaction::factory()->create([
            'message' => 'Wallet Used in Order Id#10',
            'type' => 'debit',
            'transacted_at' => now()->addMinute(),
        ]);
        WalletTransaction::factory()->create([
            'message' => 'Referral Bonus Added',
            'type' => 'credit',
            'transacted_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/wallet-transactions?search=wallet&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.message', 'Wallet Balance Added!!')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_wallet_transaction_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('users.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/wallet-transactions', [
                'customer_id' => 999,
                'message' => '',
                'type' => '',
                'amount' => -1,
                'transacted_at' => 'not-a-date',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['customer_id', 'message', 'type', 'amount', 'transacted_at']);
    }

    public function test_admin_wallet_transaction_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/wallet-transactions')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_wallet_transaction_routes_require_user_management_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/wallet-transactions')
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
