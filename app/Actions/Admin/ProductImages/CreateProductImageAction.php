<?php

namespace App\Actions\Admin\ProductImages;

use App\Data\Admin\ProductImageData;
use App\Models\ProductImage;
use App\Repositories\ProductImageRepository;

class CreateProductImageAction
{
    public function __construct(
        private readonly ProductImageRepository $images,
    ) {}

    public function execute(ProductImageData $data): ProductImage
    {
        return $this->images->create($data->toArray());
    }
}
