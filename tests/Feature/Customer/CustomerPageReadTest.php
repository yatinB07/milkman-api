<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Page;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerPageReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_active_pages_with_pagination_and_search(): void
    {
        [, $token] = $this->customerToken();

        Page::factory()->create(['title' => 'Privacy Policy', 'description' => 'Privacy details', 'is_active' => true]);
        Page::factory()->create(['title' => 'Terms', 'description' => 'Account terms', 'is_active' => true]);
        Page::factory()->create(['title' => 'Inactive Privacy', 'description' => 'Hidden page', 'is_active' => false]);

        $this->withToken($token)
            ->getJson('/api/v1/customer/pages?search=privacy&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Privacy Policy')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_customer_can_view_active_page(): void
    {
        [, $token] = $this->customerToken();
        $page = Page::factory()->create([
            'title' => 'Delivery Help',
            'description' => 'Customer delivery help content.',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/customer/pages/'.$page->getKey())
            ->assertOk()
            ->assertJsonPath('data.id', $page->getKey())
            ->assertJsonPath('data.title', 'Delivery Help')
            ->assertJsonPath('data.description', 'Customer delivery help content.');
    }

    public function test_customer_cannot_view_inactive_page(): void
    {
        [, $token] = $this->customerToken();
        $page = Page::factory()->create(['is_active' => false]);

        $this->withToken($token)
            ->getJson('/api/v1/customer/pages/'.$page->getKey())
            ->assertNotFound()
            ->assertJsonPath('message', 'Page was not found.');
    }

    public function test_customer_pages_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/customer/pages')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    /**
     * @return array{0: Customer, 1: string}
     */
    private function customerToken(): array
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $customer = Customer::factory()->create([
            'email' => 'customer@example.test',
            'password' => Hash::make('password'),
        ]);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => 'customer@example.test',
            'password' => 'password',
        ])
            ->assertOk()
            ->json('data.token');

        return [$customer, $token];
    }
}
