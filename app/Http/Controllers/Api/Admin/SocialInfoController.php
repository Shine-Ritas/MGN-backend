<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialInfoRequest;
use App\Models\SocialInfo;
use App\Repo\Admin\SocialInfo\SocialInfoRepo;
use App\Traits\CacheResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialInfoController extends Controller
{
    use CacheResponse;

    private string $applicationCacheKey = '';

    private string $applicationConfigCacheKey = '';

    public function __construct(protected SocialInfoRepo $socialInfoRepo)
    {
        $this->applicationCacheKey = $this->generateCacheKey('social_info');
        $this->applicationConfigCacheKey = $this->generateCacheKey('application_config');
    }

    /**
     * index
     *
     * @return Collection<int, SocialInfo>
     */
    public function index(): Collection
    {
        return $this->socialInfoRepo->all();
    }

    public function store(SocialInfoRequest $request): JsonResponse
    {

        $socialInfo = $this->socialInfoRepo->create($request->all());

        $this->forgetCache([$this->applicationCacheKey, $this->applicationConfigCacheKey]);

        return response()->json(
            [
                'success' => true,
                'social_info' => $socialInfo,
            ], 201
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $ck = $this->generateCacheKey('application_config');

        $socialInfo = $this->socialInfoRepo->update($id, $request->all());

        $this->forgetCache([$this->applicationCacheKey, $this->applicationConfigCacheKey]);

        return response()->json(
            [
                'success' => true,
                'social_info' => $socialInfo,
            ], 200
        );
    }

    public function delete(Request $request, string $id): JsonResponse
    {
        $this->socialInfoRepo->delete($id);

        $this->forgetCache($this->applicationCacheKey);

        return response()->json(
            [
                'success' => true,
            ], 200
        );
    }

    public function social_infos(): JsonResponse
    {
        $data = $this->socialInfoRepo->getSocialInfoByType(request('type'));

        $this->forgetCache([$this->applicationCacheKey, $this->applicationConfigCacheKey]);

        return response()->json(
            [
                'success' => true,
                'data' => $data,
            ], 200
        );
    }
}
