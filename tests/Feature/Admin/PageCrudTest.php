<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Page;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PageCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_pages(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        $page = Page::factory()->create([
            'title' => 'About MilkMan',
            'description' => 'Current about page.',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/pages')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'About MilkMan')
            ->assertJsonPath('data.0.description', 'Current about page.');

        $this->withToken($token)
            ->getJson("/api/v1/admin/pages/{$page->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $page->id)
            ->assertJsonPath('data.title', 'About MilkMan');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/pages', [
                'title' => 'Privacy Policy',
                'description' => 'Privacy details.',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Page created successfully.')
            ->assertJsonPath('data.title', 'Privacy Policy')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/pages/{$createdId}", [
                'title' => 'Privacy Notice',
                'description' => 'Updated privacy details.',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Page updated successfully.')
            ->assertJsonPath('data.title', 'Privacy Notice')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/pages/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Page deleted successfully.');

        $this->assertSoftDeleted('pages', ['id' => $createdId]);
    }

    public function test_admin_page_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        Page::factory()->create(['title' => 'Privacy Policy', 'description' => 'Privacy details']);
        Page::factory()->create(['title' => 'Privacy Notice', 'description' => 'Updated privacy details']);
        Page::factory()->create(['title' => 'Terms', 'description' => 'Terms details']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/pages?search=privacy&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Privacy Notice')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_page_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        $this->withToken($token)
            ->postJson('/api/v1/admin/pages', [
                'title' => '',
                'description' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'description']);
    }

    public function test_admin_page_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/pages')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_page_routes_require_settings_update_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/pages')
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');
    }

    private function adminTokenWithPermission(string $permission): string
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = Admin::factory()->create(['password' => 'secret-password']);
        $admin->givePermissionTo(Permission::findByName($permission, 'sanctum'));

        return $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
