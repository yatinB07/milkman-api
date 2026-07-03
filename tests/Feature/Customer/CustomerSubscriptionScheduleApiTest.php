<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionOrderItem;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerSubscriptionScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_skip_subscription_item_dates_and_receive_wallet_refund(): void
    {
        $token = $this->customerToken(['wallet_balance' => 25]);
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $order = SubscriptionOrder::factory()->for($customer)->create([
            'subtotal' => 300,
            'total' => 300,
            'wallet_amount' => 0,
            'delivery_charge' => 10,
            'coupon_amount' => 0,
        ]);
        $item = SubscriptionOrderItem::factory()->for($order, 'subscriptionOrder')->create([
            'price' => 50,
            'quantity' => 1,
            'total_deliveries' => 4,
            'total_dates' => '2026-07-06,2026-07-08,2026-07-13,2026-07-15',
            'selected_days' => '0,2',
        ]);

        $this->withToken($token)
            ->postJson("/api/v1/customer/subscription-orders/{$order->id}/items/{$item->id}/skip", [
                'dates' => ['2026-07-08', '2026-07-13'],
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Subscription dates skipped successfully.')
            ->assertJsonPath('wallet_balance', '125.00')
            ->assertJsonPath('data.items.0.total_deliveries', 2)
            ->assertJsonPath('data.items.0.total_dates', '2026-07-06,2026-07-15');

        $this->assertDatabaseHas('subscription_order_items', [
            'id' => $item->id,
            'total_deliveries' => 2,
            'total_dates' => '2026-07-06,2026-07-15',
        ]);
        $this->assertDatabaseHas('subscription_orders', [
            'id' => $order->id,
            'subtotal' => 200,
            'total' => 210,
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'customer_id' => $customer->id,
            'type' => 'Credit',
            'amount' => 100,
            'message' => "Refund amount for subscription order #{$order->id}",
        ]);
    }

    public function test_customer_can_extend_subscription_item_dates(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $order = SubscriptionOrder::factory()->for($customer)->create();
        $item = SubscriptionOrderItem::factory()->for($order, 'subscriptionOrder')->create([
            'total_deliveries' => 4,
            'total_dates' => '2026-07-06,2026-07-08,2026-07-13,2026-07-15',
            'selected_days' => '0,2',
        ]);

        $this->withToken($token)
            ->postJson("/api/v1/customer/subscription-orders/{$order->id}/items/{$item->id}/extend", [
                'dates' => ['2026-07-08', '2026-07-13'],
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Subscription dates extended successfully.')
            ->assertJsonPath('data.items.0.total_dates', '2026-07-06,2026-07-15,2026-07-20,2026-07-22')
            ->assertJsonPath('data.items.0.schedule.2.date', '2026-07-20')
            ->assertJsonPath('data.items.0.schedule.3.date', '2026-07-22');

        $this->assertDatabaseHas('subscription_order_items', [
            'id' => $item->id,
            'total_dates' => '2026-07-06,2026-07-15,2026-07-20,2026-07-22',
        ]);
    }

    public function test_subscription_schedule_requires_dates_in_the_item_schedule(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $order = SubscriptionOrder::factory()->for($customer)->create();
        $item = SubscriptionOrderItem::factory()->for($order, 'subscriptionOrder')->create([
            'total_dates' => '2026-07-06,2026-07-08',
        ]);

        $this->withToken($token)
            ->postJson("/api/v1/customer/subscription-orders/{$order->id}/items/{$item->id}/skip", [
                'dates' => ['2026-07-10'],
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Selected subscription date was not found.');
    }

    public function test_customer_cannot_change_another_customers_subscription_item_schedule(): void
    {
        $token = $this->customerToken();
        $order = SubscriptionOrder::factory()->create();
        $item = SubscriptionOrderItem::factory()->for($order, 'subscriptionOrder')->create([
            'total_dates' => '2026-07-06,2026-07-08',
        ]);

        $this->withToken($token)
            ->postJson("/api/v1/customer/subscription-orders/{$order->id}/items/{$item->id}/skip", [
                'dates' => ['2026-07-08'],
            ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Subscription order item was not found.');
    }

    public function test_subscription_schedule_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');
        $order = SubscriptionOrder::factory()->create();
        $item = SubscriptionOrderItem::factory()->for($order, 'subscriptionOrder')->create();

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->postJson("/api/v1/customer/subscription-orders/{$order->id}/items/{$item->id}/skip", [
                'dates' => ['2026-07-08'],
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    /** @param array<string, mixed> $attributes */
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
