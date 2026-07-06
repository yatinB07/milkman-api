<?php

namespace Tests\Feature\Store;

use App\Models\Admin;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class StoreAccountDeactivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_deactivate_own_account(): void
    {
        [$store, $token] = $this->storeToken();

        $this->withToken($token)
            ->deleteJson('/api/v1/store/account')
            ->assertOk()
            ->assertJsonPath('message', 'Store account deactivated successfully.');

        $this->assertDatabaseHas('stores', [
            'id' => $store->getKey(),
            'is_active' => false,
        ]);
        $this->assertNotSoftDeleted('stores', ['id' => $store->getKey()]);
        $this->assertSame(0, PersonalAccessToken::query()->count());

        $this->postJson('/api/v1/store/auth/login', [
            'email' => 'store@example.test',
            'password' => 'password',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'This account is inactive.');
    }

    public function test_store_account_deactivation_rejects_other_identity_tokens(): void
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
            ->deleteJson('/api/v1/store/account')
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
