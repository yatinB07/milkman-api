# Payouts

Legacy references include `payout_setting`, `tbl_cash`, `add_payout.php`, `list_payout.php`, and `store_api/request_withdraw.php`.

## Admin Payout Request CRUD

```text
GET    /api/v1/admin/payout-requests
GET    /api/v1/admin/payout-requests/{payoutRequest}
POST   /api/v1/admin/payout-requests
PUT    /api/v1/admin/payout-requests/{payoutRequest}
DELETE /api/v1/admin/payout-requests/{payoutRequest}
```

These endpoints require an admin Sanctum token with `payouts.approve`. They manage payout records from the legacy `payout_setting` table. Legacy `owner_id`, `amt`, `proof`, `r_date`, `r_type`, `acc_number`, `bank_name`, `acc_name`, `ifsc_code`, `upi_id`, and `paypal_id` map to Laravel `store_id`, `amount`, `proof_path`, `requested_at`, `request_type`, `account_number`, `bank_name`, `account_name`, `ifsc_code`, `upi_id`, and `paypal_id`.

The list endpoint supports `search` across status, request type, transfer details, and store identity fields, accepts `per_page`, and returns Laravel pagination metadata. Delete requests soft delete payout requests.

The legacy store withdrawal endpoint checked available earnings before inserting a pending payout. That calculation belongs in a store-facing payout request Action/Service when store APIs are implemented. The admin CRUD focuses on operational management and completion metadata such as `status` and `proof_path`.

The admin payout request module uses:

- `PayoutRequestController`
- `PayoutRequestRequest` and `UpdatePayoutRequestRequest`
- `App\Data\Admin\PayoutRequestData` and `App\Data\Admin\ListQueryData`
- payout request actions under `App\Actions\Admin\PayoutRequests`
- `PayoutRequestRepository`
- `App\Http\Resources\Admin\PayoutRequestResource`
