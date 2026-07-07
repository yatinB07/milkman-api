<?php

namespace App\Actions\Customer\Orders;

use App\Data\Customer\CustomerSubscriptionOrderData;
use App\Data\Customer\CustomerSubscriptionOrderItemData;
use App\Exceptions\Customer\InsufficientWalletBalanceException;
use App\Models\Customer;
use App\Models\SubscriptionOrder;
use App\Repositories\StoreRepository;
use App\Repositories\SubscriptionOrderRepository;
use App\Services\WalletService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class PlaceCustomerSubscriptionOrderAction
{
    public function __construct(
        private readonly SubscriptionOrderRepository $subscriptionOrders,
        private readonly StoreRepository $stores,
        private readonly WalletService $wallets,
    ) {}

    public function execute(Customer $customer, CustomerSubscriptionOrderData $data): SubscriptionOrder
    {
        if ((float) $customer->getAttribute('wallet_balance') < $data->walletAmount) {
            throw new InsufficientWalletBalanceException;
        }

        $store = $this->stores->findActiveForCart($data->storeId);

        return DB::transaction(function () use ($customer, $data, $store): SubscriptionOrder {
            $order = $this->subscriptionOrders->createWithItems([
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
                'order_type' => $data->orderType,
                'commission_percent' => $store->getAttribute('commission_percent'),
                'store_charge' => $store->getAttribute('store_charge'),
                'admin_status' => 0,
                'internal_status' => 1,
            ], array_map(
                fn (CustomerSubscriptionOrderItemData $item): array => $this->itemAttributes($item),
                $data->items,
            ));

            if ($data->walletAmount > 0) {
                $this->wallets->debit(
                    $customer,
                    number_format($data->walletAmount, 2, '.', ''),
                    __('catalog.wallet_used_in_subscription_order', ['order' => $order->getKey()]),
                );
            }

            return $order->refresh()->load(['store', 'customer', 'paymentMethod', 'coupon', 'items']);
        });
    }

    /** @return array<string, mixed> */
    private function itemAttributes(CustomerSubscriptionOrderItemData $item): array
    {
        return [
            'quantity' => $item->quantity,
            'product_title' => $item->productTitle,
            'discount' => $item->discount,
            'image_path' => $item->imagePath,
            'price' => $item->price,
            'variant_title' => $item->variantTitle,
            'starts_at' => $item->startsAt,
            'total_deliveries' => $item->totalDeliveries,
            'total_dates' => implode(',', $this->deliveryDates($item)),
            'completed_dates' => null,
            'selected_days' => implode(',', $item->selectedDays),
            'time_slot' => $item->timeSlot,
        ];
    }

    /** @return array<int, string> */
    private function deliveryDates(CustomerSubscriptionOrderItemData $item): array
    {
        $deliveryDates = [];
        $date = CarbonImmutable::parse($item->startsAt);
        $weekdays = array_map(fn (int $day): int => $day + 1, $item->selectedDays);

        while (count($deliveryDates) < $item->totalDeliveries) {
            $date = $date->addDay();

            if (in_array((int) $date->format('N'), $weekdays, true)) {
                $deliveryDates[] = $date->toDateString();
            }
        }

        return $deliveryDates;
    }
}
