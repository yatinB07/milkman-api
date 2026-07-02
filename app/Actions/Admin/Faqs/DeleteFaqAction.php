<?php

namespace App\Actions\Admin\Faqs;

use App\Repositories\FaqRepository;

class DeleteFaqAction
{
    public function __construct(
        private readonly FaqRepository $faqs,
    ) {}

    public function execute(int $faqId): void
    {
        $this->faqs->delete(
            $this->faqs->find($faqId),
        );
    }
}
