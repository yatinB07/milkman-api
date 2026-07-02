<?php

namespace App\Actions\Admin\Banners;

use App\Models\Banner;
use App\Repositories\BannerRepository;

class ShowBannerAction
{
    public function __construct(
        private readonly BannerRepository $banners,
    ) {}

    public function execute(int $bannerId): Banner
    {
        return $this->banners->find($bannerId);
    }
}
