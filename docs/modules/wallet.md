# Wallet

Wallet behavior is split between ledger management and balance-changing workflows.

Legacy references include `wallet_report`, customer wallet APIs, and order placement wallet deductions.

## Admin Wallet Transaction CRUD

```text
GET    /api/v1/admin/wallet-transactions
GET    /api/v1/admin/wallet-transactions/{walletTransaction}
POST   /api/v1/admin/wallet-transactions
PUT    /api/v1/admin/wallet-transactions/{walletTransaction}
DELETE /api/v1/admin/wallet-transactions/{walletTransaction}
```

These endpoints require an admin Sanctum token with `users.manage`. They manage the wallet ledger records from the legacy `wallet_report` table. Legacy `uid`, `message`, `status`, `amt`, and `tdate` map to Laravel `customer_id`, `message`, `type`, `amount`, and `transacted_at`.

The list endpoint supports `search` across wallet message, transaction type, customer name, customer email, and customer mobile, accepts `per_page`, and always returns Laravel pagination metadata. Delete requests soft delete wallet transactions.

The legacy `user_api/u_wallet_up.php` endpoint both increments `tbl_user.wallet` and writes a `wallet_report` credit row. This admin CRUD manages ledger records directly; customer balance updates and order wallet deductions should be implemented through a wallet service when the customer/order APIs are built.

The admin wallet transaction module uses:

- `WalletTransactionController`
- `WalletTransactionRequest` and `UpdateWalletTransactionRequest`
- `App\Data\Admin\WalletTransactionData` and `App\Data\Admin\ListQueryData`
- wallet transaction actions under `App\Actions\Admin\WalletTransactions`
- `WalletTransactionRepository`
- `App\Http\Resources\Admin\WalletTransactionResource`

## Customer Wallet APIs

```text
GET  /api/v1/customer/wallet-transactions
POST /api/v1/customer/wallet/top-ups
```

These endpoints require a customer Sanctum token. Admin, store, and rider tokens are rejected by the identity boundary checks.

`GET /customer/wallet-transactions` maps legacy `user_api/u_wallet_report.php`. It returns only the authenticated customer's wallet ledger, ordered newest first, with message, type/status, amount, and transaction timestamp fields. It supports `search` across message and type, accepts `per_page`, returns Laravel pagination metadata, and includes the current `wallet_balance`.

`POST /customer/wallet/top-ups` credits the authenticated customer's wallet and writes a `Credit` ledger row with the legacy message `Wallet Balance Added!!`. The balance update and ledger insert are executed in `WalletService` inside one database transaction so the two records cannot drift apart.

The customer wallet module uses:

- `CustomerWalletController`
- `ListCustomerResourcesRequest` and `WalletTopUpRequest`
- `App\Data\Customer\ListCustomerQueryData` and `WalletTopUpData`
- customer wallet actions under `App\Actions\Customer\Wallet`
- `WalletService`
- `WalletTransactionRepository`
- `App\Http\Resources\Customer\WalletTransactionResource`
