<?php

namespace App\Actions\Admin\Faqs;

use App\Models\Faq;
use App\Repositories\FaqRepository;

class ShowFaqAction
{
    public function __construct(
        private readonly FaqRepository $faqs,
    ) {}

    public function execute(int $faqId): Faq
    {
        return $this->faqs->find($faqId);
    }
}
