<?php

namespace App\Actions\Admin\Favorites;

use App\Repositories\FavoriteRepository;

class DeleteFavoriteAction
{
    public function __construct(
        private readonly FavoriteRepository $favorites,
    ) {}

    public function execute(int $favoriteId): void
    {
        $this->favorites->delete(
            $this->favorites->find($favoriteId),
        );
    }
}
