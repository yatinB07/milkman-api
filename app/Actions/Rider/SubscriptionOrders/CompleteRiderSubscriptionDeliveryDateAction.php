<?php

namespace App\Actions\Rider\SubscriptionOrders;

use App\Data\Rider\RiderSubscriptionDeliveryDateData;
use App\Exceptions\Catalog\FutureSubscriptionDeliveryDateException;
use App\Exceptions\Catalog\SubscriptionDeliveryDateAlreadyCompletedException;
use App\Exceptions\Catalog\SubscriptionDeliveryDateNotScheduledException;
use App\Models\Rider;
use App\Models\SubscriptionOrder;
use App\Repositories\SubscriptionOrderItemRepository;
use Illuminate\Support\Carbon;

class CompleteRiderSubscriptionDeliveryDateAction
{
    public function __construct(private readonly SubscriptionOrderItemRepository $items) {}

    public function execute(Rider $rider, int $orderId, int $itemId, RiderSubscriptionDeliveryDateData $data): SubscriptionOrder
    {
        $item = $this->items->findForRiderSubscriptionOrder($rider, $orderId, $itemId);
        $totalDates = $this->dates((string) $item->getAttribute('total_dates'));
        $completedDates = $this->dates((string) $item->getAttribute('completed_dates'));

        if (in_array($data->selectedDate, $completedDates, true)) {
            throw new SubscriptionDeliveryDateAlreadyCompletedException;
        }

        if (! in_array($data->selectedDate, $totalDates, true)) {
            throw new SubscriptionDeliveryDateNotScheduledException;
        }

        if (Carbon::parse($data->selectedDate)->isFuture()) {
            throw new FutureSubscriptionDeliveryDateException;
        }

        $item = $this->items->markDeliveryDateCompleted($item, $data->selectedDate, $completedDates);

        return $item->getRelation('subscriptionOrder')->load(['paymentMethod', 'items']);
    }

    /** @return list<string> */
    private function dates(string $dates): array
    {
        if ($dates === '' || $dates === '[]') {
            return [];
        }

        return collect(explode(',', $dates))
            ->map(fn (string $date): string => trim($date, " \t\n\r\0\x0B\"[]"))
            ->filter()
            ->values()
            ->all();
    }
}
