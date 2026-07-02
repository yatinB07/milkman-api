<?php

namespace App\Actions\Admin\Favorites;

use App\Models\Favorite;
use App\Repositories\FavoriteRepository;

class ShowFavoriteAction
{
    public function __construct(
        private readonly FavoriteRepository $favorites,
    ) {}

    public function execute(int $favoriteId): Favorite
    {
        return $this->favorites->find($favoriteId);
    }
}
