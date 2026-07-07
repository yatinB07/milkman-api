<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StorePayoutRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_list_show_and_create_payout_requests(): void
    {
        [$store, $token] = $this->storeToken();
        Order::factory()->create([
            'store_id' => $store->getKey(),
            'status' => 'Completed',
            'subtotal' => 400,
            'coupon_amount' => 20,
            'delivery_charge' => 10,
            'commission_percent' => 10,
            'rider_id' => null,
            'coupon_id' => null,
        ]);
        $payout = PayoutRequest::factory()->create([
            'store_id' => $store->getKey(),
            'amount' => 125.50,
            'status' => 'pending',
            'request_type' => 'bank',
            'bank_name' => 'Demo Bank',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/payout-requests')
            ->assertOk()
            ->assertJsonPath('data.0.id', $payout->getKey());

        $this->withToken($token)
            ->getJson('/api/v1/store/payout-requests/'.$payout->getKey())
            ->assertOk()
            ->assertJsonPath('data.id', $payout->getKey())
            ->assertJsonPath('data.store_id', $store->getKey());

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/store/payout-requests', [
                'amount' => 75.25,
                'request_type' => 'upi',
                'upi_id' => 'store@upi',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Payout request created successfully.')
            ->assertJsonPath('data.store_id', $store->getKey())
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.request_type', 'upi')
            ->assertJsonPath('data.upi_id', 'store@upi')
            ->json('data.id');

        $this->assertDatabaseHas('payout_requests', [
            'id' => $createdId,
            'store_id' => $store->getKey(),
            'status' => 'pending',
            'request_type' => 'upi',
        ]);
    }

    public function test_store_cannot_request_payout_above_available_earnings(): void
    {
        [$store, $token] = $this->storeToken();
        Order::factory()->create([
            'store_id' => $store->getKey(),
            'status' => 'Completed',
            'subtotal' => 100,
            'coupon_amount' => 10,
            'delivery_charge' => 0,
            'commission_percent' => 10,
            'rider_id' => null,
            'coupon_id' => null,
        ]);
        SubscriptionOrder::factory()->create([
            'store_id' => $store->getKey(),
            'status' => 'Completed',
            'subtotal' => 50,
            'coupon_amount' => 0,
            'delivery_charge' => 0,
            'commission_percent' => 10,
            'rider_id' => null,
            'coupon_id' => null,
        ]);
        PayoutRequest::factory()->create([
            'store_id' => $store->getKey(),
            'amount' => 100,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/store/payout-requests', [
                'amount' => 30,
                'request_type' => 'upi',
                'upi_id' => 'store@upi',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'You cannot withdraw above your available earning.');

        $this->assertDatabaseMissing('payout_requests', [
            'store_id' => $store->getKey(),
            'amount' => 30,
            'upi_id' => 'store@upi',
        ]);
    }

    public function test_store_payout_request_list_is_paginated_and_searchable(): void
    {
        [$store, $token] = $this->storeToken();

        PayoutRequest::factory()->create(['store_id' => $store->getKey(), 'request_type' => 'bank', 'bank_name' => 'Milk Bank', 'requested_at' => now()->subMinutes(2)]);
        PayoutRequest::factory()->create(['store_id' => $store->getKey(), 'request_type' => 'upi', 'upi_id' => 'milk@upi', 'requested_at' => now()->subMinute()]);
        PayoutRequest::factory()->create(['store_id' => $store->getKey(), 'request_type' => 'paypal', 'paypal_id' => 'curd@example.test', 'requested_at' => now()]);
        PayoutRequest::factory()->create(['store_id' => Store::factory(), 'request_type' => 'bank', 'bank_name' => 'Milk Other Bank']);

        $this->withToken($token)
            ->getJson('/api/v1/store/payout-requests?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.request_type', 'upi')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_store_cannot_view_another_stores_payout_request(): void
    {
        [, $token] = $this->storeToken();
        $otherPayout = PayoutRequest::factory()->create(['store_id' => Store::factory()]);

        $this->withToken($token)
            ->getJson('/api/v1/store/payout-requests/'.$otherPayout->getKey())
            ->assertNotFound()
            ->assertJsonPath('message', 'Payout request was not found.');
    }

    public function test_store_payout_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('payouts.request');
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/store/payout-requests')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    /**
     * @return array{0: Store, 1: string}
     */
    private function storeToken(): array
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $store = Store::factory()->create([
            'email' => 'store@example.test',
            'password' => Hash::make('password'),
        ]);
        $store->assignRole('store-owner');

        $token = $this->postJson('/api/v1/store/auth/login', [
            'email' => 'store@example.test',
            'password' => 'password',
        ])
            ->assertOk()
            ->json('data.token');

        return [$store, $token];
    }
}
