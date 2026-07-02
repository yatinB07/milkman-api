# Settings

Legacy references include `tbl_setting`, `setting.php`, and the `edit_setting` branch in `controller/mrequire.php`.

## Admin Setting CRUD

```text
GET    /api/v1/admin/settings
GET    /api/v1/admin/settings/{setting}
POST   /api/v1/admin/settings
PUT    /api/v1/admin/settings/{setting}
DELETE /api/v1/admin/settings/{setting}
```

These endpoints require an admin Sanctum token with `settings.update`. They manage global application settings from the legacy `tbl_setting` table.

Legacy field mapping:

- `webname`, `weblogo`, `timezone`, `currency` map to `web_name`, `web_logo_path`, `timezone`, and `currency`.
- `one_key`, `one_hash`, `d_key`, `d_hash`, `s_key`, and `s_hash` map to customer, delivery, and store OneSignal key/hash columns.
- `scredit` and `rcredit` map to `signup_credit` and `referral_credit`.
- `auth_key`, `otp_id`, `acc_id`, `auth_token`, `twilio_number`, `sms_type`, and `otp_auth` map to the SMS/OTP configuration columns.

The list endpoint supports `search` across web name, timezone, currency, SMS type, and primary store identity fields, accepts `per_page`, and returns Laravel pagination metadata. Delete requests soft delete setting records.

The legacy admin panel usually edited one row with `id=1`. The Laravel API keeps standard CRUD semantics so environments can keep draft/tenant-specific settings if needed, while the frontend can still treat the first active record as the current global setting.

The admin setting module uses:

- `SettingController`
- `SettingRequest` and `UpdateSettingRequest`
- `App\Data\Admin\SettingData` and `App\Data\Admin\ListQueryData`
- setting actions under `App\Actions\Admin\Settings`
- `SettingRepository`
- `App\Http\Resources\Admin\SettingResource`

## Admin Milk Data CRUD

```text
GET    /api/v1/admin/milk-data
GET    /api/v1/admin/milk-data/{milkData}
POST   /api/v1/admin/milk-data
PUT    /api/v1/admin/milk-data/{milkData}
DELETE /api/v1/admin/milk-data/{milkData}
```

These endpoints require an admin Sanctum token with `settings.update`. They intentionally expose the legacy `tbl_milk` destination as a raw reference payload because the active legacy code no longer reads it; `controller/mediconfig.php` contains the note that the old dependency was removed.

The list endpoint supports `search` across the raw payload, accepts `per_page`, returns Laravel pagination metadata, and soft deletes records. No new business meaning is inferred for this table until a future source confirms its purpose.

The admin milk data module uses:

- `MilkDataController`
- `MilkDataRequest` and `UpdateMilkDataRequest`
- `App\Data\Admin\MilkDataData` and `App\Data\Admin\ListQueryData`
- milk data actions under `App\Actions\Admin\MilkData`
- `MilkDataRepository`
- `App\Http\Resources\Admin\MilkDataResource`
