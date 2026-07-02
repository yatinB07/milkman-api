<?php

namespace App\Actions\Admin\Pages;

use App\Models\Page;
use App\Repositories\PageRepository;

class ShowPageAction
{
    public function __construct(
        private readonly PageRepository $pages,
    ) {}

    public function execute(int $pageId): Page
    {
        return $this->pages->find($pageId);
    }
}
