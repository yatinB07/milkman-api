<?php

namespace App\Actions\Store\Faqs;

use App\Data\Store\StoreFaqData;
use App\Models\Faq;
use App\Models\Store;
use App\Repositories\FaqRepository;

class CreateStoreFaqAction
{
    public function __construct(private readonly FaqRepository $faqs) {}

    public function execute(Store $store, StoreFaqData $data): Faq
    {
        return $this->faqs->createForStore($store, $data->toArray());
    }
}
