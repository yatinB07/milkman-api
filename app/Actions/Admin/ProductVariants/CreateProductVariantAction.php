<?php

namespace App\Actions\Admin\ProductVariants;

use App\Data\Admin\ProductVariantData;
use App\Models\ProductVariant;
use App\Repositories\ProductVariantRepository;

class CreateProductVariantAction
{
    public function __construct(
        private readonly ProductVariantRepository $variants,
    ) {}

    public function execute(ProductVariantData $data): ProductVariant
    {
        return $this->variants->create($data->toArray());
    }
}
