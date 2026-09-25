---
name: mgn-partitioned-content
description: Change MGN chapters, chapter images, rotation keys, or partition-aware content queries and schema.
---

# MGN partitioned content

Protect the relationship between a `Mogou` and its physical chapter tables.

1. Read [references/partition-map.md](references/partition-map.md) before changing a partitioned query, write, relationship, lifecycle hook, or migration.
2. Resolve the parent `Mogou` first and use its `rotation_key` for both chapters and chapter images. Prefer `Mogou::subMogous($rotationKey)`, `SubMogou::images($rotationKey)`, or `MogouPartitionFind` over an unqualified base-model query.
3. Keep a chapter model bound to its resolved physical table throughout the operation. Do not create a fresh unbound `SubMogou` or `SubMogouImage` midway through a workflow.
4. Preserve chapter invariants: unique chapter number within the parent Mogou, paired image partition, ULID-backed slug behavior, image LexoRank ordering, and `Mogou.total_chapters` updates.
5. Avoid bulk writes that bypass Eloquent lifecycle events when those events maintain counters or slugs. If a bulk operation is required, maintain those invariants explicitly and transactionally.
6. For schema changes, update both the base table definition and all active physical partition tables. Account for already-deployed partitions; creating future partitions from the base table is not enough.
7. Test at least two explicit rotation keys when routing behavior changes, plus not-found and cross-parent isolation. Keep tests compatible with SQLite as well as PostgreSQL.

Use `$mgn-media-storage` as well when chapter covers or page images are uploaded, reordered, replaced, or deleted.

