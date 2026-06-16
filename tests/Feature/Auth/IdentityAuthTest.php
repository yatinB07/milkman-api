<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Rider;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class IdentityAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_identity_can_login_and_receive_profile_roles_permissions_and_token(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $identities = [
            'admin' => Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password'])->assignRole('super-admin'),
            'customer' => Customer::factory()->create(['email' => 'customer@example.test', 'password' => 'secret-password'])->assignRole('customer'),
            'store' => Store::factory()->create(['email' => 'store@example.test', 'password' => 'secret-password'])->assignRole('store-owner'),
            'rider' => Rider::factory()->create(['email' => 'rider@example.test', 'password' => 'secret-password'])->assignRole('rider'),
        ];

        foreach ($identities as $type => $identity) {
            $response = $this->postJson("/api/v1/{$type}/auth/login", [
                'email' => $identity->email,
                'password' => 'secret-password',
            ]);

            $response
                ->assertOk()
                ->assertJsonPath('data.user.type', $type)
                ->assertJsonPath('data.user.id', $identity->id)
                ->assertJsonPath('data.user.email', $identity->email)
                ->assertJsonStructure([
                    'data' => [
                        'token',
                        'user' => [
                            'id',
                            'type',
                            'name',
                            'email',
                            'roles',
                            'permissions',
                        ],
                    ],
                ]);

            $this->assertNotEmpty($response->json('data.token'));
            $this->assertNotEmpty($response->json('data.user.roles'));
        }
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ]);

        $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid credentials.');
    }

    public function test_login_rejects_inactive_identity(): void
    {
        Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'secret-password',
            'is_active' => false,
        ]);

        $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->assertForbidden()
            ->assertJsonPath('message', 'This account is inactive.');
    }

    public function test_authenticated_identity_can_fetch_profile_and_logout(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ]);
        $admin->assignRole('super-admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.type', 'admin')
            ->assertJsonPath('data.user.email', 'admin@example.test')
            ->assertJsonPath('data.user.permissions.0', 'settings.update');

        $this->withToken($token)
            ->postJson('/api/v1/admin/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');

        $this->assertSame(0, PersonalAccessToken::query()->count());
    }

    public function test_identity_token_cannot_be_used_on_another_identity_profile_route(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ]);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/customer/auth/me')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }
}
