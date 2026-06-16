<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Rider;
use App\Models\Store;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        $admin = Admin::query()->firstOrCreate([
            'email' => 'admin@milkman.test',
        ], [
            'name' => 'MilkMan Admin',
            'password' => 'password',
        ]);
        $admin->assignRole('super-admin');
        $this->command?->info('Demo admin ready: admin@milkman.test in admins');

        $store = Store::query()->firstOrCreate([
            'email' => 'store@milkman.test',
        ], [
            'title' => 'Demo Milk Store',
            'password' => 'password',
        ]);
        $store->assignRole('store-owner');
        $this->command?->info('Demo store ready: store@milkman.test in stores');

        $rider = Rider::query()->firstOrCreate([
            'email' => 'rider@milkman.test',
        ], [
            'store_id' => $store->id,
            'name' => 'Demo Rider',
            'password' => 'password',
        ]);
        $rider->assignRole('rider');
        $this->command?->info('Demo rider ready: rider@milkman.test in riders');

        $customer = Customer::query()->firstOrCreate([
            'email' => 'customer@milkman.test',
        ], [
            'name' => 'Demo Customer',
            'password' => 'password',
        ]);
        $customer->assignRole('customer');
        $this->command?->info('Demo customer ready: customer@milkman.test in customers');
    }
}
