<?php

namespace App\Actions\Admin\Pages;

use App\Data\Admin\PageData;
use App\Models\Page;
use App\Repositories\PageRepository;

class CreatePageAction
{
    public function __construct(
        private readonly PageRepository $pages,
    ) {}

    public function execute(PageData $data): Page
    {
        return $this->pages->create($data->toArray());
    }
}
