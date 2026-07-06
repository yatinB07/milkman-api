<?php

namespace Tests\Feature\Rider;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Rider;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RiderOrderDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_can_accept_assigned_order(): void
    {
        [$rider, $token] = $this->riderToken();
        $order = Order::factory()->create([
            'rider_id' => $rider->getKey(),
            'customer_name' => 'Mina Customer',
            'status' => 'Processing',
            'internal_status' => 3,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/orders/'.$order->getKey().'/decision', [
                'decision' => 'accepted',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Order decision updated successfully.')
            ->assertJsonPath('data.status', 'On Route')
            ->assertJsonPath('data.internal_status', 4)
            ->assertJsonPath('data.rider_id', $rider->getKey());

        $this->assertDatabaseHas('orders', [
            'id' => $order->getKey(),
            'rider_id' => $rider->getKey(),
            'status' => 'On Route',
            'internal_status' => 4,
        ]);
        $this->assertDatabaseHas('customer_notifications', [
            'customer_id' => $order->getAttribute('customer_id'),
            'title' => 'Order On Route!!',
            'description' => 'Mina Customer, Your Order #'.$order->getKey().' Has Been On Route.',
        ]);
        $this->assertDatabaseHas('store_notifications', [
            'store_id' => $order->getAttribute('store_id'),
            'title' => 'Order Accepted',
            'description' => 'Rider Accepted Order #'.$order->getKey().'!!',
        ]);
    }

    public function test_rider_can_reject_assigned_order(): void
    {
        [$rider, $token] = $this->riderToken();
        $order = Order::factory()->create([
            'rider_id' => $rider->getKey(),
            'status' => 'Processing',
            'internal_status' => 3,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/orders/'.$order->getKey().'/decision', [
                'decision' => 'rejected',
                'rejection_comment' => 'Vehicle issue',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Order decision updated successfully.')
            ->assertJsonPath('data.rider_id', null)
            ->assertJsonPath('data.internal_status', 5)
            ->assertJsonPath('data.rejection_comment', 'Vehicle issue');

        $this->assertDatabaseHas('orders', [
            'id' => $order->getKey(),
            'rider_id' => null,
            'status' => 'Processing',
            'internal_status' => 5,
            'rejection_comment' => 'Vehicle issue',
        ]);
        $this->assertDatabaseHas('store_notifications', [
            'store_id' => $order->getAttribute('store_id'),
            'title' => 'Vehicle issue',
            'description' => 'Rider Rejected Order Find Another Rider!!',
        ]);
    }

    public function test_rider_cannot_decide_another_riders_order(): void
    {
        [, $token] = $this->riderToken();
        $order = Order::factory()->create([
            'rider_id' => Rider::factory(),
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/orders/'.$order->getKey().'/decision', [
                'decision' => 'accepted',
            ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Order was not found.');
    }

    public function test_rider_order_decision_rejects_other_identity_tokens(): void
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
            ->postJson('/api/v1/rider/orders/1/decision', [
                'decision' => 'accepted',
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
