<?php

namespace App\Actions\Rider\Pages;

use App\Models\Page;
use App\Repositories\PageRepository;

class ShowRiderPageAction
{
    public function __construct(private readonly PageRepository $pages) {}

    public function execute(int $pageId): Page
    {
        return $this->pages->findActive($pageId);
    }
}
