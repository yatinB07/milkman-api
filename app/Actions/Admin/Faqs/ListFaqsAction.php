<?php

namespace App\Actions\Admin\Faqs;

use App\Data\Admin\ListQueryData;
use App\Repositories\FaqRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListFaqsAction
{
    public function __construct(
        private readonly FaqRepository $faqs,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->faqs->paginate($query->search, $query->perPage);
    }
}
