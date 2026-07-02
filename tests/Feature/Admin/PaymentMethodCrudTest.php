<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\PaymentMethod;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PaymentMethodCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_payment_methods(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        $method = PaymentMethod::factory()->create([
            'title' => 'Cash on Delivery',
            'image_path' => 'payments/cod.png',
            'attributes' => ['code' => 'cod'],
            'subtitle' => 'Pay on arrival',
            'is_visible' => true,
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/payment-methods')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Cash on Delivery')
            ->assertJsonPath('data.0.attributes.code', 'cod');

        $this->withToken($token)
            ->getJson("/api/v1/admin/payment-methods/{$method->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $method->id)
            ->assertJsonPath('data.title', 'Cash on Delivery');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/payment-methods', [
                'title' => 'Wallet',
                'image_path' => 'payments/wallet.png',
                'attributes' => ['code' => 'wallet'],
                'subtitle' => 'Pay with wallet balance',
                'is_visible' => true,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Payment method created successfully.')
            ->assertJsonPath('data.title', 'Wallet')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/payment-methods/{$createdId}", [
                'title' => 'Customer Wallet',
                'attributes' => ['code' => 'customer-wallet'],
                'subtitle' => 'Use wallet balance',
                'is_visible' => false,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Payment method updated successfully.')
            ->assertJsonPath('data.title', 'Customer Wallet')
            ->assertJsonPath('data.attributes.code', 'customer-wallet')
            ->assertJsonPath('data.is_visible', false)
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/payment-methods/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Payment method deleted successfully.');

        $this->assertSoftDeleted('payment_methods', ['id' => $createdId]);
    }

    public function test_admin_payment_method_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        PaymentMethod::factory()->create(['title' => 'Cash on Delivery', 'subtitle' => 'cash payment']);
        PaymentMethod::factory()->create(['title' => 'Cash Wallet', 'subtitle' => 'wallet payment']);
        PaymentMethod::factory()->create(['title' => 'Card', 'subtitle' => 'card payment']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/payment-methods?search=cash&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Cash Wallet')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_payment_method_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        $this->withToken($token)
            ->postJson('/api/v1/admin/payment-methods', [
                'title' => '',
                'attributes' => 'not-an-array',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'attributes']);
    }

    public function test_admin_payment_method_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/payment-methods')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_payment_method_routes_require_settings_update_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/payment-methods')
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
