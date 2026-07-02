<?php

namespace App\Actions\Admin\Settings;

use App\Data\Admin\SettingData;
use App\Models\Setting;
use App\Repositories\SettingRepository;

class CreateSettingAction
{
    public function __construct(
        private readonly SettingRepository $settings,
    ) {}

    public function execute(SettingData $data): Setting
    {
        return $this->settings->create($data->toArray());
    }
}
