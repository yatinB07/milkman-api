<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Page;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StorePageReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_view_active_pages(): void
    {
        [, $token] = $this->storeToken();
        $page = Page::factory()->create([
            'title' => 'Privacy Policy',
            'description' => 'Privacy content.',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/pages')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Privacy Policy');

        $this->withToken($token)
            ->getJson('/api/v1/store/pages/'.$page->getKey())
            ->assertOk()
            ->assertJsonPath('data.id', $page->getKey())
            ->assertJsonPath('data.title', 'Privacy Policy');
    }

    public function test_store_page_list_is_paginated_searchable_and_active_only(): void
    {
        [, $token] = $this->storeToken();

        Page::factory()->create(['title' => 'Milk Privacy', 'is_active' => true]);
        Page::factory()->create(['title' => 'Milk Terms', 'is_active' => true]);
        Page::factory()->create(['title' => 'Curd Terms', 'is_active' => true]);
        Page::factory()->create(['title' => 'Milk Hidden', 'is_active' => false]);

        $this->withToken($token)
            ->getJson('/api/v1/store/pages?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Milk Privacy')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_store_cannot_view_inactive_page(): void
    {
        [, $token] = $this->storeToken();
        $page = Page::factory()->create(['is_active' => false]);

        $this->withToken($token)
            ->getJson('/api/v1/store/pages/'.$page->getKey())
            ->assertNotFound()
            ->assertJsonPath('message', 'Page was not found.');
    }

    public function test_store_page_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('stores.view');
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/store/pages')
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
