# Partition map

## Physical layout

`App\Services\Partition\TablePartition` defines 24 Greek rotation keys. The current locked count is 2, so new Mogous are assigned either `alpha` or `beta` by `Mogou`'s creating hook.

The base models are templates plus query objects:

- `SubMogou` base table: `sub_mogous`
- `SubMogouImage` base table: `sub_mogou_images`
- live chapter table: `{rotation_key}_sub_mogous`
- live image table: `{rotation_key}_sub_mogou_images`

`DbPartition::dbConstructing()` creates currently enabled physical tables on model boot. SQL differs for MySQL, SQLite, and PostgreSQL.

## Supported resolution paths

- `Mogou::subMogous($mogou->rotation_key)` creates a relationship against the correct chapter table.
- `SubMogou::images($mogou->rotation_key)` creates a relationship against the correct image table.
- `App\Repo\Admin\SubMogouRepo\MogouPartitionFind` finds the parent, records its rotation key, and returns a model bound to the correct physical table.
- `App\Repo\User\SubMogou\UserSubMogouRepo` demonstrates partition-safe reader queries.

Never rely on the default `alpha` relationship parameter unless the parent has actually been resolved as `alpha`.

## Lifecycle invariants

- `Mogou` generates its slug and rotation key when created.
- `SubMogou` generates a ULID and a slug ending in that ULID when created; updates retain the ULID suffix.
- Creating or deleting a `SubMogou` increments or decrements the parent `Mogou.total_chapters` through model events.
- `SubMogouImage` uses `position` with `LexoRankTrait`; ranking is scoped by both `mogou_id` and `sub_mogou_id`.
- Chapter images must be queried from the same rotation partition as their parent chapter.

Query-builder bulk updates/deletes do not run these model events. Model instances returned from manually selected partition tables must retain that table before `save()`, `update()`, or `delete()`.

## Schema and test consequences

A migration that changes only `sub_mogous` or `sub_mogou_images` does not retrofit existing prefixed tables. Explicitly alter every active partition in a rollout-safe way, and keep the base table updated so future partitions inherit the new schema.

Tests use in-memory SQLite. `tests/Unit/Db/DbPartitionTest.php` and `tests/Unit/Db/ParititionTest.php` cover table creation, while Mogou and SubMogou Feature/Unit tests cover application behavior. Set rotation keys explicitly when a test must prove routing rather than random assignment.

