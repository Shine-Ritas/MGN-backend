<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublishingRequest;
use App\Services\Publishing\PublishingService;
use Illuminate\Http\JsonResponse;

class PublishingController extends Controller
{

    public function __construct(
        protected PublishingService $publishingService
    ) {
    }

    public function publishContent(PublishingRequest $request): JsonResponse
    {
        $content = $this->publishingService->getContentModel($request->mogou_slug, $request->sub_mogou_slug, $request->type);
        try {
            if ($content) {
                $this->publishingService->publishContent($content, $request->social_channel_ids, $request->text_content);
            }else{
                return response()->json(['message' => "Content not found"], 404);
            }
            return response()->json(['message' => 'Content published successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => "Publishing failed"], 500);
        }
    }
}
