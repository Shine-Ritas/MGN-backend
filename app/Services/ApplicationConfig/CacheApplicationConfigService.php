<?php

namespace App\Services\ApplicationConfig;

use App\Enum\SocialInfoType;
use App\Models\ApplicationConfig;
use App\Models\SocialInfo;
use App\Traits\CacheResponse;

class CacheApplicationConfigService
{
    use CacheResponse;

    private string $cacheKey;

    public function __construct()
    {
        $this->cacheKey = $this->generateCacheKey('application_config');

    }

    public function getApplicationConfig(): mixed
    {
        $key = $this->cacheKey;

        $applicationConfig = (new self)->cacheResponse(
            $key, 300, function () {
                $application = ApplicationConfig::first();
                $application->socials = SocialInfo::where('type', SocialInfoType::ReferSocial->value)->get();

                return $application;
            }
        );

        return $applicationConfig;
    }
}
