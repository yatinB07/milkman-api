<?php

namespace App\Actions\Admin\Favorites;

use App\Data\Admin\FavoriteData;
use App\Models\Favorite;
use App\Repositories\FavoriteRepository;

class UpdateFavoriteAction
{
    public function __construct(
        private readonly FavoriteRepository $favorites,
    ) {}

    public function execute(int $favoriteId, FavoriteData $data): Favorite
    {
        return $this->favorites->update(
            $this->favorites->find($favoriteId),
            $data->toArray(),
        );
    }
}
