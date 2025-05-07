<?php

namespace App\Services\SocialChannel;

use App\Enum\SocialMediaType;
use App\Models\BotPublisher;
use App\Models\SocialChannel;
use App\Services\BotPublisher\Publisher\SocialPublisher;
use Illuminate\Database\Eloquent\Collection;

class SocialChannelService
{
    public function getSocialChannels(int $type): array
    {
        $bots = BotPublisher::where('type', $type)->get();

        $channels = [];

        $bots->each(function($bot) use (&$channels) {
            $botChannels = (new SocialPublisher($bot->token_key, $bot->type->value))->get()->getChannelsWithSubscribers()->toArray();

            $channels = array_merge($channels, $botChannels);
        });

        return $channels;
    }
}
