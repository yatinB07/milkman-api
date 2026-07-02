<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Store;
use App\Models\StoreGalleryImage;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StoreGalleryImageCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_store_gallery_images(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');
        $store = Store::factory()->create();
        $image = StoreGalleryImage::factory()->create([
            'store_id' => $store->id,
            'image_path' => 'stores/gallery/current.png',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/store-gallery-images')
            ->assertOk()
            ->assertJsonPath('data.0.image_path', 'stores/gallery/current.png')
            ->assertJsonPath('data.0.store.id', $store->id);

        $this->withToken($token)
            ->getJson("/api/v1/admin/store-gallery-images/{$image->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $image->id)
            ->assertJsonPath('data.image_path', 'stores/gallery/current.png');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/store-gallery-images', [
                'store_id' => $store->id,
                'image_path' => 'stores/gallery/new.png',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Store gallery image created successfully.')
            ->assertJsonPath('data.image_path', 'stores/gallery/new.png')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/store-gallery-images/{$createdId}", [
                'image_path' => 'stores/gallery/updated.png',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Store gallery image updated successfully.')
            ->assertJsonPath('data.image_path', 'stores/gallery/updated.png')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/store-gallery-images/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Store gallery image deleted successfully.');

        $this->assertSoftDeleted('store_gallery_images', ['id' => $createdId]);
    }

    public function test_admin_store_gallery_image_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');

        StoreGalleryImage::factory()->create(['image_path' => 'stores/gallery/milk-one.png']);
        StoreGalleryImage::factory()->create(['image_path' => 'stores/gallery/milk-two.png']);
        StoreGalleryImage::factory()->create(['image_path' => 'stores/gallery/curd.png']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/store-gallery-images?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.image_path', 'stores/gallery/milk-one.png')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_store_gallery_image_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/store-gallery-images', [
                'store_id' => null,
                'image_path' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id', 'image_path']);
    }

    public function test_admin_store_gallery_image_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/store-gallery-images')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_store_gallery_image_routes_require_stores_manage_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/store-gallery-images')
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
