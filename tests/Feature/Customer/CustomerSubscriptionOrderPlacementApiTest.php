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

class CustomerSubscriptionOrderPlacementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_place_subscription_order_and_generate_delivery_dates(): void
    {
        $token = $this->customerToken(['wallet_balance' => 100]);
        $customer = Customer::query()->where('email', 'customer@example.test')->firstOrFail();
        $store = Store::factory()->create(['commission_percent' => 8, 'store_charge' => 5, 'is_active' => true]);
        $paymentMethod = PaymentMethod::factory()->create(['is_visible' => true, 'is_active' => true]);

        $response = $this->withToken($token)
            ->postJson('/api/v1/customer/subscription-orders', [
                'store_id' => $store->id,
                'payment_method_id' => $paymentMethod->id,
                'address' => 'CG Road, Ahmedabad',
                'landmark' => 'Near Metro',
                'delivery_charge' => 0,
                'coupon_id' => null,
                'coupon_amount' => 0,
                'total' => 220,
                'subtotal' => 240,
                'transaction_id' => 'SUB-TXN-1001',
                'admin_note' => 'Morning only',
                'wallet_amount' => 30,
                'customer_name' => 'Customer Demo',
                'customer_mobile' => '9999999999',
                'items' => [
                    [
                        'quantity' => 1,
                        'product_title' => 'Cow Milk 1L',
                        'discount' => 5,
                        'image_path' => 'products/cow-milk.png',
                        'price' => 55,
                        'variant_title' => '1 Litre',
                        'starts_at' => '2026-07-06',
                        'selected_days' => [0, 2],
                        'total_deliveries' => 3,
                        'time_slot' => '06:00 AM - 09:00 AM',
                    ],
                ],
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Subscription order placed successfully.')
            ->assertJsonPath('wallet_balance', '70.00')
            ->assertJsonPath('data.status', 'Pending')
            ->assertJsonPath('data.items.0.product_title', 'Cow Milk 1L')
            ->assertJsonPath('data.items.0.total_dates', '2026-07-08,2026-07-13,2026-07-15');

        $this->assertDatabaseHas('subscription_orders', [
            'customer_id' => $customer->id,
            'store_id' => $store->id,
            'transaction_id' => 'SUB-TXN-1001',
            'status' => 'Pending',
        ]);
        $this->assertDatabaseHas('subscription_order_items', [
            'product_title' => 'Cow Milk 1L',
            'selected_days' => '0,2',
            'total_dates' => '2026-07-08,2026-07-13,2026-07-15',
        ]);
        $this->assertSame('70.00', $customer->refresh()->wallet_balance);
        $this->assertTrue(WalletTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', 'Debit')
            ->where('message', 'Wallet used in subscription order #'.$response->json('data.id'))
            ->where('amount', 30)
            ->exists());
    }

    public function test_subscription_order_rejects_insufficient_wallet_balance(): void
    {
        $token = $this->customerToken(['wallet_balance' => 10]);
        $store = Store::factory()->create(['is_active' => true]);
        $paymentMethod = PaymentMethod::factory()->create(['is_visible' => true, 'is_active' => true]);

        $this->withToken($token)
            ->postJson('/api/v1/customer/subscription-orders', [
                'store_id' => $store->id,
                'payment_method_id' => $paymentMethod->id,
                'address' => 'CG Road, Ahmedabad',
                'delivery_charge' => 0,
                'coupon_amount' => 0,
                'total' => 220,
                'subtotal' => 240,
                'transaction_id' => 'SUB-TXN-1002',
                'wallet_amount' => 30,
                'customer_name' => 'Customer Demo',
                'customer_mobile' => '9999999999',
                'items' => [
                    [
                        'quantity' => 1,
                        'product_title' => 'Cow Milk 1L',
                        'price' => 55,
                        'variant_title' => '1 Litre',
                        'starts_at' => '2026-07-06',
                        'selected_days' => [0, 2],
                        'total_deliveries' => 3,
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Wallet balance is not enough for this order.');
    }

    public function test_subscription_order_routes_reject_other_identity_tokens(): void
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
            ->postJson('/api/v1/customer/subscription-orders', [
                'store_id' => $store->id,
                'payment_method_id' => $paymentMethod->id,
                'address' => 'CG Road, Ahmedabad',
                'delivery_charge' => 0,
                'coupon_amount' => 0,
                'total' => 220,
                'subtotal' => 240,
                'transaction_id' => 'SUB-TXN-1003',
                'wallet_amount' => 0,
                'customer_name' => 'Customer Demo',
                'customer_mobile' => '9999999999',
                'items' => [
                    [
                        'quantity' => 1,
                        'product_title' => 'Cow Milk 1L',
                        'price' => 55,
                        'variant_title' => '1 Litre',
                        'starts_at' => '2026-07-06',
                        'selected_days' => [0, 2],
                        'total_deliveries' => 3,
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
