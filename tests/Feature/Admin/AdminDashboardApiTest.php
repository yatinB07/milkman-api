<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Banner;
use App\Models\CashCollection;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Page;
use App\Models\PaymentMethod;
use App\Models\PayoutRequest;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use App\Models\Zone;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_legacy_dashboard_metrics(): void
    {
        $token = $this->adminTokenWithPermission('reports.view');
        $store = Store::factory()->create();
        $customer = Customer::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        Banner::factory()->count(2)->create();
        Category::factory()->create();
        Zone::factory()->create();
        Page::factory()->create();

        Order::factory()->for($store)->for($customer)->for($paymentMethod)->create([
            'rider_id' => null,
            'coupon_id' => null,
            'status' => 'Completed',
            'subtotal' => 200,
            'coupon_amount' => 20,
            'delivery_charge' => 10,
            'commission_percent' => 10,
        ]);
        SubscriptionOrder::factory()->for($store)->for($customer)->for($paymentMethod)->create([
            'rider_id' => null,
            'coupon_id' => null,
            'status' => 'Completed',
            'subtotal' => 300,
            'coupon_amount' => 30,
            'delivery_charge' => 0,
            'commission_percent' => 10,
        ]);
        PayoutRequest::factory()->for($store)->create(['status' => 'completed', 'amount' => 40]);
        PayoutRequest::factory()->for($store)->create(['status' => 'pending', 'amount' => 25]);
        CashCollection::factory()->for($store)->create(['amount' => 30]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.counts.banners', 2)
            ->assertJsonPath('data.counts.categories', 1)
            ->assertJsonPath('data.counts.zones', 1)
            ->assertJsonPath('data.counts.stores', 1)
            ->assertJsonPath('data.counts.payment_methods', 1)
            ->assertJsonPath('data.counts.pages', 1)
            ->assertJsonPath('data.counts.customers', 1)
            ->assertJsonPath('data.financials.total_earning', '46.00')
            ->assertJsonPath('data.financials.total_sales', '460.00')
            ->assertJsonPath('data.financials.completed_payout', '40.00')
            ->assertJsonPath('data.financials.pending_payout', '25.00')
            ->assertJsonPath('data.financials.on_hand_cash_amount', '160.00')
            ->assertJsonPath('data.cards.0.title', 'Banners')
            ->assertJsonPath('data.cards.0.report_data', 2);
    }

    public function test_admin_dashboard_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_dashboard_requires_reports_view_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');
    }

    private function adminTokenWithPermission(string $permission): string
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = Admin::factory()->create(['password' => 'secret-password']);
        $admin->givePermissionTo($permission);

        return $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
