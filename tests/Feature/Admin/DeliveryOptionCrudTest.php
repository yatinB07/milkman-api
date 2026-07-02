<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\DeliveryOption;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DeliveryOptionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_delivery_options(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');
        $store = Store::factory()->create();
        $option = DeliveryOption::factory()->create([
            'store_id' => $store->id,
            'title' => 'Morning Delivery',
            'delivery_days' => 1,
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/delivery-options')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Morning Delivery')
            ->assertJsonPath('data.0.store.id', $store->id);

        $this->withToken($token)
            ->getJson("/api/v1/admin/delivery-options/{$option->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $option->id)
            ->assertJsonPath('data.title', 'Morning Delivery');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/delivery-options', [
                'store_id' => $store->id,
                'title' => 'Evening Delivery',
                'delivery_days' => 2,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Delivery option created successfully.')
            ->assertJsonPath('data.title', 'Evening Delivery')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/delivery-options/{$createdId}", [
                'title' => 'Late Evening Delivery',
                'delivery_days' => 3,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Delivery option updated successfully.')
            ->assertJsonPath('data.title', 'Late Evening Delivery')
            ->assertJsonPath('data.delivery_days', 3)
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/delivery-options/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Delivery option deleted successfully.');

        $this->assertSoftDeleted('delivery_options', ['id' => $createdId]);
    }

    public function test_admin_delivery_option_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');

        DeliveryOption::factory()->create(['title' => 'Morning Delivery']);
        DeliveryOption::factory()->create(['title' => 'Evening Delivery']);
        DeliveryOption::factory()->create(['title' => 'Pickup']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/delivery-options?search=delivery&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Evening Delivery')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_delivery_option_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/delivery-options', [
                'store_id' => null,
                'title' => '',
                'delivery_days' => -1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id', 'title', 'delivery_days']);
    }

    public function test_admin_delivery_option_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/delivery-options')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_delivery_option_routes_require_stores_manage_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/delivery-options')
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
