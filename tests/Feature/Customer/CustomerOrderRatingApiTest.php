<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Order;
use App\Models\SubscriptionOrder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderRatingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_rate_own_completed_order(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $order = Order::factory()->for($customer)->create([
            'status' => 'Completed',
            'is_rated' => false,
            'total_rating' => 0,
            'rating_text' => null,
            'reviewed_at' => null,
        ]);

        $this->withToken($token)
            ->postJson("/api/v1/customer/orders/{$order->id}/rating", [
                'total_rating' => 5,
                'rating_text' => 'Fresh delivery and good packaging.',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Rating submitted successfully.')
            ->assertJsonPath('data.is_rated', true)
            ->assertJsonPath('data.total_rating', 5)
            ->assertJsonPath('data.rating_text', 'Fresh delivery and good packaging.');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'is_rated' => true,
            'total_rating' => 5,
            'rating_text' => 'Fresh delivery and good packaging.',
        ]);
    }

    public function test_customer_can_rate_own_completed_subscription_order(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $order = SubscriptionOrder::factory()->for($customer)->create([
            'status' => 'Completed',
            'is_rated' => false,
            'total_rating' => 0,
            'rating_text' => null,
            'reviewed_at' => null,
        ]);

        $this->withToken($token)
            ->postJson("/api/v1/customer/subscription-orders/{$order->id}/rating", [
                'total_rating' => 4,
                'rating_text' => 'Subscription arrived as planned.',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Rating submitted successfully.')
            ->assertJsonPath('data.is_rated', true)
            ->assertJsonPath('data.total_rating', 4)
            ->assertJsonPath('data.rating_text', 'Subscription arrived as planned.');

        $this->assertDatabaseHas('subscription_orders', [
            'id' => $order->id,
            'is_rated' => true,
            'total_rating' => 4,
            'rating_text' => 'Subscription arrived as planned.',
        ]);
    }

    public function test_rating_requires_valid_payload(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $order = Order::factory()->for($customer)->create(['status' => 'Completed']);

        $this->withToken($token)
            ->postJson("/api/v1/customer/orders/{$order->id}/rating", [
                'total_rating' => 6,
                'rating_text' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['total_rating', 'rating_text']);
    }

    public function test_customer_cannot_rate_another_customers_order(): void
    {
        $token = $this->customerToken();
        $order = Order::factory()->create(['status' => 'Completed']);

        $this->withToken($token)
            ->postJson("/api/v1/customer/orders/{$order->id}/rating", [
                'total_rating' => 5,
                'rating_text' => 'Great.',
            ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Order was not found.');
    }

    public function test_customer_order_rating_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');
        $order = Order::factory()->create(['status' => 'Completed']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->postJson("/api/v1/customer/orders/{$order->id}/rating", [
                'total_rating' => 5,
                'rating_text' => 'Great.',
            ])
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
