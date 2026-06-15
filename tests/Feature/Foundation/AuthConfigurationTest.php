<?php

namespace Tests\Feature\Foundation;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Rider;
use App\Models\Store;
use Tests\TestCase;

class AuthConfigurationTest extends TestCase
{
    public function test_auth_providers_are_registered_for_all_identity_models(): void
    {
        $this->assertSame(Admin::class, config('auth.providers.admins.model'));
        $this->assertSame(Customer::class, config('auth.providers.customers.model'));
        $this->assertSame(Store::class, config('auth.providers.stores.model'));
        $this->assertSame(Rider::class, config('auth.providers.riders.model'));
    }
}
