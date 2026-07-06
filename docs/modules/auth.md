# Auth

Phase 3 auth uses Sanctum personal access tokens for each MilkMan identity area:

- Admin: `/api/v1/admin/auth/*`
- Customer: `/api/v1/customer/auth/*`
- Store: `/api/v1/store/auth/*`
- Rider: `/api/v1/rider/auth/*`

## Endpoints

```text
POST /api/v1/{identity}/auth/login
GET  /api/v1/{identity}/auth/me
POST /api/v1/{identity}/auth/logout
GET  /api/v1/{identity}/auth/permissions/{permission}
POST /api/v1/customer/auth/email-availability
POST /api/v1/customer/auth/mobile-availability
POST /api/v1/customer/auth/password/reset
```

`identity` must be one of `admin`, `customer`, `store`, or `rider`.

Login returns:

- Sanctum bearer token
- Identity profile
- Spatie roles
- Spatie permissions

The profile and logout endpoints require `auth:sanctum`. A token issued for one identity type cannot be used against another identity area.

The customer availability endpoints are public registration helpers mapped from the legacy customer `email_check.php` and `mobile_check.php` endpoints. They return a boolean `available` value for a candidate email or country-code/mobile pair. `CustomerRepository` performs the existence checks, including soft-deleted rows, because unique identity values should not be reused accidentally while old customer records remain restorable.

The customer password reset endpoint maps the legacy `u_forget_password.php` behavior. The legacy endpoint reset the password by mobile number; the Laravel endpoint requires both `country_code` and `mobile` so two regions can safely share the same local number. Password persistence stays in `CustomerRepository`, and the customer model hashed cast stores the new password securely.

The permission endpoint verifies a Spatie permission for the authenticated identity and returns:

```json
{
  "data": {
    "permission": "settings.update",
    "allowed": true
  }
}
```

## Implementation

Request flow:

```text
Route -> LoginIdentityRequest -> IdentityAuthController -> LoginIdentityAction -> IdentityAuthService -> IdentityRepository -> Model -> IdentityProfileResource
```

`IdentityRepository` owns identity model lookup. `IdentityAuthService` validates credentials, account activity, and token identity boundaries. `IdentityProfileResource` owns the API response shape.

Inactive accounts are rejected with HTTP 403. Invalid credentials are rejected with HTTP 401.

Auth errors use named exception classes under `App\Exceptions\Auth`, and response text comes from `lang/en/auth.php` so messages can be localized later.

## Policies

Initial Phase 3 ownership policies:

- `StorePolicy`: admins with store permissions can manage stores; store owners can update only their own store.
- `OrderPolicy`: admins can view/update order status by permission; stores and riders can access assigned orders; customers can view their own orders only.
