<?php

namespace Tests\Feature\Rider;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Rider;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RiderOrderCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_can_complete_assigned_delivery_order_with_signature(): void
    {
        Storage::fake('public');
        [$rider, $token] = $this->riderToken();
        $order = Order::factory()->create([
            'rider_id' => $rider->getKey(),
            'customer_name' => 'Mina Customer',
            'order_type' => 'Delivery',
            'status' => 'On Route',
            'internal_status' => 4,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/orders/'.$order->getKey().'/complete', [
                'signature_image' => 'data:image/png;base64,'.base64_encode('signature-bytes'),
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Order completed successfully.')
            ->assertJsonPath('data.status', 'Completed')
            ->assertJsonPath('data.internal_status', 7)
            ->assertJsonPath('data.rider_id', $rider->getKey())
            ->assertJsonPath('data.signature_path', fn (?string $path): bool => is_string($path) && str_starts_with($path, 'signatures/'));

        $order->refresh();

        $this->assertSame('Completed', $order->getAttribute('status'));
        $this->assertSame(7, $order->getAttribute('internal_status'));
        $this->assertNotNull($order->getAttribute('signature_path'));
        Storage::disk('public')->assertExists($order->getAttribute('signature_path'));

        $this->assertDatabaseHas('customer_notifications', [
            'customer_id' => $order->getAttribute('customer_id'),
            'title' => 'Order Completed!!',
            'description' => 'Mina Customer, Your Order #'.$order->getKey().' Has Been Completed.',
        ]);
        $this->assertDatabaseHas('store_notifications', [
            'store_id' => $order->getAttribute('store_id'),
            'title' => 'Order Completed!!',
            'description' => 'Order #'.$order->getKey().' Has Been Completed.',
        ]);
    }

    public function test_rider_cannot_complete_non_delivery_order(): void
    {
        [$rider, $token] = $this->riderToken();
        $order = Order::factory()->create([
            'rider_id' => $rider->getKey(),
            'order_type' => 'Self Pickup',
            'status' => 'On Route',
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/orders/'.$order->getKey().'/complete', [
                'signature_image' => 'data:image/png;base64,'.base64_encode('signature-bytes'),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Only delivery orders can be completed by the rider.');
    }

    public function test_rider_cannot_complete_another_riders_order(): void
    {
        [, $token] = $this->riderToken();
        $order = Order::factory()->create([
            'rider_id' => Rider::factory(),
            'order_type' => 'Delivery',
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/orders/'.$order->getKey().'/complete', [
                'signature_image' => 'data:image/png;base64,'.base64_encode('signature-bytes'),
            ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Order was not found.');
    }

    public function test_rider_order_completion_rejects_other_identity_tokens(): void
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
            ->postJson('/api/v1/rider/orders/1/complete', [
                'signature_image' => 'data:image/png;base64,'.base64_encode('signature-bytes'),
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
