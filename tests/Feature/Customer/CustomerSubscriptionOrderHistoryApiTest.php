<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Rider;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionOrderItem;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerSubscriptionOrderHistoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_current_subscription_orders_with_search_and_pagination(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $store = Store::factory()->create(['title' => 'Aarav Dairy']);

        SubscriptionOrder::factory()->for($customer)->for($store)->create([
            'status' => 'Pending',
            'transaction_id' => 'SUB-CURRENT-1',
            'customer_name' => 'Customer Demo',
        ]);
        SubscriptionOrder::factory()->for($customer)->for($store)->create([
            'status' => 'Active',
            'transaction_id' => 'SUB-CURRENT-2',
            'customer_name' => 'Customer Demo',
        ]);
        SubscriptionOrder::factory()->for($customer)->for($store)->create([
            'status' => 'Completed',
            'transaction_id' => 'SUB-PAST-1',
            'customer_name' => 'Customer Demo',
        ]);
        SubscriptionOrder::factory()->create(['status' => 'Pending', 'transaction_id' => 'SUB-OTHER-1']);

        $this->withToken($token)
            ->getJson('/api/v1/customer/subscription-orders?status=current&search=current&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.transaction_id', 'SUB-CURRENT-2')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_customer_can_list_past_subscription_orders(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();

        SubscriptionOrder::factory()->for($customer)->create(['status' => 'Completed', 'transaction_id' => 'SUB-COMPLETE']);
        SubscriptionOrder::factory()->for($customer)->create(['status' => 'Cancelled', 'transaction_id' => 'SUB-CANCELLED']);
        SubscriptionOrder::factory()->for($customer)->create(['status' => 'Pending', 'transaction_id' => 'SUB-PENDING']);

        $this->withToken($token)
            ->getJson('/api/v1/customer/subscription-orders?status=past&per_page=5')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_customer_can_view_own_subscription_order_detail_with_schedule(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $store = Store::factory()->create(['title' => 'Aarav Dairy']);
        $paymentMethod = PaymentMethod::factory()->create(['title' => 'Wallet']);
        $rider = Rider::factory()->for($store)->create(['name' => 'Rider Demo']);
        $order = SubscriptionOrder::factory()
            ->for($customer)
            ->for($store)
            ->for($paymentMethod, 'paymentMethod')
            ->for($rider)
            ->create([
                'status' => 'Pending',
                'transaction_id' => 'SUB-DETAIL-1',
                'customer_name' => 'Customer Demo',
                'customer_mobile' => '9999999999',
            ]);
        SubscriptionOrderItem::factory()->for($order, 'subscriptionOrder')->create([
            'product_title' => 'Cow Milk 1L',
            'total_dates' => '2026-07-08,2026-07-13',
            'completed_dates' => '2026-07-08',
        ]);

        $this->withToken($token)
            ->getJson("/api/v1/customer/subscription-orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.transaction_id', 'SUB-DETAIL-1')
            ->assertJsonPath('data.store.title', 'Aarav Dairy')
            ->assertJsonPath('data.payment_method.title', 'Wallet')
            ->assertJsonPath('data.rider.name', 'Rider Demo')
            ->assertJsonPath('data.items.0.product_title', 'Cow Milk 1L')
            ->assertJsonPath('data.items.0.schedule.0.date', '2026-07-08')
            ->assertJsonPath('data.items.0.schedule.0.is_complete', true)
            ->assertJsonPath('data.items.0.schedule.1.is_complete', false);
    }

    public function test_customer_cannot_view_another_customers_subscription_order(): void
    {
        $token = $this->customerToken();
        $order = SubscriptionOrder::factory()->create();

        $this->withToken($token)
            ->getJson("/api/v1/customer/subscription-orders/{$order->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Subscription order was not found.');
    }

    public function test_customer_subscription_order_history_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/customer/subscription-orders')
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
