# Storage map

## Providers

`config/hydrastorage.php` selects the HydraStorage provider from `STORAGE_PROVIDER` and enables a public prefix. The default is `local`; production may use the `bunnycdn` disk registered in `AppServiceProvider`.

`config/filesystems.php` defines `local`, `public`, `s3`, `backups`, and `bunnycdn`. `appDriver()` separately reads `control.mongou_storage`; do not interchange that helper with HydraStorage provider selection without tracing the caller.

## Canonical object paths

- Mogou cover: `public/mogou/cover/{filename}`
- Chapter cover: `public/sub_mogou/{chapter_slug}/cover/{filename}`
- Chapter page image: `public/mogou/{mogou_id}/{sub_mogou_id}/{filename}`
- Application configuration media, including watermark: `public/config/{filename}`

The folder passed to `storeMedia()` normally omits the leading `public/`; deletion and direct disk checks in current code normally include it.

## Accessors and raw values

- `Mogou::cover` calls `getMedia($value, 'mogou/cover')`.
- `SubMogouImage::path` calls `getMedia($value, "mogou/{mogou_id}/{sub_mogou_id}")`.
- Other media models follow the same pattern: serialized attributes may be URLs while the stored column is only a filename.

Use `$model->getRawOriginal('cover')` or `$model->getRawOriginal('path')` before constructing a deletion path.

## Existing workflows

- `MogouActionRepo` stores/replaces/deletes Mogou covers and uses image quality 70.
- `SubMogouActionRepo` stores chapter covers.
- `SubMogouStorageUploadRepo` writes chapter pages, optionally reads application watermark media, and creates partitioned `SubMogouImage` rows.
- `SubMogouDeleteRepo` removes image rows and the chapter directory.
- `SubMogouActionRepo::deleteImage()` removes an individual page object and row.

`Tests\Support\TestStorage::assertInStorage($path)` already prepends `public/`.

