<?php

namespace Tests\Feature\Rider;

use App\Models\Admin;
use App\Models\Rider;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionOrderItem;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RiderSubscriptionOrderReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_can_list_assigned_current_subscription_orders_with_pagination_and_search(): void
    {
        [$rider, $token] = $this->riderToken();
        $otherRider = Rider::factory()->create();

        SubscriptionOrder::factory()->create([
            'rider_id' => $rider->getKey(),
            'customer_name' => 'Mina Customer',
            'customer_mobile' => '9991112222',
            'status' => 'Processing',
        ]);
        SubscriptionOrder::factory()->create([
            'rider_id' => $rider->getKey(),
            'customer_name' => 'Past Customer',
            'status' => 'Completed',
        ]);
        SubscriptionOrder::factory()->create([
            'rider_id' => $otherRider->getKey(),
            'customer_name' => 'Mina Other',
            'status' => 'Processing',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/rider/subscription-orders?status=current&search=mina&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.customer_name', 'Mina Customer')
            ->assertJsonPath('data.0.status', 'Processing')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_rider_can_list_assigned_past_subscription_orders(): void
    {
        [$rider, $token] = $this->riderToken();

        SubscriptionOrder::factory()->create(['rider_id' => $rider->getKey(), 'status' => 'Processing']);
        SubscriptionOrder::factory()->create(['rider_id' => $rider->getKey(), 'status' => 'Completed']);
        SubscriptionOrder::factory()->create(['rider_id' => $rider->getKey(), 'status' => 'Cancelled']);

        $this->withToken($token)
            ->getJson('/api/v1/rider/subscription-orders?status=past')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonCount(2, 'data');
    }

    public function test_rider_can_view_assigned_subscription_order_detail_with_schedule(): void
    {
        [$rider, $token] = $this->riderToken();
        $order = SubscriptionOrder::factory()->create([
            'rider_id' => $rider->getKey(),
            'customer_name' => 'Mina Customer',
            'customer_mobile' => '9991112222',
            'status' => 'Processing',
            'internal_status' => 3,
        ]);
        SubscriptionOrderItem::factory()->create([
            'subscription_order_id' => $order->getKey(),
            'product_title' => 'Organic Milk',
            'variant_title' => '1 Litre',
            'total_dates' => '2026-07-07,2026-07-08',
            'completed_dates' => '2026-07-07',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/rider/subscription-orders/'.$order->getKey())
            ->assertOk()
            ->assertJsonPath('data.id', $order->getKey())
            ->assertJsonPath('data.customer_name', 'Mina Customer')
            ->assertJsonPath('data.customer_mobile', '9991112222')
            ->assertJsonPath('data.internal_status', 3)
            ->assertJsonPath('data.items.0.product_title', 'Organic Milk')
            ->assertJsonPath('data.items.0.schedule.0.date', '2026-07-07')
            ->assertJsonPath('data.items.0.schedule.0.is_complete', true)
            ->assertJsonPath('data.items.0.schedule.1.date', '2026-07-08')
            ->assertJsonPath('data.items.0.schedule.1.is_complete', false);
    }

    public function test_rider_cannot_view_another_riders_subscription_order(): void
    {
        [, $token] = $this->riderToken();
        $order = SubscriptionOrder::factory()->create([
            'rider_id' => Rider::factory(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/rider/subscription-orders/'.$order->getKey())
            ->assertNotFound()
            ->assertJsonPath('message', 'Subscription order was not found.');
    }

    public function test_rider_subscription_orders_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('orders.view');
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/rider/subscription-orders')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    /**
     * @return array{0: Rider, 1: string}
     */
    private function riderToken(): array
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $rider = Rider::factory()->create([
            'email' => 'rider@example.test',
            'password' => Hash::make('password'),
        ]);
        $rider->assignRole('rider');

        $token = $this->postJson('/api/v1/rider/auth/login', [
            'email' => 'rider@example.test',
            'password' => 'password',
        ])
            ->assertOk()
            ->json('data.token');

        return [$rider, $token];
    }
}
