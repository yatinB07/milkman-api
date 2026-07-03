<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Faq;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreFaqCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_manage_own_faqs(): void
    {
        $token = $this->storeToken();
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();
        $faq = Faq::factory()->for($store)->create(['question' => 'Do you deliver daily?']);

        $this->withToken($token)
            ->getJson('/api/v1/store/faqs')
            ->assertOk()
            ->assertJsonPath('data.0.question', 'Do you deliver daily?');

        $this->withToken($token)
            ->getJson("/api/v1/store/faqs/{$faq->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $faq->id)
            ->assertJsonPath('data.question', 'Do you deliver daily?');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/store/faqs', [
                'question' => 'Can I pause delivery?',
                'answer' => 'Yes, you can pause it from your account.',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'FAQ created successfully.')
            ->assertJsonPath('data.store_id', $store->id)
            ->assertJsonPath('data.question', 'Can I pause delivery?')
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/store/faqs/{$createdId}", [
                'question' => 'Can I pause my subscription?',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'FAQ updated successfully.')
            ->assertJsonPath('data.question', 'Can I pause my subscription?')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/store/faqs/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'FAQ deleted successfully.');

        $this->assertSoftDeleted('faqs', ['id' => $createdId]);
    }

    public function test_store_faq_list_is_paginated_and_searchable(): void
    {
        $token = $this->storeToken();
        $store = Store::query()->where('email', 'store@example.test')->firstOrFail();

        Faq::factory()->for($store)->create(['question' => 'Milk delivery timing?']);
        Faq::factory()->for($store)->create(['question' => 'Milk subscription pause?']);
        Faq::factory()->for($store)->create(['question' => 'Curd delivery?']);
        Faq::factory()->create(['question' => 'Other milk question?']);

        $this->withToken($token)
            ->getJson('/api/v1/store/faqs?search=milk&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.question', 'Milk delivery timing?')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_store_cannot_view_another_stores_faq(): void
    {
        $token = $this->storeToken();
        $faq = Faq::factory()->create();

        $this->withToken($token)
            ->getJson("/api/v1/store/faqs/{$faq->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'FAQ was not found.');
    }

    public function test_store_faq_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/store/faqs')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    private function storeToken(): string
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $store = Store::factory()->create([
            'email' => 'store@example.test',
            'password' => 'secret-password',
        ]);
        $store->assignRole('store-owner');

        return $this->postJson('/api/v1/store/auth/login', [
            'email' => 'store@example.test',
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
