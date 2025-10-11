<?php

namespace App\Repo\Admin\SubMogouRepo;

use App\Http\Requests\SubMogouStorageUploadRequest;
use App\Models\ApplicationConfig;
use App\Models\Mogou;
use App\Models\SubMogou;
use App\Models\SubMogouImage;
use HydraStorage\HydraStorage\Service\Option\MediaOption;
use HydraStorage\HydraStorage\Traits\HydraMedia;
use Storage;

class SubMogouStorageUploadRepo
{
    use HydraMedia;

    protected Mogou $parentMogou;

    protected int $compress_quality;

    public function __construct()
    {
        $this->compress_quality = config('hydrastorage.compressed_quality') ?? 100;
    }

    protected function setSubMogouTable(string $key = 'id', ?string $value = null): SubMogou
    {
        $this->parentMogou = Mogou::where($key, $value)->firstOrFail();

        $rotation_key = $this->parentMogou->rotation_key;

        $sub_mogou = new SubMogou;
        $table = $sub_mogou->getPartition($rotation_key);

        $sub_mogou->setTable($table);

        $sub_mogou->setKeyName('id');

        return $sub_mogou;
    }

    public function upload(SubMogouStorageUploadRequest $request): SubMogou
    {

        $subMogou = $this->setSubMogouTable('id', $request['mogou_id']);
        $subMogou = $subMogou->where('ulid', $request['ulid'])->firstOrFail();

        $parent_mogou = $this->parentMogou->id;
        $sub_mogou_id = $subMogou->id;
        $path = "mogou/{$parent_mogou}/{$sub_mogou_id}";

        $mediaOption = MediaOption::create();

        if ($request->has('watermark_apply') && $request->watermark_apply == '1') {
            $applicationConfig = ApplicationConfig::firstOrFail();

            \Log::info('check watermark exists', [$this->checkWaterMarkExists($applicationConfig)]);
            if ($this->checkWaterMarkExists($applicationConfig)) {
                $mediaOption = $mediaOption->setWaterMark($this->getWaterMarkImage($applicationConfig), $applicationConfig->watermark_position, 100);
            }
        }
        $mediaOption = $mediaOption->get();
        $subMogouImage = new SubMogouImage;

        $rotation_key = $this->parentMogou->rotation_key;
        $table = $subMogouImage->getPartition($rotation_key);
        $subMogouImage->setTable($table);

        foreach ($request->upload_files as $file) {
            $obj['path'] = $this->storeMedia($file['file'], $path, true, $mediaOption);
            $obj['sub_mogou_id'] = $subMogou->id;
            $obj['mogou_id'] = $parent_mogou;

            (clone $subMogouImage)->create($obj);
        }

        return $subMogou;
    }

    public function removeStorageFile(array $data): void
    {
        $subMogou = $this->setSubMogouTable('id', $data['mogou_id']);
        $subMogou = $subMogou->where('id', $data['sub_mogou_id'])->firstOrFail();

        $subMogouImage = new SubMogouImage;
        $rotation_key = $this->parentMogou->rotation_key;
        $table = $subMogouImage->getPartition($rotation_key);
        $subMogouImage->setTable($table);
        $fileRecord = $subMogouImage->where('id', $data['image_id'])->firstOrFail();

        $this->removeMedia("public/mogou/{$data['mogou_id']}/{$data['sub_mogou_id']}/{$fileRecord->path}");

    }

    public function checkWaterMarkExists(ApplicationConfig $app): bool
    {
        $provider = config('hydrastorage.provider');

        $water_mark = $app->getRawOriginal('water_mark');
        // if water mark is null
        if ($water_mark == null) {
            return false;
        }

        return Storage::disk($provider)->exists('public/config/'.$water_mark);
    }

    public function getWaterMarkImage(ApplicationConfig $app): ?string
    {
        $water_mark = $app->getRawOriginal('water_mark');

        $provider = config('hydrastorage.provider');
        $wm = Storage::disk($provider)->get('public/config/'.$water_mark);

        return $wm;
    }
}
