<?php

namespace App\Actions\Admin\Settings;

use App\Models\Setting;
use App\Repositories\SettingRepository;

class ShowSettingAction
{
    public function __construct(
        private readonly SettingRepository $settings,
    ) {}

    public function execute(int $settingId): Setting
    {
        return $this->settings->find($settingId);
    }
}
