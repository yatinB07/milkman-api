<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\WalletTransaction;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerWalletApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_own_wallet_transactions(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $otherCustomer = Customer::factory()->create();

        WalletTransaction::factory()->for($customer)->create([
            'message' => 'Welcome credit',
            'type' => 'Credit',
            'amount' => 25,
            'transacted_at' => now()->subMinutes(2),
        ]);
        WalletTransaction::factory()->for($customer)->create([
            'message' => 'Order payment',
            'type' => 'Debit',
            'amount' => 10,
            'transacted_at' => now()->subMinute(),
        ]);
        WalletTransaction::factory()->for($otherCustomer)->create([
            'message' => 'Other customer credit',
            'type' => 'Credit',
            'amount' => 99,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/customer/wallet-transactions?search=credit&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.message', 'Welcome credit')
            ->assertJsonPath('data.0.amount', '25.00')
            ->assertJsonPath('wallet_balance', '0.00')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_customer_wallet_transactions_cover_legacy_wallet_report_order_and_balance(): void
    {
        $token = $this->customerToken(['wallet_balance' => 75]);
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();

        WalletTransaction::factory()->for($customer)->create([
            'message' => 'Opening credit',
            'type' => 'Credit',
            'amount' => 100,
            'transacted_at' => now()->subDay(),
        ]);
        WalletTransaction::factory()->for($customer)->create([
            'message' => 'Order debit',
            'type' => 'Debit',
            'amount' => 25,
            'transacted_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/customer/wallet-transactions')
            ->assertOk()
            ->assertJsonPath('data.0.message', 'Order debit')
            ->assertJsonPath('data.0.type', 'Debit')
            ->assertJsonPath('data.0.amount', '25.00')
            ->assertJsonPath('data.1.message', 'Opening credit')
            ->assertJsonPath('data.1.type', 'Credit')
            ->assertJsonPath('data.1.amount', '100.00')
            ->assertJsonPath('wallet_balance', '75.00')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_customer_can_credit_wallet_balance(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();

        $transactionId = $this->withToken($token)
            ->postJson('/api/v1/customer/wallet/top-ups', [
                'amount' => 150,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Wallet updated successfully.')
            ->assertJsonPath('wallet_balance', '150.00')
            ->assertJsonPath('data.message', 'Wallet Balance Added!!')
            ->assertJsonPath('data.type', 'Credit')
            ->assertJsonPath('data.amount', '150.00')
            ->json('data.id');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'wallet_balance' => '150.00',
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'id' => $transactionId,
            'customer_id' => $customer->id,
            'message' => 'Wallet Balance Added!!',
            'type' => 'Credit',
            'amount' => '150.00',
        ]);
    }

    public function test_customer_wallet_top_up_validates_amount(): void
    {
        $token = $this->customerToken();

        $this->withToken($token)
            ->postJson('/api/v1/customer/wallet/top-ups', [
                'amount' => 0,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_customer_wallet_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/customer/wallet-transactions')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function customerToken(array $attributes = []): string
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(array_merge([
            'email' => 'customer@example.test',
            'password' => 'secret-password',
        ], $attributes));
        $customer->assignRole('customer');

        return $this->postJson('/api/v1/customer/auth/login', [
            'email' => 'customer@example.test',
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
