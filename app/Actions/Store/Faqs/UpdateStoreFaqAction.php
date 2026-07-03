<?php

namespace App\Actions\Store\Faqs;

use App\Data\Store\StoreFaqData;
use App\Models\Faq;
use App\Models\Store;
use App\Repositories\FaqRepository;

class UpdateStoreFaqAction
{
    public function __construct(private readonly FaqRepository $faqs) {}

    public function execute(Store $store, int $faqId, StoreFaqData $data): Faq
    {
        return $this->faqs->update(
            $this->faqs->findForStore($store, $faqId),
            $data->toArray(),
        );
    }
}
