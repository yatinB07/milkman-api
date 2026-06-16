<?php

namespace Tests\Feature\Foundation;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Rider;
use App\Models\Store;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_seeder_creates_secure_role_backed_identities(): void
    {
        $this->seed(DemoDataSeeder::class);
        $this->seed(DemoDataSeeder::class);

        $admin = Admin::query()->where('email', 'admin@milkman.test')->firstOrFail();
        $store = Store::query()->where('email', 'store@milkman.test')->firstOrFail();
        $rider = Rider::query()->where('email', 'rider@milkman.test')->firstOrFail();
        $customer = Customer::query()->where('email', 'customer@milkman.test')->firstOrFail();

        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertNotSame('password', $admin->password);
        $this->assertTrue($admin->hasRole('super-admin'));
        $this->assertTrue($store->hasRole('store-owner'));
        $this->assertTrue($rider->hasRole('rider'));
        $this->assertTrue($customer->hasRole('customer'));
        $this->assertTrue($rider->store()->is($store));
        $this->assertSame(1, Admin::query()->where('email', 'admin@milkman.test')->count());
        $this->assertSame(1, Store::query()->where('email', 'store@milkman.test')->count());
        $this->assertSame(1, Rider::query()->where('email', 'rider@milkman.test')->count());
        $this->assertSame(1, Customer::query()->where('email', 'customer@milkman.test')->count());
    }
}
