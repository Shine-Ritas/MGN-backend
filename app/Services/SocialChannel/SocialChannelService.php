<?php

namespace App\Services\SocialChannel;

use App\Models\BotPublisher;
use App\Services\BotPublisher\Publisher\SocialPublisher;

class SocialChannelService
{
    public function getSocialChannels(int $type): array
    {
        $bots = BotPublisher::where('type', $type)->get();

        $channels = [];

        $bots->each(function ($bot) use (&$channels) {
            $botChannels = (new SocialPublisher($bot->token_key, $bot->type->value))->get()->getChannelsWithSubscribers()->toArray();

            $channels = array_merge($channels, $botChannels);
        });

        return $channels;
    }
}
