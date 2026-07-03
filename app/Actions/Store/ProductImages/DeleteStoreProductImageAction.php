<?php

namespace App\Actions\Store\ProductImages;

use App\Models\Store;
use App\Repositories\ProductImageRepository;

class DeleteStoreProductImageAction
{
    public function __construct(private readonly ProductImageRepository $images) {}

    public function execute(Store $store, int $imageId): void
    {
        $this->images->delete($this->images->findForStore($store, $imageId));
    }
}
