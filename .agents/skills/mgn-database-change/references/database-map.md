# Database map

## Runtime and tests

- Docker runs PostgreSQL 17.
- `phpunit.xml` runs tests with in-memory SQLite, array cache/session/mail, and a synchronous queue.
- Pest applies `RefreshDatabase` to both Feature and Unit tests.
- Feature tests automatically seed admin permissions, application configuration, and user avatars.

Keep migrations and queries portable across PostgreSQL and SQLite unless the change has an explicit production-only path.

## Main domains

- Identity: `users`, `admins`, Sanctum tokens, Spatie roles/permissions, login histories, avatars.
- Catalog: `mogous`, `categories`, and `mogous_categories`.
- Partitioned reading content: base and rotation-prefixed `sub_mogous` and `sub_mogou_images`.
- Commercial: `subscriptions` and `user_subscriptions`.
- Engagement: favorites, comments, reports, chapter analytics, and summaries.
- Presentation/configuration: application configuration, base/child sections, and social information.
- Publishing: bot publishers, social channels, their pivot, and published-post records.

## Model conventions

- Many models use `$fillable`, `$casts`, appended accessors, and lifecycle hooks; schema work is incomplete until these are reviewed.
- Backed enums live in `app/Enum`; validation helpers may live in `app/Vaildations` (the directory name is intentionally misspelled in the current project).
- `Mogou` binds routes by `slug`, while most models use `id`.
- Media columns usually store a filename but serialize through URL-producing accessors. Use raw originals for database and storage assertions.
- Database factories exist for nearly every domain model and should be the default test-data source.

## Risky commands

`make seed` invokes `php artisan migrate:fresh --seed` and destroys the selected database. `make test` includes Pint without `--test`, so it can rewrite PHP files. Prefer focused Pest tests and non-mutating style checks during ordinary changes.

