<?php

namespace App\Actions\Admin\Settings;

use App\Data\Admin\ListQueryData;
use App\Repositories\SettingRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListSettingsAction
{
    public function __construct(
        private readonly SettingRepository $settings,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->settings->paginate($query->search, $query->perPage);
    }
}
