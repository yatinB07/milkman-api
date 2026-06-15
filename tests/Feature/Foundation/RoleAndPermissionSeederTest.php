<?php

namespace Tests\Feature\Foundation;

use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAndPermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_and_permissions_are_seeded_for_the_migration_baseline(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        foreach (['super-admin', 'admin', 'store-owner', 'store-staff', 'rider', 'customer'] as $role) {
            $this->assertTrue(Role::query()->where('name', $role)->where('guard_name', 'sanctum')->exists());
        }

        foreach (['settings.update', 'stores.manage', 'orders.assign', 'products.manage', 'payouts.approve'] as $permission) {
            $this->assertTrue(Permission::query()->where('name', $permission)->where('guard_name', 'sanctum')->exists());
        }

        $this->assertTrue(Role::findByName('super-admin', 'sanctum')->hasPermissionTo('settings.update'));
    }
}
