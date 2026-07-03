<?php

namespace Tests\Feature\Customer;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Store;
use App\Models\WalletTransaction;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderPlacementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_place_normal_order_and_debit_wallet(): void
    {
        $token = $this->customerToken(['wallet_balance' => 75]);
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $store = Store::factory()->create(['commission_percent' => 8, 'store_charge' => 5, 'is_active' => true]);
        $paymentMethod = PaymentMethod::factory()->create(['is_visible' => true, 'is_active' => true]);

        $response = $this->withToken($token)
            ->postJson('/api/v1/customer/orders', [
                'store_id' => $store->id,
                'payment_method_id' => $paymentMethod->id,
                'address' => 'CG Road, Ahmedabad',
                'landmark' => 'Near Metro',
                'delivery_charge' => 10,
                'coupon_id' => null,
                'coupon_amount' => 0,
                'total' => 110,
                'subtotal' => 120,
                'transaction_id' => 'TXN-1001',
                'admin_note' => 'Leave at door',
                'wallet_amount' => 25,
                'customer_name' => 'Customer Demo',
                'customer_mobile' => '9999999999',
                'time_slot' => '06:00 AM - 09:00 AM',
                'order_type' => 'Delivery',
                'items' => [
                    [
                        'quantity' => 2,
                        'product_title' => 'Cow Milk 1L',
                        'discount' => 5,
                        'image_path' => 'products/cow-milk.png',
                        'price' => 55,
                        'variant_title' => '1 Litre',
                    ],
                ],
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Order placed successfully.')
            ->assertJsonPath('wallet_balance', '50.00')
            ->assertJsonPath('data.status', 'Pending')
            ->assertJsonPath('data.items.0.product_title', 'Cow Milk 1L');

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'store_id' => $store->id,
            'transaction_id' => 'TXN-1001',
            'status' => 'Pending',
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_title' => 'Cow Milk 1L',
            'quantity' => 2,
        ]);
        $this->assertSame('50.00', $customer->refresh()->wallet_balance);
        $this->assertTrue(WalletTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', 'Debit')
            ->where('amount', 25)
            ->exists());
    }

    public function test_customer_order_rejects_insufficient_wallet_balance(): void
    {
        $token = $this->customerToken(['wallet_balance' => 10]);
        $store = Store::factory()->create(['is_active' => true]);
        $paymentMethod = PaymentMethod::factory()->create(['is_visible' => true, 'is_active' => true]);

        $this->withToken($token)
            ->postJson('/api/v1/customer/orders', [
                'store_id' => $store->id,
                'payment_method_id' => $paymentMethod->id,
                'address' => 'CG Road, Ahmedabad',
                'delivery_charge' => 10,
                'coupon_amount' => 0,
                'total' => 110,
                'subtotal' => 120,
                'transaction_id' => 'TXN-1002',
                'wallet_amount' => 25,
                'customer_name' => 'Customer Demo',
                'customer_mobile' => '9999999999',
                'order_type' => 'Delivery',
                'items' => [
                    [
                        'quantity' => 1,
                        'product_title' => 'Cow Milk 1L',
                        'price' => 55,
                        'variant_title' => '1 Litre',
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Wallet balance is not enough for this order.');
    }

    public function test_customer_order_routes_reject_other_identity_tokens(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $admin = Admin::factory()->create(['email' => 'admin@example.test', 'password' => 'secret-password']);
        $admin->assignRole('super-admin');
        $store = Store::factory()->create(['is_active' => true]);
        $paymentMethod = PaymentMethod::factory()->create(['is_visible' => true, 'is_active' => true]);

        $token = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->json('data.token');

        $this->withToken($token)
            ->postJson('/api/v1/customer/orders', [
                'store_id' => $store->id,
                'payment_method_id' => $paymentMethod->id,
                'address' => 'CG Road, Ahmedabad',
                'delivery_charge' => 10,
                'coupon_amount' => 0,
                'total' => 110,
                'subtotal' => 120,
                'transaction_id' => 'TXN-1003',
                'wallet_amount' => 0,
                'customer_name' => 'Customer Demo',
                'customer_mobile' => '9999999999',
                'order_type' => 'Delivery',
                'items' => [
                    [
                        'quantity' => 1,
                        'product_title' => 'Cow Milk 1L',
                        'price' => 55,
                        'variant_title' => '1 Litre',
                    ],
                ],
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'This token cannot access the requested identity area.');
    }

    /** @param array<string, mixed> $attributes */
    private function customerToken(array $attributes = []): string
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = Customer::factory()->create([
            'email' => 'customer@example.test',
            'password' => 'secret-password',
            ...$attributes,
        ]);
        $customer->assignRole('customer');

        return $this->postJson('/api/v1/customer/auth/login', [
            'email' => 'customer@example.test',
            'password' => 'secret-password',
        ])->json('data.token');
    }
}
