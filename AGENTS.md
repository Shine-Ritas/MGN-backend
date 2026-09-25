# MGN Backend agent guide

## Project

This repository is the API backend for the MGN/Mogou application. It is a Laravel 11 application on PHP 8.2 with Sanctum authentication, PostgreSQL in the Docker environment, Redis-backed queues/cache, Pest tests, Laravel Pint, and Larastan/PHPStan level 6.

Use the existing architecture instead of introducing a new application pattern for a local change:

- `routes/api.php` mounts every API route under `/api/v1` and recursively includes `routes/api/**/*.php`.
- `app/Http/Controllers/Api` owns HTTP orchestration and response envelopes.
- `app/Http/Requests` owns reusable request validation.
- `app/Repo` owns most persistence and query workflows; `app/Services` owns cross-model or integration logic.
- `app/Models`, `database/migrations`, `database/factories`, and `database/seeders` define persistence.
- `tests/Feature/Api` covers API behavior; `tests/Unit` covers repositories, services, and infrastructure helpers.
- `deployment` and the Compose files define the PHP-FPM, Horizon/scheduler, Nginx, PostgreSQL, and Redis runtime.

## Working agreements

- Preserve existing response keys, status codes, route names, and authentication behavior unless the request explicitly changes the API contract.
- Keep controllers thin. Put multi-step queries and writes in the nearest existing repository or service, and use a `FormRequest` when validation is reusable or non-trivial.
- Treat `Admin` and `User` as separate authenticatable models. A route using `auth:sanctum` still needs its intended principal and permissions verified; do not infer that the URL prefix alone enforces the actor type.
- `Mogou` route-model binding uses `slug`. Confirm the route key before changing parameter lookup or uniqueness rules.
- Do not query chapter or chapter-image base tables as if all live rows are stored there. These models use rotation-key partitions. Use the partition workflow below for any chapter-related work.
- Media accessors often return a public URL while deletion and existence checks require the stored filename. Use `getRawOriginal()` when constructing storage paths.
- Keep database behavior compatible with both PostgreSQL and the in-memory SQLite test suite.
- Do not put credentials, tokens, production URLs, or real user data in source, fixtures, logs, or examples. Add configuration through environment-backed config values.
- Do not run destructive database commands such as `migrate:fresh`, `db:wipe`, or `make seed` against an unspecified environment.
- Preserve unrelated working-tree changes and generated/runtime data. Do not edit `.DS_Store`, `vendor`, `node_modules`, `storage/framework`, or `bootstrap/cache` as part of source changes.
- The ownership declaration in `ownership.md` is authoritative; do not alter ownership notices unless explicitly requested.

## Repository skills

Load the narrow skill that matches the requested workflow:

- `$mgn-api-change` for adding or changing an HTTP API endpoint or its contract.
- `$mgn-partitioned-content` for Mogou chapters, chapter images, rotation keys, or partition-aware queries and migrations.
- `$mgn-media-storage` for uploads, image processing, watermarks, public media URLs, replacements, or deletions.
- `$mgn-database-change` for migrations, model persistence fields, factories, seeders, or enum-backed columns.
- `$mgn-runtime-change` for Docker, Nginx, PHP-FPM, Horizon, scheduler, Redis, or deployment configuration.

Explicit user requirements take precedence over these workflow defaults.

## Verification

Run the smallest checks that prove the change, then widen them when shared infrastructure is affected:

```bash
./vendor/bin/pest path/to/relevant/Test.php
./vendor/bin/phpstan analyse --memory-limit=512M
./vendor/bin/pint --test path/to/changed.php
```

Feature and unit tests use `RefreshDatabase`; feature tests also seed permissions, application configuration, and user avatars from `tests/Pest.php`. Reuse `Tests\Support\UserAuthenticated` and `Tests\Support\TestStorage` where applicable.

`make test` runs PHPStan, the full parallel Pest suite, and Pint in write mode. Use it only when a full-suite run and repository-wide formatting are intended; use Pint with `--test` for a non-mutating style check.
