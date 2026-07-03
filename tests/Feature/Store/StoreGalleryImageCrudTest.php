<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Store;
use App\Models\StoreGalleryImage;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreGalleryImageCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_manage_own_gallery_images(): void
    {
        [$store, $token] = $this->storeToken();
        $image = StoreGalleryImage::factory()->create([
            'store_id' => $store->getKey(),
            'image_path' => 'stores/gallery/front.png',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/gallery-images')
            ->assertOk()
            ->assertJsonPath('data.0.image_path', 'stores/gallery/front.png');

        $this->withToken($token)
            ->getJson('/api/v1/store/gallery-images/'.$image->getKey())
            ->assertOk()
            ->assertJsonPath('data.id', $image->getKey());

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/store/gallery-images', [
                'image_path' => 'stores/gallery/counter.png',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Store gallery image created successfully.')
            ->assertJsonPath('data.store_id', $store->getKey())
            ->assertJsonPath('data.image_path', 'stores/gallery/counter.png')
            ->json('data.id');

        $this->withToken($token)
            ->putJson('/api/v1/store/gallery-images/'.$createdId, [
                'image_path' => 'stores/gallery/updated.png',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Store gallery image updated successfully.')
            ->assertJsonPath('data.image_path', 'stores/gallery/updated.png')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson('/api/v1/store/gallery-images/'.$createdId)
            ->assertOk()
            ->assertJsonPath('message', 'Store gallery image deleted successfully.');

        $this->assertSoftDeleted('store_gallery_images', ['id' => $createdId]);
    }

    public function test_store_gallery_image_list_is_paginated_and_searchable(): void
    {
        [$store, $token] = $this->storeToken();

        StoreGalleryImage::factory()->create([
            'store_id' => $store->getKey(),
            'image_path' => 'stores/gallery/milk-front.png',
        ]);
        StoreGalleryImage::factory()->create([
            'store_id' => $store->getKey(),
            'image_path' => 'stores/gallery/milk-counter.png',
        ]);
        StoreGalleryImage::factory()->create([
            'store_id' => $store->getKey(),
            'image_path' => 'stores/gallery/curd-counter.png',
        ]);
        StoreGalleryImage::factory()->create([
            'store_id' => Store::factory(),
            'image_path' => 'stores/gallery/milk-other.png',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/store/gallery-images?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.image_path', 'stores/gallery/milk-counter.png')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_store_cannot_view_another_stores_gallery_image(): void
    {
        [, $token] = $this->storeToken();
        $otherImage = StoreGalleryImage::factory()->create(['store_id' => Store::factory()]);

        $this->withToken($token)
            ->getJson('/api/v1/store/gallery-images/'.$otherImage->getKey())
            ->assertNotFound()
            ->assertJsonPath('message', 'Store gallery image was not found.');
    }

    public function test_store_gallery_image_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('stores.update');
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/store/gallery-images')
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
