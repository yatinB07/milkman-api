<?php

namespace App\Actions\Admin\Banners;

use App\Repositories\BannerRepository;
use Illuminate\Database\Eloquent\Collection;

class ListBannersAction
{
    public function __construct(
        private readonly BannerRepository $banners,
    ) {}

    public function execute(): Collection
    {
        return $this->banners->all();
    }
}
