<?php

namespace App\Actions\Customer\Stores;

use App\Data\Customer\CustomerStoreDetailQueryData;
use App\Models\Customer;
use App\Models\Store;
use App\Repositories\StoreRepository;

class ShowCustomerStoreAction
{
    public function __construct(
        private readonly StoreRepository $stores,
    ) {}

    public function execute(Customer $customer, int $storeId, CustomerStoreDetailQueryData $query): Store
    {
        $store = $this->stores->findActiveForCustomer($customer, $storeId);
        $store->setAttribute('distance_km', $this->distanceKilometers(
            (float) $store->getAttribute('latitude'),
            (float) $store->getAttribute('longitude'),
            $query->latitude,
            $query->longitude,
        ));

        return $store;
    }

    private function distanceKilometers(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): string
    {
        $theta = $fromLongitude - $toLongitude;
        $distance = sin(deg2rad($fromLatitude)) * sin(deg2rad($toLatitude))
            + cos(deg2rad($fromLatitude)) * cos(deg2rad($toLatitude)) * cos(deg2rad($theta));
        $distance = min(1.0, max(-1.0, $distance));
        $miles = rad2deg(acos($distance)) * 60 * 1.1515;

        return number_format($miles * 1.609344, 2, '.', '');
    }
}
