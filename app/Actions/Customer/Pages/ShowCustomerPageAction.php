<?php

namespace App\Actions\Customer\Pages;

use App\Models\Page;
use App\Repositories\PageRepository;

class ShowCustomerPageAction
{
    public function __construct(private readonly PageRepository $pages) {}

    public function execute(int $pageId): Page
    {
        return $this->pages->findActive($pageId);
    }
}
