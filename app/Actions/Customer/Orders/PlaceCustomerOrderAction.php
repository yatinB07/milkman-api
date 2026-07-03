<?php

namespace App\Actions\Customer\Orders;

use App\Data\Customer\CustomerOrderData;
use App\Exceptions\Customer\InsufficientWalletBalanceException;
use App\Models\Customer;
use App\Models\Order;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderRepository;
use App\Repositories\StoreRepository;
use App\Repositories\WalletTransactionRepository;
use Illuminate\Support\Facades\DB;

class PlaceCustomerOrderAction
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly CustomerRepository $customers,
        private readonly StoreRepository $stores,
        private readonly WalletTransactionRepository $walletTransactions,
    ) {}

    public function execute(Customer $customer, CustomerOrderData $data): Order
    {
        if ((float) $customer->getAttribute('wallet_balance') < $data->walletAmount) {
            throw new InsufficientWalletBalanceException;
        }

        $store = $this->stores->findActiveForCart($data->storeId);

        return DB::transaction(function () use ($customer, $data, $store): Order {
            $order = $this->orders->createWithItems([
                'store_id' => $data->storeId,
                'customer_id' => $customer->getKey(),
                'ordered_at' => now(),
                'payment_method_id' => $data->paymentMethodId,
                'address' => $data->address,
                'landmark' => $data->landmark,
                'delivery_charge' => $data->deliveryCharge,
                'coupon_id' => $data->couponId,
                'coupon_amount' => $data->couponAmount,
                'total' => $data->total,
                'subtotal' => $data->subtotal,
                'transaction_id' => $data->transactionId,
                'admin_note' => $data->adminNote,
                'wallet_amount' => $data->walletAmount,
                'customer_name' => $data->customerName,
                'customer_mobile' => $data->customerMobile,
                'status' => 'Pending',
                'time_slot' => $data->timeSlot,
                'order_type' => $data->orderType,
                'commission_percent' => $store->getAttribute('commission_percent'),
                'store_charge' => $store->getAttribute('store_charge'),
                'admin_status' => 0,
                'internal_status' => 1,
            ], array_map(
                fn ($item): array => $item->toOrderItemAttributes(),
                $data->items,
            ));

            if ($data->walletAmount > 0) {
                $this->customers->debitWallet($customer, $data->walletAmount);
                $this->walletTransactions->create([
                    'customer_id' => $customer->getKey(),
                    'message' => "Wallet used in order #{$order->getKey()}",
                    'type' => 'Debit',
                    'amount' => $data->walletAmount,
                    'transacted_at' => now(),
                ]);
            }

            return $order->refresh()->load(['store', 'customer', 'paymentMethod', 'coupon', 'items']);
        });
    }
}
