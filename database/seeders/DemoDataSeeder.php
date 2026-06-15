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

        Admin::factory()->create([
            'name' => 'MilkMan Admin',
            'email' => 'admin@milkman.test',
        ])->assignRole('super-admin');

        $store = Store::factory()->create([
            'title' => 'Demo Milk Store',
            'email' => 'store@milkman.test',
        ]);
        $store->assignRole('store-owner');

        Rider::factory()->for($store)->create([
            'name' => 'Demo Rider',
            'email' => 'rider@milkman.test',
        ])->assignRole('rider');

        Customer::factory()->create([
            'name' => 'Demo Customer',
            'email' => 'customer@milkman.test',
        ])->assignRole('customer');
    }
}
