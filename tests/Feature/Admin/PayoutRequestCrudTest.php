<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\PayoutRequest;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PayoutRequestCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_payout_requests(): void
    {
        $token = $this->adminTokenWithPermission('payouts.approve');
        $store = Store::factory()->create(['title' => 'Milky Way Central']);

        $payout = PayoutRequest::factory()->for($store)->create([
            'amount' => 250,
            'status' => 'pending',
            'request_type' => 'upi',
            'upi_id' => 'central@upi',
            'requested_at' => now()->addMinute(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/payout-requests')
            ->assertOk()
            ->assertJsonPath('data.0.amount', '250.00')
            ->assertJsonPath('data.0.store.title', 'Milky Way Central');

        $this->withToken($token)
            ->getJson("/api/v1/admin/payout-requests/{$payout->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $payout->id)
            ->assertJsonPath('data.upi_id', 'central@upi');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/payout-requests', [
                'store_id' => $store->id,
                'amount' => 500,
                'status' => 'pending',
                'requested_at' => now()->toDateTimeString(),
                'request_type' => 'bank',
                'account_number' => '1234567890',
                'bank_name' => 'Demo Bank',
                'account_name' => 'Milky Way Central',
                'ifsc_code' => 'DEMO0001234',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Payout request created successfully.')
            ->assertJsonPath('data.amount', '500.00')
            ->assertJsonPath('data.store.id', $store->id)
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/payout-requests/{$createdId}", [
                'status' => 'paid',
                'proof_path' => 'images/payout/receipt.jpg',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Payout request updated successfully.')
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.proof_path', 'images/payout/receipt.jpg');

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/payout-requests/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Payout request deleted successfully.');

        $this->assertSoftDeleted('payout_requests', ['id' => $createdId]);
    }

    public function test_admin_payout_request_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('payouts.approve');
        $store = Store::factory()->create(['title' => 'Milky Way Central']);

        PayoutRequest::factory()->for($store)->create([
            'status' => 'pending',
            'request_type' => 'upi',
            'upi_id' => 'central@upi',
            'requested_at' => now()->addMinutes(2),
        ]);
        PayoutRequest::factory()->create([
            'status' => 'paid',
            'request_type' => 'paypal',
            'paypal_id' => 'paid@example.com',
            'requested_at' => now()->addMinute(),
        ]);
        PayoutRequest::factory()->create([
            'status' => 'rejected',
            'request_type' => 'bank',
            'bank_name' => 'Other Bank',
            'requested_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/payout-requests?search=central&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.request_type', 'upi')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_admin_payout_request_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('payouts.approve');

        $this->withToken($token)
            ->postJson('/api/v1/admin/payout-requests', [
                'store_id' => 999,
                'amount' => -1,
                'status' => 'unknown',
                'requested_at' => 'not-a-date',
                'request_type' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id', 'amount', 'status', 'requested_at', 'request_type']);
    }

    public function test_admin_payout_request_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/payout-requests')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_payout_request_routes_require_payout_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/payout-requests')
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
