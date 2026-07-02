<?php

namespace App\Actions\Admin\Banners;

use App\Data\Admin\BannerData;
use App\Models\Banner;
use App\Repositories\BannerRepository;

class UpdateBannerAction
{
    public function __construct(
        private readonly BannerRepository $banners,
    ) {}

    public function execute(int $bannerId, BannerData $data): Banner
    {
        return $this->banners->update(
            $this->banners->find($bannerId),
            $data->toArray(),
        );
    }
}
