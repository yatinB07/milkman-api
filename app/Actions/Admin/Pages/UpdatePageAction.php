<?php

namespace App\Actions\Admin\Pages;

use App\Data\Admin\PageData;
use App\Models\Page;
use App\Repositories\PageRepository;

class UpdatePageAction
{
    public function __construct(
        private readonly PageRepository $pages,
    ) {}

    public function execute(int $pageId, PageData $data): Page
    {
        return $this->pages->update(
            $this->pages->find($pageId),
            $data->toArray(),
        );
    }
}
