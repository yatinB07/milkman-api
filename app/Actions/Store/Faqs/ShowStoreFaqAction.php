<?php

namespace App\Actions\Store\Faqs;

use App\Models\Faq;
use App\Models\Store;
use App\Repositories\FaqRepository;

class ShowStoreFaqAction
{
    public function __construct(private readonly FaqRepository $faqs) {}

    public function execute(Store $store, int $faqId): Faq
    {
        return $this->faqs->findForStore($store, $faqId);
    }
}
