# Customer APIs

## Customer Page Read API

Legacy reference:

- `MilkMan_web/user_api/u_pagelist.php`
- Legacy table: `tbl_page`
- Laravel table: `pages`

The legacy endpoint returned active page records from `tbl_page` with title and description fields. The Laravel API keeps the active-only behavior and exposes it as authenticated customer read endpoints:

- `GET /api/v1/customer/pages`
- `GET /api/v1/customer/pages/{page}`

Implementation notes:

- Customer tokens are checked through `IdentityAuthService` so store, rider, and admin tokens cannot access the customer route group.
- The list endpoint is paginated and supports `search` across page title and description through `PageRepository::paginateActive`.
- The show endpoint only returns active pages through `PageRepository::findActive`.
- Inactive or missing pages throw the existing named `PageNotFoundException`, which resolves its user-facing message through `lang/en/catalog.php`.

Code paths:

- `App\Http\Controllers\Api\V1\Customer\CustomerPageController`
- `App\Actions\Customer\Pages\ListCustomerPagesAction`
- `App\Actions\Customer\Pages\ShowCustomerPageAction`
- `App\Http\Resources\Customer\CustomerPageResource`
- `App\Repositories\PageRepository`

Tests:

- `tests/Feature/Customer/CustomerPageReadTest.php`
- `tests/Feature/Foundation/OpenApiDocumentationTest.php`
