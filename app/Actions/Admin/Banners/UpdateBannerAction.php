<?php

namespace App\Actions\Admin\Banners;

use App\Models\Banner;
use App\Repositories\BannerRepository;

class UpdateBannerAction
{
    public function __construct(
        private readonly BannerRepository $banners,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function execute(int $bannerId, array $attributes): Banner
    {
        return $this->banners->update(
            $this->banners->find($bannerId),
            $attributes,
        );
    }
}
