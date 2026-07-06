<?php

namespace Tests\Feature\Rider;

use App\Models\Admin;
use App\Models\Rider;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionOrderItem;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RiderSubscriptionOrderCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_can_complete_assigned_subscription_order_when_all_dates_are_completed(): void
    {
        Storage::fake('public');
        [$rider, $token] = $this->riderToken();
        $order = SubscriptionOrder::factory()->create([
            'rider_id' => $rider->getKey(),
            'customer_name' => 'Mina Customer',
            'status' => 'Processing',
            'internal_status' => 4,
        ]);
        SubscriptionOrderItem::factory()->create([
            'subscription_order_id' => $order->getKey(),
            'total_dates' => '2026-07-07,2026-07-08',
            'completed_dates' => '2026-07-07,2026-07-08',
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/subscription-orders/'.$order->getKey().'/complete', [
                'signature_image' => 'data:image/png;base64,'.base64_encode('signature-bytes'),
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Subscription order completed successfully.')
            ->assertJsonPath('data.status', 'Completed')
            ->assertJsonPath('data.internal_status', 10)
            ->assertJsonPath('data.signature_path', fn (?string $path): bool => is_string($path) && str_starts_with($path, 'subscription-signatures/'));

        $order->refresh();

        $this->assertSame('Completed', $order->getAttribute('status'));
        $this->assertSame(10, $order->getAttribute('internal_status'));
        $this->assertNotNull($order->getAttribute('signature_path'));
        Storage::disk('public')->assertExists($order->getAttribute('signature_path'));

        $this->assertDatabaseHas('customer_notifications', [
            'customer_id' => $order->getAttribute('customer_id'),
            'title' => 'Subscription Order Completed!!',
            'description' => 'Mina Customer, Your Subscription Order #'.$order->getKey().' Has Been Completed.',
        ]);
        $this->assertDatabaseHas('store_notifications', [
            'store_id' => $order->getAttribute('store_id'),
            'title' => 'Subscription Order Completed!!',
            'description' => 'Subscription Order #'.$order->getKey().' Has Been Completed.',
        ]);
    }

    public function test_rider_cannot_complete_subscription_order_until_all_dates_are_completed(): void
    {
        [$rider, $token] = $this->riderToken();
        $order = SubscriptionOrder::factory()->create([
            'rider_id' => $rider->getKey(),
            'status' => 'Processing',
            'internal_status' => 4,
        ]);
        SubscriptionOrderItem::factory()->create([
            'subscription_order_id' => $order->getKey(),
            'total_dates' => '2026-07-07,2026-07-08',
            'completed_dates' => '2026-07-07',
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/subscription-orders/'.$order->getKey().'/complete', [
                'signature_image' => 'data:image/png;base64,'.base64_encode('signature-bytes'),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Please complete all delivery dates first.');
    }

    public function test_rider_cannot_complete_another_riders_subscription_order(): void
    {
        [, $token] = $this->riderToken();
        $order = SubscriptionOrder::factory()->create([
            'rider_id' => Rider::factory(),
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/rider/subscription-orders/'.$order->getKey().'/complete', [
                'signature_image' => 'data:image/png;base64,'.base64_encode('signature-bytes'),
            ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Subscription order was not found.');
    }

    public function test_rider_subscription_order_completion_rejects_other_identity_tokens(): void
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
            ->postJson('/api/v1/rider/subscription-orders/1/complete', [
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
