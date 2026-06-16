<?php

namespace App\Actions\Admin\Banners;

use App\Repositories\BannerRepository;

class DeleteBannerAction
{
    public function __construct(
        private readonly BannerRepository $banners,
    ) {}

    public function execute(int $bannerId): void
    {
        $this->banners->delete(
            $this->banners->find($bannerId),
        );
    }
}
