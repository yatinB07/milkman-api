<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\Store;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SettingCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_settings(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');
        $store = Store::factory()->create(['title' => 'Milky Way Central']);

        $setting = Setting::factory()->for($store, 'primaryStore')->create([
            'web_name' => 'MilkMan Demo',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'sms_type' => 'Msg91',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/settings')
            ->assertOk()
            ->assertJsonPath('data.0.web_name', 'MilkMan Demo')
            ->assertJsonPath('data.0.primary_store.title', 'Milky Way Central');

        $this->withToken($token)
            ->getJson("/api/v1/admin/settings/{$setting->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $setting->id)
            ->assertJsonPath('data.currency', 'INR');

        $createdId = $this->withToken($token)
            ->postJson('/api/v1/admin/settings', [
                'web_name' => 'MilkMan Live',
                'web_logo_path' => 'images/website/logo.png',
                'timezone' => 'UTC',
                'currency' => 'USD',
                'primary_store_id' => $store->id,
                'customer_onesignal_key' => 'customer-app-id',
                'customer_onesignal_hash' => 'customer-rest-key',
                'delivery_onesignal_key' => 'delivery-app-id',
                'delivery_onesignal_hash' => 'delivery-rest-key',
                'store_onesignal_key' => 'store-app-id',
                'store_onesignal_hash' => 'store-rest-key',
                'signup_credit' => 25,
                'referral_credit' => 15,
                'store_withdrawal_limit' => 500,
                'show_dark_mode' => true,
                'google_maps_key' => 'google-key',
                'sms_type' => 'Twilio',
                'message_auth_key' => 'msg91-auth',
                'otp_template_id' => 'otp-template',
                'twilio_account_sid' => 'twilio-sid',
                'twilio_auth_token' => 'twilio-token',
                'twilio_number' => '+15551234567',
                'otp_auth_token' => 'Yes',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Setting created successfully.')
            ->assertJsonPath('data.web_name', 'MilkMan Live')
            ->assertJsonPath('data.store_withdrawal_limit', '500.00')
            ->assertJsonPath('data.primary_store.id', $store->id)
            ->json('data.id');

        $this->withToken($token)
            ->putJson("/api/v1/admin/settings/{$createdId}", [
                'web_name' => 'MilkMan Live Updated',
                'currency' => 'GBP',
                'store_withdrawal_limit' => 250,
                'show_dark_mode' => false,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Setting updated successfully.')
            ->assertJsonPath('data.web_name', 'MilkMan Live Updated')
            ->assertJsonPath('data.currency', 'GBP')
            ->assertJsonPath('data.store_withdrawal_limit', '250.00')
            ->assertJsonPath('data.show_dark_mode', false);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/settings/{$createdId}")
            ->assertOk()
            ->assertJsonPath('message', 'Setting deleted successfully.');

        $this->assertSoftDeleted('settings', ['id' => $createdId]);
    }

    public function test_admin_setting_list_is_paginated_and_searchable(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        Setting::factory()->create(['web_name' => 'MilkMan Demo', 'currency' => 'INR', 'created_at' => now()->subMinutes(2)]);
        Setting::factory()->create(['web_name' => 'MilkMan Live', 'currency' => 'USD', 'created_at' => now()->subMinute()]);
        Setting::factory()->create(['web_name' => 'Grocery App', 'currency' => 'EUR', 'created_at' => now()]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/settings?search=milkman&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.web_name', 'MilkMan Live')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_setting_create_validates_payload(): void
    {
        $token = $this->adminTokenWithPermission('settings.update');

        $this->withToken($token)
            ->postJson('/api/v1/admin/settings', [
                'web_name' => '',
                'timezone' => '',
                'currency' => '',
                'primary_store_id' => 999,
                'signup_credit' => -1,
                'referral_credit' => -1,
                'store_withdrawal_limit' => -1,
                'show_dark_mode' => 'not-boolean',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'web_name',
                'timezone',
                'currency',
                'primary_store_id',
                'signup_credit',
                'referral_credit',
                'store_withdrawal_limit',
                'show_dark_mode',
            ]);
    }

    public function test_admin_setting_routes_require_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/settings')
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    public function test_admin_setting_routes_require_settings_update_permission(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['password' => 'secret-password']);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/settings')
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');
    }

    private function adminTokenWithPermission(string $permission): string
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = Admin::factory()->create(['password' => 'secret-password']);
        $admin->givePermissionTo(Permission::findByName($permission, 'sanctum'));

        return $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
