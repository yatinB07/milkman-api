<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Faq;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FaqCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_faqs(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');
        $store = Store::factory()->create(['title' => 'Fresh Dairy']);
        $faq = Faq::factory()->create([
            'store_id' => $store->id,
            'question' => 'When do you deliver?',
            'answer' => 'We deliver every morning.',
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/faqs')
            ->assertOk()
            ->assertJsonPath('data.0.question', 'When do you deliver?')
            ->assertJsonPath('data.0.store.id', $store->id);

        $this->withToken($token)
            ->getJson("/api/v1/admin/faqs/{$faq->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $faq->id)
            ->assertJsonPath('data.answer', 'We deliver every morning.');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/faqs', [
                'store_id' => $store->id,
                'question' => 'Can I pause delivery?',
                'answer' => 'Yes, from your subscription calendar.',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'FAQ created successfully.')
            ->assertJsonPath('data.question', 'Can I pause delivery?')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/faqs/{$createdId}", [
                'question' => 'Can I pause deliveries?',
                'answer' => 'Yes, pause deliveries from your subscription calendar.',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'FAQ updated successfully.')
            ->assertJsonPath('data.question', 'Can I pause deliveries?')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/faqs/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'FAQ deleted successfully.');

        $this->assertSoftDeleted('faqs', ['id' => $createdId]);
    }

    public function test_admin_faq_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');

        $freshStore = Store::factory()->create(['title' => 'Fresh Dairy']);
        $bakeryStore = Store::factory()->create(['title' => 'Bakery']);

        Faq::factory()->create(['store_id' => $freshStore->id, 'question' => 'When is milk delivered?']);
        Faq::factory()->create(['store_id' => $freshStore->id, 'question' => 'How is milk packed?']);
        Faq::factory()->create(['store_id' => $bakeryStore->id, 'question' => 'When is bread baked?']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/faqs?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.store.title', 'Fresh Dairy')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_faq_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('stores.manage');

        $this->withToken($token)
            ->postJson('/api/v1/admin/faqs', [
                'store_id' => null,
                'question' => '',
                'answer' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id', 'question', 'answer']);
    }

    public function test_admin_faq_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/faqs')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_faq_routes_require_stores_manage_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/faqs')
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
