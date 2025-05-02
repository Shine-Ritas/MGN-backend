<?php

namespace App\Services\Publishing;

use App\Models\Mogou;
use App\Models\SocialChannel;
use App\Models\SubMogou;

class PublishingService
{
    public function __construct()
    {
    }


    public function publishOneContent(Mogou|SubMogou $mougou,string|SocialChannel $socialChannel)
    {
        $botProvider = $socialChannel->botProvider;
        $publisherViaBot = (new BotPublisherService())->getPublisherViaBot($botProvider);
    }
}
