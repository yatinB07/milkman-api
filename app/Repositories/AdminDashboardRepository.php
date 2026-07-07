<?php

namespace App\Repositories;

use App\Models\Banner;
use App\Models\CashCollection;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Page;
use App\Models\PaymentMethod;
use App\Models\PayoutRequest;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use App\Models\Zone;

class AdminDashboardRepository
{
    /** @return array<string, mixed> */
    public function metrics(): array
    {
        $counts = [
            'banners' => Banner::query()->count(),
            'categories' => Category::query()->count(),
            'zones' => Zone::query()->count(),
            'stores' => Store::query()->count(),
            'payment_methods' => PaymentMethod::query()->count(),
            'pages' => Page::query()->count(),
            'customers' => Customer::query()->count(),
        ];

        $normalGross = $this->completedNormalOrderGross();
        $subscriptionGross = $this->completedSubscriptionOrderGross();
        $financials = [
            'total_earning' => number_format($this->normalOrderCommission() + $this->subscriptionOrderCommission(), 2, '.', ''),
            'total_sales' => number_format($normalGross + $subscriptionGross, 2, '.', ''),
            'completed_payout' => number_format($this->payoutTotal('completed'), 2, '.', ''),
            'pending_payout' => number_format($this->payoutTotal('pending'), 2, '.', ''),
            'on_hand_cash_amount' => number_format(max(0, $normalGross - (float) CashCollection::query()->sum('amount')), 2, '.', ''),
        ];

        return [
            'counts' => $counts,
            'financials' => $financials,
            'cards' => $this->cards($counts, $financials),
        ];
    }

    private function normalOrderCommission(): float
    {
        return (float) Order::query()
            ->where('status', 'Completed')
            ->get()
            ->sum(fn (Order $order): float => $this->gross($order) * ((float) $order->getAttribute('commission_percent') / 100));
    }

    private function subscriptionOrderCommission(): float
    {
        return (float) SubscriptionOrder::query()
            ->where('status', 'Completed')
            ->get()
            ->sum(fn (SubscriptionOrder $order): float => $this->gross($order) * ((float) $order->getAttribute('commission_percent') / 100));
    }

    private function completedNormalOrderGross(): float
    {
        return (float) Order::query()
            ->where('status', 'Completed')
            ->get()
            ->sum(fn (Order $order): float => $this->gross($order));
    }

    private function completedSubscriptionOrderGross(): float
    {
        return (float) SubscriptionOrder::query()
            ->where('status', 'Completed')
            ->get()
            ->sum(fn (SubscriptionOrder $order): float => $this->gross($order));
    }

    private function payoutTotal(string $status): float
    {
        return (float) PayoutRequest::query()
            ->where('status', $status)
            ->sum('amount');
    }

    private function gross(Order|SubscriptionOrder $order): float
    {
        return ((float) $order->getAttribute('subtotal'))
            - ((float) $order->getAttribute('coupon_amount'))
            + ((float) $order->getAttribute('delivery_charge'));
    }

    /**
     * @param  array<string, int>  $counts
     * @param  array<string, string>  $financials
     * @return list<array<string, int|string>>
     */
    private function cards(array $counts, array $financials): array
    {
        return [
            ['title' => 'Banners', 'report_data' => $counts['banners']],
            ['title' => 'Category', 'report_data' => $counts['categories']],
            ['title' => 'Zones', 'report_data' => $counts['zones']],
            ['title' => 'Total Store', 'report_data' => $counts['stores']],
            ['title' => 'Payment Gateway', 'report_data' => $counts['payment_methods']],
            ['title' => 'Pages', 'report_data' => $counts['pages']],
            ['title' => 'User List', 'report_data' => $counts['customers']],
            ['title' => 'Total Earning', 'report_data' => $financials['total_earning']],
            ['title' => 'Total Sales', 'report_data' => $financials['total_sales']],
            ['title' => 'Total Completed Payout', 'report_data' => $financials['completed_payout']],
            ['title' => 'Total Pending Payout', 'report_data' => $financials['pending_payout']],
            ['title' => 'Total On Hand Cash Amount Stores', 'report_data' => $financials['on_hand_cash_amount']],
        ];
    }
}
