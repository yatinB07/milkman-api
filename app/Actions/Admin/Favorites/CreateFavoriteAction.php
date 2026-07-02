<?php

namespace App\Actions\Admin\Favorites;

use App\Data\Admin\FavoriteData;
use App\Models\Favorite;
use App\Repositories\FavoriteRepository;

class CreateFavoriteAction
{
    public function __construct(
        private readonly FavoriteRepository $favorites,
    ) {}

    public function execute(FavoriteData $data): Favorite
    {
        return $this->favorites->create($data->toArray());
    }
}
