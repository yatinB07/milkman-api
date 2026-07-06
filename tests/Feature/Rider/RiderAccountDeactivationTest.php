<?php

namespace Tests\Feature\Rider;

use App\Models\Admin;
use App\Models\Rider;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class RiderAccountDeactivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_can_deactivate_own_account(): void
    {
        [$rider, $token] = $this->riderToken();

        $this->withToken($token)
            ->deleteJson('/api/v1/rider/account')
            ->assertOk()
            ->assertJsonPath('message', 'Rider account deactivated successfully.');

        $this->assertDatabaseHas('riders', [
            'id' => $rider->getKey(),
            'is_active' => false,
        ]);
        $this->assertNotSoftDeleted('riders', ['id' => $rider->getKey()]);
        $this->assertSame(0, PersonalAccessToken::query()->count());

        $this->postJson('/api/v1/rider/auth/login', [
            'email' => 'rider@example.test',
            'password' => 'password',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'This account is inactive.');
    }

    public function test_rider_account_deactivation_rejects_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('orders.view');
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->deleteJson('/api/v1/rider/account')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    /**
     * @return array{0: Rider, 1: string}
     */
    private function riderToken(): array
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $rider = Rider::factory()->create([
            'email' => 'rider@example.test',
            'password' => Hash::make('password'),
        ]);
        $rider->assignRole('rider');

        $token = $this->postJson('/api/v1/rider/auth/login', [
            'email' => 'rider@example.test',
            'password' => 'password',
        ])
            ->assertOk()
            ->json('data.token');

        return [$rider, $token];
    }
}
