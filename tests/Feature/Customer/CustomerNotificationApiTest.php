<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\CustomerNotification;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerNotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_own_notifications(): void
    {
        $token = $this->customerToken();
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $otherCustomer = Customer::factory()->create();

        CustomerNotification::factory()->for($customer)->create([
            'title' => 'Order accepted',
            'description' => 'Your milk order is accepted.',
            'notified_at' => now()->subMinutes(2),
        ]);
        CustomerNotification::factory()->for($customer)->create([
            'title' => 'Wallet credited',
            'description' => 'Your wallet was credited.',
            'notified_at' => now()->subMinute(),
        ]);
        CustomerNotification::factory()->for($otherCustomer)->create([
            'title' => 'Other wallet credited',
            'description' => 'Other customer notification.',
            'notified_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/customer/notifications?search=wallet&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Wallet credited')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_customer_notification_list_returns_empty_page_when_none_exist(): void
    {
        $token = $this->customerToken();

        $this->withToken($token)
            ->getJson('/api/v1/customer/notifications')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_customer_notification_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/customer/notifications')
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
