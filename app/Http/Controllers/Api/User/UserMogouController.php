<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Services\Mogou\UserMogouService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserMogouController extends Controller
{
    public function __construct(
        protected UserMogouService $mogouService
    ) {}

    /**
     * Display the specified Mogou.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = auth('sanctum')->user();
        $userId = $user?->id;
        $data = $this->mogouService->getMogouDetails($request->mogou, $userId);

        return response()->json($data);
    }

    /**
     * Get more chapters of the specified Mogou.
     */
    public function getMoreChapters(Request $request): JsonResponse
    {
        $chapters = $this->mogouService->getAllChapters($request->mogou);

        return response()->json(['chapters' => $chapters]);
    }

    /**
     * Get related posts for the specified Mogou.
     */
    public function relatedPostPerMogou(Request $request): JsonResponse
    {
        $relatedMogous = $this->mogouService->getRelatedMogous($request->mogou);

        return response()->json(['mogous' => $relatedMogous]);
    }

    /**
     * Get chapter details with images and navigation.
     */
    public function getChapter(Request $request): JsonResponse
    {
        $data = $this->mogouService->getChapterDetails($request->mogou, $request->chapter);

        return response()->json($data);
    }

    public function randomMogou(): JsonResponse
    {
        $mogou = $this->mogouService->getRandomMogou();

        return response()->json(['mogou' => $mogou]);
    }

    /**
     * Record chapter view event.
     */
    public function getViewed(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $this->mogouService->recordChapterView($request->mogou, $request->chapter);

            DB::commit();

            return response()->json(['message' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'failed', 'error' => $e->getMessage()], 500);
        }
    }
}
