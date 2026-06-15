<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    private const GUARD = 'sanctum';

    /** @var list<string> */
    private const ROLES = [
        'super-admin',
        'admin',
        'store-owner',
        'store-staff',
        'rider',
        'customer',
    ];

    /** @var list<string> */
    private const PERMISSIONS = [
        'settings.update',
        'stores.view',
        'stores.create',
        'stores.update',
        'stores.delete',
        'stores.manage',
        'products.manage',
        'orders.view',
        'orders.assign',
        'orders.update-status',
        'subscriptions.manage',
        'payouts.request',
        'payouts.approve',
        'users.manage',
        'riders.manage',
        'reports.view',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [];
        foreach (self::PERMISSIONS as $permission) {
            $permissions[$permission] = Permission::findOrCreate($permission, self::GUARD);
        }

        foreach (self::ROLES as $role) {
            Role::findOrCreate($role, self::GUARD);
        }

        Role::findByName('super-admin', self::GUARD)->syncPermissions(array_values($permissions));
        Role::findByName('admin', self::GUARD)->syncPermissions($this->only($permissions, [
            'stores.view',
            'stores.create',
            'stores.update',
            'products.manage',
            'orders.view',
            'orders.assign',
            'orders.update-status',
            'subscriptions.manage',
            'payouts.approve',
            'users.manage',
            'riders.manage',
            'reports.view',
        ]));
        Role::findByName('store-owner', self::GUARD)->syncPermissions($this->only($permissions, [
            'stores.view',
            'stores.update',
            'products.manage',
            'orders.view',
            'orders.assign',
            'orders.update-status',
            'subscriptions.manage',
            'payouts.request',
            'riders.manage',
            'reports.view',
        ]));
        Role::findByName('store-staff', self::GUARD)->syncPermissions($this->only($permissions, [
            'products.manage',
            'orders.view',
            'orders.assign',
            'orders.update-status',
        ]));
        Role::findByName('rider', self::GUARD)->syncPermissions($this->only($permissions, [
            'orders.view',
            'orders.update-status',
        ]));
        Role::findByName('customer', self::GUARD)->syncPermissions([]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param  array<string, Permission>  $permissions
     * @param  list<string>  $names
     * @return list<Permission>
     */
    private function only(array $permissions, array $names): array
    {
        return array_values(array_intersect_key($permissions, array_flip($names)));
    }
}
