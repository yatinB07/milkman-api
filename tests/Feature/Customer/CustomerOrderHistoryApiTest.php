<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Rider;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderHistoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_current_orders_with_search_and_pagination(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $store = Store::factory()->create(['title' => 'Aarav Dairy']);

        Order::factory()->for($customer)->for($store)->create([
            'status' => 'Pending',
            'transaction_id' => 'ORDER-CURRENT-1',
            'customer_name' => 'Customer Demo',
        ]);
        Order::factory()->for($customer)->for($store)->create([
            'status' => 'Processing',
            'transaction_id' => 'ORDER-CURRENT-2',
            'customer_name' => 'Customer Demo',
        ]);
        Order::factory()->for($customer)->for($store)->create([
            'status' => 'Completed',
            'transaction_id' => 'ORDER-PAST-1',
            'customer_name' => 'Customer Demo',
        ]);
        Order::factory()->create(['status' => 'Pending', 'transaction_id' => 'ORDER-OTHER-1']);

        $this->withToken($token)
            ->getJson('/api/v1/customer/orders?status=current&search=current&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.transaction_id', 'ORDER-CURRENT-2')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_customer_can_list_past_orders(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();

        Order::factory()->for($customer)->create(['status' => 'Completed', 'transaction_id' => 'ORDER-COMPLETE']);
        Order::factory()->for($customer)->create(['status' => 'Cancelled', 'transaction_id' => 'ORDER-CANCELLED']);
        Order::factory()->for($customer)->create(['status' => 'Pending', 'transaction_id' => 'ORDER-PENDING']);

        $this->withToken($token)
            ->getJson('/api/v1/customer/orders?status=past&per_page=5')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_customer_can_view_own_order_detail(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $store = Store::factory()->create(['title' => 'Aarav Dairy']);
        $paymentMethod = PaymentMethod::factory()->create(['title' => 'Cash']);
        $rider = Rider::factory()->for($store)->create(['name' => 'Rider Demo']);
        $order = Order::factory()
            ->for($customer)
            ->for($store)
            ->for($paymentMethod, 'paymentMethod')
            ->for($rider)
            ->create([
                'status' => 'Pending',
                'transaction_id' => 'ORDER-DETAIL-1',
                'customer_name' => 'Customer Demo',
                'customer_mobile' => '9999999999',
            ]);
        OrderItem::factory()->for($order)->create(['product_title' => 'Cow Milk 1L']);

        $this->withToken($token)
            ->getJson("/api/v1/customer/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.transaction_id', 'ORDER-DETAIL-1')
            ->assertJsonPath('data.store.title', 'Aarav Dairy')
            ->assertJsonPath('data.payment_method.title', 'Cash')
            ->assertJsonPath('data.rider.name', 'Rider Demo')
            ->assertJsonPath('data.items.0.product_title', 'Cow Milk 1L');
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $token = $this->customerToken();
        $order = Order::factory()->create();

        $this->withToken($token)
            ->getJson("/api/v1/customer/orders/{$order->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Order was not found.');
    }

    public function test_customer_order_history_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/customer/orders')
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
