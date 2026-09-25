# API map

## Route loading

`App\Providers\RouteServiceProvider` mounts `routes/api.php` at `/api`. That file adds the `v1` prefix and the `api.` name prefix, then `App\Services\Route\RouteHelper` recursively includes every PHP file below `routes/api`.

The effective base URI is `/api/v1` and named routes begin with `api.`.

## Route groups

- `routes/api/admin/auth.php`: admin login is guarded by `guest:admin`; change-password and logout use `auth:sanctum`.
- `routes/api/admin/general.php`: admin-intended management endpoints use `auth:sanctum` and the `admin.` name prefix.
- `routes/api/user/auth.php`: user register/login are guest routes; logout uses `auth:sanctum`.
- `routes/api/user/general.php`: public reader endpoints and protected user actions are both under `user.maintenance`; protected routes use `auth:sanctum`.
- `routes/api.php`: public application configuration and category endpoints.

Sanctum tokens can belong to either `App\Models\Admin` or `App\Models\User`. Do not treat `auth:sanctum` as an admin-only or user-only authorization rule.

## Application layers

- Controllers translate requests into calls and return JSON.
- Form requests live in `app/Http/Requests` and return HTTP 422 for validation failures.
- Repositories in `app/Repo/Admin` and `app/Repo/User` encapsulate most Eloquent queries and writes.
- Services coordinate multiple repositories/models or integrations.
- Models contain relationships, casts, accessors, scopes, and some lifecycle invariants.

`App\Models\Mogou::getRouteKeyName()` returns `slug`; user route binding still uses `id`.

## Test conventions

- API tests live below `tests/Feature/Api/Admin` or `tests/Feature/Api/User`.
- All Feature tests use `Tests\TestCase` and `RefreshDatabase` via `tests/Pest.php`.
- Feature setup automatically seeds `AdminPermissionSeeder`, `ApplicationConfigSeeder`, and `UserAvatarSeeder`.
- `Tests\Support\UserAuthenticated` creates and authenticates users/admins; `setupAdmin()` assigns the `Admin` role.
- Use `postJson`, `getJson`, named `route(...)` calls, response assertions, and database/storage assertions matching nearby tests.

