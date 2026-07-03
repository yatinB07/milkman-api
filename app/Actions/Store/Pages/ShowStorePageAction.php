<?php

namespace App\Actions\Store\Pages;

use App\Models\Page;
use App\Repositories\PageRepository;

class ShowStorePageAction
{
    public function __construct(private readonly PageRepository $pages) {}

    public function execute(int $pageId): Page
    {
        return $this->pages->findActive($pageId);
    }
}
