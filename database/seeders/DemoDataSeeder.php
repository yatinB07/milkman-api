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

        Admin::query()->firstOrCreate([
            'email' => 'admin@milkman.test',
        ], [
            'name' => 'MilkMan Admin',
            'password' => 'password',
        ])->assignRole('super-admin');

        $store = Store::query()->firstOrCreate([
            'email' => 'store@milkman.test',
        ], [
            'title' => 'Demo Milk Store',
            'password' => 'password',
        ]);
        $store->assignRole('store-owner');

        Rider::query()->firstOrCreate([
            'email' => 'rider@milkman.test',
        ], [
            'store_id' => $store->id,
            'name' => 'Demo Rider',
            'password' => 'password',
        ])->assignRole('rider');

        Customer::query()->firstOrCreate([
            'email' => 'customer@milkman.test',
        ], [
            'name' => 'Demo Customer',
            'password' => 'password',
        ])->assignRole('customer');
    }
}
