<?php

namespace Tests\Feature\Rider;

use App\Models\Admin;
use App\Models\Rider;
use App\Models\RiderNotification;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RiderNotificationReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_can_list_own_notifications_with_pagination_and_search(): void
    {
        [$rider, $token] = $this->riderToken();
        $otherRider = Rider::factory()->create();

        RiderNotification::factory()->create([
            'rider_id' => $rider->getKey(),
            'title' => 'Fresh assignment',
            'message' => 'You have a fresh delivery assignment.',
        ]);
        RiderNotification::factory()->create([
            'rider_id' => $rider->getKey(),
            'title' => 'Route update',
            'message' => 'The customer changed their landmark.',
        ]);
        RiderNotification::factory()->create([
            'rider_id' => $otherRider->getKey(),
            'title' => 'Fresh assignment for another rider',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/rider/notifications?search=fresh&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Fresh assignment')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_rider_can_view_own_notification(): void
    {
        [$rider, $token] = $this->riderToken();
        $notification = RiderNotification::factory()->create([
            'rider_id' => $rider->getKey(),
            'title' => 'Order assigned',
            'message' => 'You have an order assigned to you.',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/rider/notifications/'.$notification->getKey())
            ->assertOk()
            ->assertJsonPath('data.id', $notification->getKey())
            ->assertJsonPath('data.title', 'Order assigned')
            ->assertJsonPath('data.message', 'You have an order assigned to you.');
    }

    public function test_rider_cannot_view_another_riders_notification(): void
    {
        [, $token] = $this->riderToken();
        $notification = RiderNotification::factory()->create([
            'rider_id' => Rider::factory(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/rider/notifications/'.$notification->getKey())
            ->assertNotFound()
            ->assertJsonPath('message', 'Rider notification was not found.');
    }

    public function test_rider_notifications_reject_other_identity_tokens(): void
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
            ->getJson('/api/v1/rider/notifications')
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
