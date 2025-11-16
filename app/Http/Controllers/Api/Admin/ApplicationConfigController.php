<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repo\Admin\ApplicationConfig\ApplicationConfigUploadRepo;
use App\Services\ApplicationConfig\CacheApplicationConfigService;
use App\Traits\CacheResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationConfigController extends Controller
{
    use CacheResponse;

    private string $cacheKey;

    public function __construct(protected ApplicationConfigUploadRepo $applicationConfigUploadRepo)
    {
        $this->cacheKey = $this->generateCacheKey('application_config');
    }

    public function index(): JsonResponse
    {
        $app = (new CacheApplicationConfigService)->getApplicationConfig();

        return response()->json($app);
    }

    public function update(Request $request): JsonResponse
    {
        $app = $this->applicationConfigUploadRepo->upload(request: $request);
        $key = $this->cacheKey;

        $this->forgetCache($key);

        return response()->json($app);
    }
}
