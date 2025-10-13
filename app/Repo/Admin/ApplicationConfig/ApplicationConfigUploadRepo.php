<?php

namespace App\Repo\Admin\ApplicationConfig;

use App\Models\ApplicationConfig;
use HydraStorage\HydraStorage\Traits\HydraMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        // ✅ Validation rules for uploads
        $rules = [
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:300|dimensions:min_width=150,min_height=50',
            'cover_photo' => 'nullable|image|max:1024',
            'water_mark' => 'nullable|image|max:300',
            'intro_a' => 'nullable|file|mimes:mp4,webm|max:51200',
            'outro_a' => 'nullable|file|mimes:mp4,webm|max:51200',
            'intro_b' => 'nullable|file|mimes:mp4,webm|max:51200',
            'outro_b' => 'nullable|file|mimes:mp4,webm|max:51200',
        ];

        $messages = [
            'logo.dimensions' => 'Logo must be at least 150x50 pixels.',
            'logo.max' => 'Logo file must not exceed 300 KB.',
            'logo.mimes' => 'Logo must be a PNG, JPG, or SVG file.',
        ];

        Validator::make($request->all(), $rules, $messages)->validate();

        // Fetch the first application configuration
        $app = ApplicationConfig::firstOrFail();

        \Log::info('Application Config Upload Request', [$request->all()]);

        foreach ($this->validUploadProperties as $property) {
            if ($request->hasFile($property)) {
                $app = $this->handleFileUpload($app, $request, $property);
            }
        }

        $app->fill($request->only([
            'title',
            'daily_subscriptions_target',
            'daily_traffic_target',
            'monthly_subscriptions_target',
            'user_side_is_maintenance_mode',
            'watermark_position',
            'prefix_active',
        ]));

        $app->save();

        return $app;
    }

    private function handleFileUpload(ApplicationConfig $app, Request $request, string $property): ApplicationConfig
    {
        $oldPath = $app->getRawOriginal($property);

        // Remove old file if exists
        if ($oldPath) {
            $this->removeMedia("public/config/{$oldPath}");
        }

        $mediaResult = $this->storeMedia($request->file($property), 'config');

        if (!is_string($mediaResult)) {
            throw new UnexpectedValueException('Expected a string path but got an array.');
        }

        $app->{$property} = $mediaResult;
        $app->save();

        return $app;
    }
}
