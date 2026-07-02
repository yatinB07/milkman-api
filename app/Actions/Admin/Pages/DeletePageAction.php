<?php

namespace App\Actions\Admin\Pages;

use App\Repositories\PageRepository;

class DeletePageAction
{
    public function __construct(
        private readonly PageRepository $pages,
    ) {}

    public function execute(int $pageId): void
    {
        $this->pages->delete(
            $this->pages->find($pageId),
        );
    }
}
