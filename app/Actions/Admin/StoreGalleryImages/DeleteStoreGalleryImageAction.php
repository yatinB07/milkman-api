<?php

namespace App\Actions\Admin\StoreGalleryImages;

use App\Repositories\StoreGalleryImageRepository;

class DeleteStoreGalleryImageAction
{
    public function __construct(
        private readonly StoreGalleryImageRepository $images,
    ) {}

    public function execute(int $imageId): void
    {
        $this->images->delete(
            $this->images->find($imageId),
        );
    }
}
