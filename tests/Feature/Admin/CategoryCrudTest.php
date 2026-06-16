<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Customer;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_categories(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');

        Category::factory()->create(['title' => 'Milk', 'is_active' => true]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/categories')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Milk');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/categories', [
                'title' => 'Curd',
                'image_path' => 'categories/curd.png',
                'cover_path' => 'categories/covers/curd.png',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Category created successfully.')
            ->assertJsonPath('data.title', 'Curd')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/categories/{$createdId}", [
                'title' => 'Fresh Curd',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Category updated successfully.')
            ->assertJsonPath('data.title', 'Fresh Curd')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/categories/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Category deleted successfully.');

        $this->assertDatabaseMissing('categories', ['id' => $createdId]);
    }

    public function test_admin_category_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('products.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/categories', [
                'title' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }

    public function test_admin_category_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/categories')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_category_routes_require_products_manage_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/categories')
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
