<?php

namespace App\Actions\Customer\Home;

use App\Data\Customer\CustomerHomeQueryData;
use App\Models\Customer;
use App\Repositories\BannerRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\SettingRepository;
use App\Repositories\StoreRepository;

class ShowCustomerHomeAction
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly CategoryRepository $categories,
        private readonly StoreRepository $stores,
        private readonly SettingRepository $settings,
    ) {}

    /** @return array<string, mixed> */
    public function execute(Customer $customer, CustomerHomeQueryData $query): array
    {
        return [
            'banners' => $this->banners->activeForHome($query->perPage),
            'categories' => $this->categories->activeForHome($query->perPage),
            'favorite_stores' => $this->stores->favoriteStoresForHome($customer, $query->perPage),
            'spotlight_stores' => $this->stores->activeForHome($query->perPage),
            'top_stores' => $this->stores->topRatedForHome($query->perPage),
            'currency' => $this->settings->current()?->getAttribute('currency'),
            'wallet_balance' => $customer->getAttribute('wallet_balance'),
            'location' => [
                'latitude' => $query->latitude,
                'longitude' => $query->longitude,
            ],
        ];
    }
}
