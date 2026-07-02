<?php

namespace App\Actions\Admin\Settings;

use App\Repositories\SettingRepository;

class DeleteSettingAction
{
    public function __construct(
        private readonly SettingRepository $settings,
    ) {}

    public function execute(int $settingId): void
    {
        $this->settings->delete(
            $this->settings->find($settingId),
        );
    }
}
