<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Customer;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_an_image(): void
    {
        Storage::fake('public');

        $token = $this->adminToken();

        $path = $this->withToken($token)
            ->postJson('/api/v1/admin/uploads', [
                'directory' => 'stores',
                'file' => UploadedFile::fake()->image('store-logo.jpg'),
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'File uploaded successfully.')
            ->assertJsonStructure(['data' => ['path', 'url']])
            ->json('data.path');

        $this->assertStringStartsWith('storage/stores/', $path);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $path));
    }

    public function test_admin_upload_validates_payload(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)
            ->postJson('/api/v1/admin/uploads', [
                'directory' => 'unknown',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['directory', 'file']);
    }

    public function test_admin_upload_requires_admin_identity(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create(['password' => 'secret-password']);
        $customer->assignRole('customer');

        $token = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->postJson('/api/v1/admin/uploads', [
                'directory' => 'stores',
                'file' => UploadedFile::fake()->image('store-logo.jpg'),
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    private function adminToken(): string
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = Admin::factory()->create(['password' => 'secret-password']);
        $admin->assignRole('admin');

        return $this->postJson('/api/v1/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
