<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\CashCollection;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminEarningReportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_paginated_searchable_store_earning_report(): void
    {
        $token = $this->adminTokenWithPermission('reports.view');
        $store = Store::factory()->create([
            'title' => 'Milky Way Central',
            'email' => 'central@example.test',
            'rating' => 3.25,
        ]);
        Store::factory()->create(['title' => 'Hidden Dairy']);

        Order::factory()->for($store)->create([
            'status' => 'Completed',
            'subtotal' => 200,
            'coupon_amount' => 20,
            'delivery_charge' => 10,
            'commission_percent' => 10,
            'total_rating' => 4,
            'rider_id' => null,
            'coupon_id' => null,
        ]);
        SubscriptionOrder::factory()->for($store)->create([
            'status' => 'Completed',
            'subtotal' => 300,
            'coupon_amount' => 30,
            'delivery_charge' => 5,
            'commission_percent' => 10,
            'total_rating' => 2,
            'rider_id' => null,
            'coupon_id' => null,
        ]);
        Order::factory()->for($store)->create([
            'status' => 'Pending',
            'subtotal' => 999,
            'coupon_amount' => 0,
            'delivery_charge' => 0,
            'commission_percent' => 10,
            'rider_id' => null,
            'coupon_id' => null,
        ]);
        PayoutRequest::factory()->for($store)->create(['amount' => 100]);
        CashCollection::factory()->for($store)->create(['amount' => 50]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/earning-reports?search=central&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.store.id', $store->id)
            ->assertJsonPath('data.0.store.title', 'Milky Way Central')
            ->assertJsonPath('data.0.sale_count', 2)
            ->assertJsonPath('data.0.total_amount', '465.00')
            ->assertJsonPath('data.0.cash_on_hand_amount', '140.00')
            ->assertJsonPath('data.0.delivery_charge', '15.00')
            ->assertJsonPath('data.0.platform_earning', '46.50')
            ->assertJsonPath('data.0.store_payout', '100.00')
            ->assertJsonPath('data.0.store_remaining_amount', '318.50')
            ->assertJsonPath('data.0.rating.average', '3.00')
            ->assertJsonPath('data.0.rating.count', 2);
    }

    public function test_admin_earning_report_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/earning-reports')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_earning_report_requires_reports_view_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/earning-reports')
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
