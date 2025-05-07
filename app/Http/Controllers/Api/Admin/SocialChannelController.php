<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enum\SocialMediaType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SocialChannelActionRequest;
use App\Models\SocialChannel;
use App\Repo\Admin\SocialChannel\SocialChannelActionRepo;
use App\Services\SocialChannel\SocialChannelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialChannelController extends Controller
{
    public function __construct(protected SocialChannelActionRepo $socialChannelActionRepo)
    {
    }

    public function index(): JsonResponse
    {
        $type = ucfirst(request('type'));
        $channels = (new SocialChannelService())->getSocialChannels(SocialMediaType::getByLabel($type));

        return response()->json([
            'success' => true,
            'channels' => $channels,
        ]);
    }
    
    public function create(SocialChannelActionRequest $request): JsonResponse
    {
        $channel = $this->socialChannelActionRepo->create($request);

        return response()->json([
            'success' => true,
            'channel' => $channel,
        ]);
    }
}
