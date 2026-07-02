<?php

namespace App\Actions\Admin\ProductVariants;

use App\Data\Admin\ProductVariantData;
use App\Models\ProductVariant;
use App\Repositories\ProductVariantRepository;

class UpdateProductVariantAction
{
    public function __construct(
        private readonly ProductVariantRepository $variants,
    ) {}

    public function execute(int $variantId, ProductVariantData $data): ProductVariant
    {
        return $this->variants->update(
            $this->variants->find($variantId),
            $data->toArray(),
        );
    }
}
