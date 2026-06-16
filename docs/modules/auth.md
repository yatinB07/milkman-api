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
```

`identity` must be one of `admin`, `customer`, `store`, or `rider`.

Login returns:

- Sanctum bearer token
- Identity profile
- Spatie roles
- Spatie permissions

The profile and logout endpoints require `auth:sanctum`. A token issued for one identity type cannot be used against another identity area.

## Implementation

Request flow:

```text
Route -> LoginIdentityRequest -> IdentityAuthController -> LoginIdentityAction -> IdentityAuthService -> IdentityRepository -> Model -> IdentityProfileResource
```

`IdentityRepository` owns identity model lookup. `IdentityAuthService` validates credentials, account activity, and token identity boundaries. `IdentityProfileResource` owns the API response shape.

Inactive accounts are rejected with HTTP 403. Invalid credentials are rejected with HTTP 401.

Auth errors use named exception classes under `App\Exceptions\Auth`, and response text comes from `lang/en/auth.php` so messages can be localized later.
