<?php

namespace App\Actions\Admin\Banners;

use App\Models\Banner;
use App\Repositories\BannerRepository;

class CreateBannerAction
{
    public function __construct(
        private readonly BannerRepository $banners,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function execute(array $attributes): Banner
    {
        return $this->banners->create($attributes);
    }
}
