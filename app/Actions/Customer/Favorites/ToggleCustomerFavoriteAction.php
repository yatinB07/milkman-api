<?php

namespace App\Actions\Customer\Favorites;

use App\Data\Customer\FavoriteToggleData;
use App\Models\Customer;
use App\Models\Favorite;
use App\Repositories\FavoriteRepository;

class ToggleCustomerFavoriteAction
{
    public function __construct(private readonly FavoriteRepository $favorites) {}

    /** @return array{favorite: Favorite, is_favorite: bool} */
    public function execute(Customer $customer, FavoriteToggleData $data): array
    {
        return $this->favorites->toggleForCustomer($customer, $data->storeId);
    }
}
