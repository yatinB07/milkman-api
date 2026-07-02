<?php

namespace App\Actions\Admin\ProductImages;

use App\Models\ProductImage;
use App\Repositories\ProductImageRepository;

class ShowProductImageAction
{
    public function __construct(
        private readonly ProductImageRepository $images,
    ) {}

    public function execute(int $imageId): ProductImage
    {
        return $this->images->find($imageId);
    }
}
