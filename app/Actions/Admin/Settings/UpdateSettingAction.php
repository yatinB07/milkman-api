<?php

namespace App\Actions\Admin\Settings;

use App\Data\Admin\SettingData;
use App\Models\Setting;
use App\Repositories\SettingRepository;

class UpdateSettingAction
{
    public function __construct(
        private readonly SettingRepository $settings,
    ) {}

    public function execute(int $settingId, SettingData $data): Setting
    {
        return $this->settings->update(
            $this->settings->find($settingId),
            $data->toArray(),
        );
    }
}
