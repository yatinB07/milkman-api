<?php

namespace App\Actions\Admin\Banners;

use App\Repositories\BannerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListBannersAction
{
    public function __construct(
        private readonly BannerRepository $banners,
    ) {}

    public function execute(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->banners->paginate($search, $perPage);
    }
}
