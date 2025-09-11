<?php

namespace App\Repo\Admin\ApplicationConfig;

use App\Models\ApplicationConfig;
use HydraStorage\HydraStorage\Traits\HydraMedia;
use Illuminate\Http\Request;
use UnexpectedValueException;

class ApplicationConfigUploadRepo
{
    use HydraMedia;

    private array $validUploadProperties = [
        'logo',
        'cover_photo',
        'water_mark',
        'intro_a',
        'outro_a',
        'intro_b',
        'outro_b',
    ];

    public function upload(Request $request): ApplicationConfig
    {
        $app = ApplicationConfig::firstOrFail();

        \Log::info('log', [$request->all()]);

        foreach ($this->validUploadProperties as $property) {
            if ($request->hasFile($property)) {
                $app = $this->handleFileUpload($app, $request, $property);
            }
        }

        $app->fill($request->only('title', 'daily_subscriptions_target', 'daily_traffic_target', 'monthly_subscriptions_target', 'user_side_is_maintenance_mode', 'watermark_position'));
        $app->save();

        return $app;
    }

    private function handleFileUpload(ApplicationConfig $app, Request $request, string $property): ApplicationConfig
    {
        $oldPath = $app->getRawOriginal($property);
        $this->removeMedia("public/config/{$oldPath}");
        $mediaResult = $this->storeMedia($request->file($property), 'config');
        if (! is_string($mediaResult)) {
            throw new UnexpectedValueException('Expected a string path but got an array.');
        }
        $app->{$property} = $mediaResult;
        $app->save();

        return $app;
    }
}
