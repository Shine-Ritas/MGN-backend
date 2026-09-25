---
name: mgn-database-change
description: Change an MGN database schema, persisted model field, backed enum column, factory, or seeder.
---

# MGN database change

Deliver a migration and all application updates needed to use it safely.

1. Read the current migration, model, factory, seeders, validation, repository/service, and nearest tests for the affected domain. Read [references/database-map.md](references/database-map.md) when choosing portability or rollout behavior.
2. Add a forward-only migration for an existing project; do not edit an old applied migration unless the user explicitly requests history rewriting. Make `down()` reverse only this migration's work.
3. Preserve PostgreSQL production behavior and SQLite test compatibility. When database-specific SQL is necessary, branch explicitly by driver and test the portable path.
4. Update the model's fillable/guarded fields, casts, enum mapping, relationships, accessors, and PHPDoc/type annotations as required. Update request validation and serialization intentionally rather than exposing a new column by accident.
5. Update factories and only the seeders that need the field. Keep fixtures deterministic enough for assertions and avoid secrets or production data.
6. For `sub_mogous` or `sub_mogou_images`, also use `$mgn-partitioned-content`; existing prefixed physical tables require an explicit rollout, not just a base-table change.
7. Add a migration/schema assertion and behavior coverage for defaults, nullability, constraints, cascade behavior, and enum/cast round-trips that matter to the request.
8. Run the focused Pest tests, then PHPStan and a Pint `--test` check for changed PHP files. Never run `migrate:fresh`, `db:wipe`, or `make seed` against an unspecified environment.

