---
name: mgn-media-storage
description: Change MGN media upload, image processing, watermarking, public URL, replacement, or deletion behavior.
---

# MGN media storage

Keep database records, physical objects, and public URLs consistent across local tests and configured storage providers.

1. Read [references/storage-map.md](references/storage-map.md) before changing a path, accessor, provider, upload, replacement, or deletion.
2. Use the existing HydraStorage `HydraMedia` operations for application media. Build logical folder paths without duplicating the package's configured public prefix.
3. Distinguish a stored filename from an accessor-expanded public URL. Use `getRawOriginal()` for removal, existence checks, moves, and database comparisons.
4. Validate file type and required metadata before writing. Apply `MediaOption` quality/watermark settings only when the endpoint requests them and the configured watermark exists.
5. On replacement, keep the previous object until the new upload succeeds, then remove the old object and persist the new raw filename in a failure-safe order.
6. On deletion, remove both the database record and the intended object/directory. Remember that a database rollback cannot restore an already-deleted remote object; choose and test an order that does not leave a live record pointing at missing media.
7. In tests, use `Tests\Support\TestStorage`, which fakes `testStorage` and overrides `hydrastorage.provider`. Assert the canonical `public/...` object path as well as the database effect and serialized URL shape when relevant.
8. Do not use live BunnyCDN, S3, Slack, Telegram, or other external services in automated tests.

Use `$mgn-partitioned-content` as well for chapter page images because their records live in rotation-key tables.

