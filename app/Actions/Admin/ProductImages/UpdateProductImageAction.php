<?php

namespace App\Actions\Admin\ProductImages;

use App\Data\Admin\ProductImageData;
use App\Models\ProductImage;
use App\Repositories\ProductImageRepository;

class UpdateProductImageAction
{
    public function __construct(
        private readonly ProductImageRepository $images,
    ) {}

    public function execute(int $imageId, ProductImageData $data): ProductImage
    {
        return $this->images->update(
            $this->images->find($imageId),
            $data->toArray(),
        );
    }
}
