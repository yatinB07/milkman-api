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

class RiderSubscriptionDeliveryDateCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_can_complete_due_subscription_delivery_date(): void
    {
        [$rider, $token] = $this->riderToken();
        $date = now()->toDateString();
        $order = SubscriptionOrder::factory()->create([
            'rider_id' => $rider->getKey(),
            'status' => 'Processing',
        ]);
        $item = SubscriptionOrderItem::factory()->create([
            'subscription_order_id' => $order->getKey(),
            'total_dates' => $date.','.now()->addDay()->toDateString(),
            'completed_dates' => '',
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/subscription-orders/'.$order->getKey().'/items/'.$item->getKey().'/complete-date', [
                'selected_date' => $date,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Subscription delivery date completed successfully.')
            ->assertJsonPath('data.items.0.completed_dates', $date)
            ->assertJsonPath('data.items.0.schedule.0.date', $date)
            ->assertJsonPath('data.items.0.schedule.0.is_complete', true);

        $this->assertDatabaseHas('subscription_order_items', [
            'id' => $item->getKey(),
            'completed_dates' => $date,
        ]);
    }

    public function test_rider_cannot_complete_same_subscription_delivery_date_twice(): void
    {
        [$rider, $token] = $this->riderToken();
        $date = now()->toDateString();
        $order = SubscriptionOrder::factory()->create(['rider_id' => $rider->getKey()]);
        $item = SubscriptionOrderItem::factory()->create([
            'subscription_order_id' => $order->getKey(),
            'total_dates' => $date,
            'completed_dates' => $date,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/subscription-orders/'.$order->getKey().'/items/'.$item->getKey().'/complete-date', [
                'selected_date' => $date,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Selected delivery date is already completed.');
    }

    public function test_rider_cannot_complete_date_outside_subscription_schedule(): void
    {
        [$rider, $token] = $this->riderToken();
        $order = SubscriptionOrder::factory()->create(['rider_id' => $rider->getKey()]);
        $item = SubscriptionOrderItem::factory()->create([
            'subscription_order_id' => $order->getKey(),
            'total_dates' => now()->toDateString(),
            'completed_dates' => '',
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/subscription-orders/'.$order->getKey().'/items/'.$item->getKey().'/complete-date', [
                'selected_date' => now()->subDay()->toDateString(),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Selected delivery date is not part of this subscription.');
    }

    public function test_rider_cannot_complete_future_subscription_delivery_date(): void
    {
        [$rider, $token] = $this->riderToken();
        $date = now()->addDay()->toDateString();
        $order = SubscriptionOrder::factory()->create(['rider_id' => $rider->getKey()]);
        $item = SubscriptionOrderItem::factory()->create([
            'subscription_order_id' => $order->getKey(),
            'total_dates' => $date,
            'completed_dates' => '',
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/subscription-orders/'.$order->getKey().'/items/'.$item->getKey().'/complete-date', [
                'selected_date' => $date,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Only today or past delivery dates can be completed.');
    }

    public function test_rider_cannot_complete_another_riders_subscription_delivery_date(): void
    {
        [, $token] = $this->riderToken();
        $order = SubscriptionOrder::factory()->create(['rider_id' => Rider::factory()]);
        $item = SubscriptionOrderItem::factory()->create(['subscription_order_id' => $order->getKey()]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/subscription-orders/'.$order->getKey().'/items/'.$item->getKey().'/complete-date', [
                'selected_date' => now()->toDateString(),
            ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Subscription order item was not found.');
    }

    public function test_rider_subscription_delivery_date_completion_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('orders.update-status');
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/rider/subscription-orders/1/items/1/complete-date', [
                'selected_date' => now()->toDateString(),
            ])
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
