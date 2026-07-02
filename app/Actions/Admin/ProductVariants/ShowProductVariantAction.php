<?php

namespace App\Actions\Admin\ProductVariants;

use App\Models\ProductVariant;
use App\Repositories\ProductVariantRepository;

class ShowProductVariantAction
{
    public function __construct(
        private readonly ProductVariantRepository $variants,
    ) {}

    public function execute(int $variantId): ProductVariant
    {
        return $this->variants->find($variantId);
    }
}
