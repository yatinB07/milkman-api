<?php

namespace App\Actions\Store\Faqs;

use App\Models\Store;
use App\Repositories\FaqRepository;

class DeleteStoreFaqAction
{
    public function __construct(private readonly FaqRepository $faqs) {}

    public function execute(Store $store, int $faqId): void
    {
        $this->faqs->delete($this->faqs->findForStore($store, $faqId));
    }
}
