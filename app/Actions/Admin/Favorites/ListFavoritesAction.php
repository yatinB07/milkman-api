<?php

namespace App\Actions\Admin\Favorites;

use App\Data\Admin\ListQueryData;
use App\Repositories\FavoriteRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListFavoritesAction
{
    public function __construct(
        private readonly FavoriteRepository $favorites,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->favorites->paginate($query->search, $query->perPage);
    }
}
