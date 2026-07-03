<?php

namespace App\Actions\Customer\Orders;

use App\Data\Customer\SubscriptionScheduleChangeData;
use App\Models\Customer;
use App\Models\SubscriptionOrder;
use App\Repositories\SubscriptionOrderItemRepository;
use App\Repositories\SubscriptionOrderRepository;
use App\Services\SubscriptionScheduleService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

class SkipCustomerSubscriptionScheduleAction
{
    public function __construct(
        private readonly SubscriptionOrderRepository $orders,
        private readonly SubscriptionOrderItemRepository $items,
        private readonly SubscriptionScheduleService $schedules,
        private readonly WalletService $wallet,
    ) {}

    public function execute(Customer $customer, int $orderId, int $itemId, SubscriptionScheduleChangeData $data): SubscriptionOrder
    {
        return DB::transaction(function () use ($customer, $orderId, $itemId, $data): SubscriptionOrder {
            $item = $this->items->findForCustomerSubscriptionOrder($customer, $orderId, $itemId);
            $order = $item->subscriptionOrder;
            $refundAmount = ((float) $item->getAttribute('price')) * ((int) $item->getAttribute('quantity')) * count($data->dates);
            $subtotal = max(0, ((float) $order->getAttribute('subtotal')) - $refundAmount);
            $total = max(0, $subtotal + ((float) $order->getAttribute('delivery_charge')) - ((float) $order->getAttribute('coupon_amount')) - ((float) $order->getAttribute('wallet_amount')));

            $this->items->update($item, [
                'total_dates' => $this->schedules->skippedDates((string) $item->getAttribute('total_dates'), $data->dates),
                'total_deliveries' => max(0, ((int) $item->getAttribute('total_deliveries')) - count($data->dates)),
            ]);
            $this->orders->updateFinancialTotals($order, $subtotal, $total);
            $this->wallet->credit($customer, number_format($refundAmount, 2, '.', ''), "Refund amount for subscription order #{$order->getKey()}");

            return $this->orders->findForCustomer($customer, $orderId);
        });
    }
}
