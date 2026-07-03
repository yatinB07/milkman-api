<?php

namespace App\Actions\Customer\Cart;

use App\Data\Customer\CustomerCartDataQueryData;
use App\Repositories\CouponRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\StoreRepository;
use App\Repositories\TimeSlotRepository;

class ShowCustomerCartDataAction
{
    public function __construct(
        private readonly StoreRepository $stores,
        private readonly CouponRepository $coupons,
        private readonly PaymentMethodRepository $paymentMethods,
        private readonly TimeSlotRepository $timeSlots,
    ) {}

    /** @return array<string, mixed> */
    public function execute(int $storeId, CustomerCartDataQueryData $query): array
    {
        $store = $this->stores->findActiveForCart($storeId);
        $timeSlots = $this->timeSlots->activeForStore($storeId);

        $store->setAttribute('checkout_delivery_charge', $this->deliveryCharge($store, $query));
        $store->setAttribute('checkout_is_pickup_enabled', $timeSlots->isNotEmpty() && (bool) $store->getAttribute('is_pickup_enabled'));

        return [
            'store' => $store,
            'coupons' => $this->coupons->activeForStore($storeId),
            'payment_methods' => $this->paymentMethods->visibleActive(),
            'time_slots' => $timeSlots,
        ];
    }

    private function deliveryCharge(mixed $store, CustomerCartDataQueryData $query): string
    {
        $chargeType = (int) $store->getAttribute('charge_type');

        if ($chargeType === 0 || $chargeType === 1) {
            return number_format((float) $store->getAttribute('delivery_charge'), 2, '.', '');
        }

        $distance = $this->distanceKilometers(
            (float) $store->getAttribute('latitude'),
            (float) $store->getAttribute('longitude'),
            $query->latitude,
            $query->longitude,
        );
        $includedKilometers = (float) $store->getAttribute('unit_kilometers');
        $basePrice = (float) $store->getAttribute('unit_price');
        $additionalPrice = (float) $store->getAttribute('additional_price');

        if ($distance <= $includedKilometers) {
            return number_format($basePrice, 2, '.', '');
        }

        return number_format(round($basePrice + (($distance - $includedKilometers) * $additionalPrice)), 2, '.', '');
    }

    private function distanceKilometers(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): float
    {
        $theta = $fromLongitude - $toLongitude;
        $distance = sin(deg2rad($fromLatitude)) * sin(deg2rad($toLatitude))
            + cos(deg2rad($fromLatitude)) * cos(deg2rad($toLatitude)) * cos(deg2rad($theta));
        $distance = min(1.0, max(-1.0, $distance));
        $miles = rad2deg(acos($distance)) * 60 * 1.1515;

        return $miles * 1.609344;
    }
}
