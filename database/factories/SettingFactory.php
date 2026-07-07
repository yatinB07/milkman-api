<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Setting> */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'web_name' => fake()->unique()->company(),
            'web_logo_path' => 'settings/'.fake()->uuid().'.png',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'primary_store_id' => Store::factory(),
            'signup_credit' => 25,
            'referral_credit' => 15,
            'store_withdrawal_limit' => 0,
            'show_dark_mode' => false,
            'sms_type' => 'demo',
        ];
    }
}
