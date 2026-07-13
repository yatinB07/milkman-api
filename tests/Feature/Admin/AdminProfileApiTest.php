<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_safe_profile_data(): void
    {
        [$admin, $token] = $this->adminToken([
            'name' => 'Primary Admin',
            'username' => 'root-admin',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/profile')
            ->assertOk()
            ->assertJsonPath('data.id', $admin->getKey())
            ->assertJsonPath('data.name', 'Primary Admin')
            ->assertJsonPath('data.username', 'root-admin')
            ->assertJsonMissingPath('data.password');
    }

    public function test_admin_can_update_profile_username(): void
    {
        [$admin, $token] = $this->adminToken([
            'username' => 'old-admin',
            'password' => 'old-secret-password',
        ]);

        $this->withToken($token)
            ->putJson('/api/v1/admin/profile', [
                'name' => 'Updated Admin',
                'username' => 'updated-admin',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Admin profile updated successfully.')
            ->assertJsonPath('data.id', $admin->getKey())
            ->assertJsonPath('data.name', 'Updated Admin')
            ->assertJsonPath('data.username', 'updated-admin')
            ->assertJsonMissingPath('data.password');

        $admin->refresh();

        $this->assertSame('Updated Admin', $admin->getAttribute('name'));
        $this->assertSame('updated-admin', $admin->getAttribute('username'));
        $this->assertTrue(Hash::check('old-secret-password', $admin->getAttribute('password')));
    }

    public function test_admin_can_update_profile_without_changing_password(): void
    {
        [$admin, $token] = $this->adminToken([
            'username' => 'old-admin',
            'password' => 'old-secret-password',
        ]);

        $this->withToken($token)
            ->putJson('/api/v1/admin/profile', [
                'name' => 'Updated Admin',
                'username' => 'updated-admin',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Admin')
            ->assertJsonPath('data.username', 'updated-admin');

        $admin->refresh();

        $this->assertSame('Updated Admin', $admin->getAttribute('name'));
        $this->assertSame('updated-admin', $admin->getAttribute('username'));
        $this->assertTrue(Hash::check('old-secret-password', $admin->getAttribute('password')));
    }

    public function test_admin_profile_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create([
            'email' => 'customer@example.test',
            'password' => Hash::make('password'),
        ]);
        $customer->assignRole('customer');
        $token = $customer->createToken('customer-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/profile')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_can_update_password_with_current_password(): void
    {
        [$admin, $token] = $this->adminToken([
            'password' => 'old-secret-password',
        ]);

        $this->withToken($token)
            ->putJson('/api/v1/admin/password', [
                'current_password' => 'old-secret-password',
                'password' => 'new-secret-password',
                'password_confirmation' => 'new-secret-password',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Admin password updated successfully.')
            ->assertJsonMissingPath('data.password');

        $admin->refresh();

        $this->assertTrue(Hash::check('new-secret-password', $admin->getAttribute('password')));
        $this->assertFalse(Hash::check('old-secret-password', $admin->getAttribute('password')));
    }

    public function test_admin_password_update_rejects_wrong_current_password(): void
    {
        [, $token] = $this->adminToken([
            'password' => 'old-secret-password',
        ]);

        $this->withToken($token)
            ->putJson('/api/v1/admin/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-secret-password',
                'password_confirmation' => 'new-secret-password',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The current password is incorrect.');
    }

    public function test_admin_profile_update_validates_input(): void
    {
        [, $token] = $this->adminToken();

        $this->withToken($token)
            ->putJson('/api/v1/admin/profile', [
                'name' => '',
                'username' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'username']);
    }

    public function test_admin_password_update_validates_input(): void
    {
        [, $token] = $this->adminToken();

        $this->withToken($token)
            ->putJson('/api/v1/admin/password', [
                'current_password' => '',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password', 'password']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{0: Admin, 1: string}
     */
    private function adminToken(array $attributes = []): array
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = Admin::factory()->create(array_merge([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ], $attributes));
        $admin->assignRole('admin');

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => $attributes['password'] ?? 'password',
        ])
            ->assertOk()
            ->json('data.token');

        return [$admin, $token];
    }
}
