<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Rider;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreOrderRiderAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_assign_own_rider_to_own_order(): void
    {
        [$store, $token] = $this->storeToken();
        $order = Order::factory()->create([
            'store_id' => $store->getKey(),
            'rider_id' => null,
            'internal_status' => 1,
        ]);
        $rider = Rider::factory()->create([
            'store_id' => $store->getKey(),
            'name' => 'Asha Rider',
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/store/orders/'.$order->getKey().'/rider', [
                'rider_id' => $rider->getKey(),
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Rider assigned successfully.')
            ->assertJsonPath('data.rider_id', $rider->getKey())
            ->assertJsonPath('data.internal_status', 3)
            ->assertJsonPath('data.rider.name', 'Asha Rider');

        $this->assertDatabaseHas('orders', [
            'id' => $order->getKey(),
            'rider_id' => $rider->getKey(),
            'internal_status' => 3,
        ]);
        $this->assertDatabaseHas('rider_notifications', [
            'rider_id' => $rider->getKey(),
            'title' => 'Order #'.$order->getKey().' Assigned.',
            'message' => 'You have an order assigned to you.',
        ]);
    }

    public function test_store_cannot_assign_another_stores_rider(): void
    {
        [$store, $token] = $this->storeToken();
        $order = Order::factory()->create(['store_id' => $store->getKey()]);
        $otherRider = Rider::factory()->create(['store_id' => Store::factory()]);

        $this->withToken($token)
            ->postJson('/api/v1/store/orders/'.$order->getKey().'/rider', [
                'rider_id' => $otherRider->getKey(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rider_id');
    }

    public function test_store_cannot_assign_rider_to_another_stores_order(): void
    {
        [$store, $token] = $this->storeToken();
        $otherOrder = Order::factory()->create(['store_id' => Store::factory()]);
        $rider = Rider::factory()->create(['store_id' => $store->getKey()]);

        $this->withToken($token)
            ->postJson('/api/v1/store/orders/'.$otherOrder->getKey().'/rider', [
                'rider_id' => $rider->getKey(),
            ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Order was not found.');
    }

    public function test_store_order_rider_assignment_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('orders.assign');
        $token = $admin->createToken('admin-test')->plainTextToken;
        $rider = Rider::factory()->create(['store_id' => Store::factory()]);

        $this->withToken($token)
            ->postJson('/api/v1/store/orders/1/rider', [
                'rider_id' => $rider->getKey(),
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    /**
     * @return array{0: Store, 1: string}
     */
    private function storeToken(): array
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $store = Store::factory()->create([
            'email' => 'store@example.test',
            'password' => Hash::make('password'),
        ]);
        $store->assignRole('store-owner');

        $token = $this->postJson('/api/v1/store/auth/login', [
            'email' => 'store@example.test',
            'password' => 'password',
        ])
            ->assertOk()
            ->json('data.token');

        return [$store, $token];
    }
}
