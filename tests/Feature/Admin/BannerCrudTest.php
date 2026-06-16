<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Banner;
use App\Models\Customer;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BannerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_banners(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        Banner::factory()->create([
            'title' => 'Current home banner',
            'image_path' => 'banners/current.png',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/banners')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Current home banner')
            ->assertJsonPath('data.0.image_path', 'banners/current.png');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/banners', [
                'title' => 'New home banner',
                'image_path' => 'banners/new.png',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Banner created successfully.')
            ->assertJsonPath('data.title', 'New home banner')
            ->assertJsonPath('data.image_path', 'banners/new.png')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/banners/{$createdId}", [
                'title' => 'Updated home banner',
                'image_path' => 'banners/updated.png',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Banner updated successfully.')
            ->assertJsonPath('data.title', 'Updated home banner')
            ->assertJsonPath('data.image_path', 'banners/updated.png')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/banners/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Banner deleted successfully.');

        $this->assertSoftDeleted('banners', ['id' => $createdId]);
    }

    public function test_admin_banner_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        Banner::factory()->create(['title' => 'Summer banner', 'image_path' => 'banners/one.png']);
        Banner::factory()->create(['title' => 'Summer sale banner', 'image_path' => 'banners/two.png']);
        Banner::factory()->create(['title' => 'Winter banner', 'image_path' => 'banners/three.png']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/banners?search=summer&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Summer sale banner')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_banner_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        $this->withToken($token)
            ->postJson('/api/v1/admin/banners', [
                'title' => '',
                'image_path' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'image_path']);
    }

    public function test_admin_banner_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/banners')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_banner_routes_require_settings_update_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/banners')
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
