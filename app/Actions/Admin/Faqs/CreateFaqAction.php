<?php

namespace App\Actions\Admin\Faqs;

use App\Data\Admin\FaqData;
use App\Models\Faq;
use App\Repositories\FaqRepository;

class CreateFaqAction
{
    public function __construct(
        private readonly FaqRepository $faqs,
    ) {}

    public function execute(FaqData $data): Faq
    {
        return $this->faqs->create($data->toArray());
    }
}
