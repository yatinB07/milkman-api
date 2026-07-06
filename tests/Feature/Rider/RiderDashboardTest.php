<?php

namespace Tests\Feature\Rider;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Rider;
use App\Models\SubscriptionOrder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RiderDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_can_view_dashboard_counts_for_assigned_orders(): void
    {
        [$rider, $token] = $this->riderToken();
        $otherRider = Rider::factory()->create();

        Order::factory()->create(['rider_id' => $rider->getKey(), 'status' => 'Processing']);
        Order::factory()->create(['rider_id' => $rider->getKey(), 'status' => 'Completed']);
        Order::factory()->create(['rider_id' => $otherRider->getKey(), 'status' => 'Completed']);
        SubscriptionOrder::factory()->create(['rider_id' => $rider->getKey(), 'status' => 'Processing']);
        SubscriptionOrder::factory()->create(['rider_id' => $rider->getKey(), 'status' => 'Completed']);
        SubscriptionOrder::factory()->create(['rider_id' => $otherRider->getKey(), 'status' => 'Completed']);

        $this->withToken($token)
            ->getJson('/api/v1/rider/dashboard')
            ->assertOk()
            ->assertJsonPath('data.counts.normal_orders', 2)
            ->assertJsonPath('data.counts.completed_normal_orders', 1)
            ->assertJsonPath('data.counts.subscription_orders', 2)
            ->assertJsonPath('data.counts.completed_subscription_orders', 1)
            ->assertJsonPath('data.cards.0.title', 'Normal Order')
            ->assertJsonPath('data.cards.0.report_data', 2)
            ->assertJsonPath('data.cards.1.title', 'Completed Order')
            ->assertJsonPath('data.cards.1.report_data', 1)
            ->assertJsonPath('data.cards.2.title', 'Subscription Order')
            ->assertJsonPath('data.cards.2.report_data', 2)
            ->assertJsonPath('data.cards.3.title', 'Completed Order')
            ->assertJsonPath('data.cards.3.report_data', 1)
            ->assertJsonPath('data.withdraw_limit', '0.00');
    }

    public function test_rider_dashboard_rejects_other_identity_tokens(): void
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
            ->getJson('/api/v1/rider/dashboard')
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
