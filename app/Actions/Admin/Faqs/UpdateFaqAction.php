<?php

namespace App\Actions\Admin\Faqs;

use App\Data\Admin\FaqData;
use App\Models\Faq;
use App\Repositories\FaqRepository;

class UpdateFaqAction
{
    public function __construct(
        private readonly FaqRepository $faqs,
    ) {}

    public function execute(int $faqId, FaqData $data): Faq
    {
        return $this->faqs->update(
            $this->faqs->find($faqId),
            $data->toArray(),
        );
    }
}
