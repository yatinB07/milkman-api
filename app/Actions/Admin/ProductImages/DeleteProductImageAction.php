<?php

namespace App\Actions\Admin\ProductImages;

use App\Repositories\ProductImageRepository;

class DeleteProductImageAction
{
    public function __construct(
        private readonly ProductImageRepository $images,
    ) {}

    public function execute(int $imageId): void
    {
        $this->images->delete(
            $this->images->find($imageId),
        );
    }
}
