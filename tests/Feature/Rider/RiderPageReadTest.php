<?php

namespace Tests\Feature\Rider;

use App\Models\Admin;
use App\Models\Page;
use App\Models\Rider;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RiderPageReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_can_list_active_pages_with_pagination_and_search(): void
    {
        [, $token] = $this->riderToken();

        Page::factory()->create(['title' => 'Privacy Policy', 'description' => 'Privacy details', 'is_active' => true]);
        Page::factory()->create(['title' => 'Terms', 'description' => 'Account terms', 'is_active' => true]);
        Page::factory()->create(['title' => 'Inactive Privacy', 'description' => 'Hidden page', 'is_active' => false]);

        $this->withToken($token)
            ->getJson('/api/v1/rider/pages?search=privacy&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Privacy Policy')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_rider_can_view_active_page(): void
    {
        [, $token] = $this->riderToken();
        $page = Page::factory()->create([
            'title' => 'Delivery Help',
            'description' => 'Rider delivery help content.',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/rider/pages/'.$page->getKey())
            ->assertOk()
            ->assertJsonPath('data.id', $page->getKey())
            ->assertJsonPath('data.title', 'Delivery Help')
            ->assertJsonPath('data.description', 'Rider delivery help content.');
    }

    public function test_rider_cannot_view_inactive_page(): void
    {
        [, $token] = $this->riderToken();
        $page = Page::factory()->create(['is_active' => false]);

        $this->withToken($token)
            ->getJson('/api/v1/rider/pages/'.$page->getKey())
            ->assertNotFound()
            ->assertJsonPath('message', 'Page was not found.');
    }

    public function test_rider_pages_reject_other_identity_tokens(): void
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
            ->getJson('/api/v1/rider/pages')
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
