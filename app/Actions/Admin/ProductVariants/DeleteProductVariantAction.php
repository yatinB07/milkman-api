<?php

namespace App\Actions\Admin\ProductVariants;

use App\Repositories\ProductVariantRepository;

class DeleteProductVariantAction
{
    public function __construct(
        private readonly ProductVariantRepository $variants,
    ) {}

    public function execute(int $variantId): void
    {
        $this->variants->delete(
            $this->variants->find($variantId),
        );
    }
}
