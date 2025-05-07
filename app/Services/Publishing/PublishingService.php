<?php

namespace App\Services\Publishing;

use App\Enum\SocialMediaType;
use App\Models\Mogou;
use App\Models\SocialChannel;
use App\Models\SubMogou;
use App\Repo\Admin\SubMogouRepo\MogouPartitionFind;
use App\Services\BotPublisher\GetBotServices;

class PublishingService
{
    public function __construct()
    {
    }

    public function getContentModel(string $mogou_slug,string $sub_mogou_slug,string $type): Mogou|SubMogou|null
    {
        if($type == "mogou"){
            return Mogou::where('slug',$mogou_slug)->first();
        }

        if($type == "sub_mogou"){
            $sub_mogou = MogouPartitionFind::getSubMogou("slug", $mogou_slug);
            return $sub_mogou->where('slug',$sub_mogou_slug)->first();
        }

        return null;
    }


    public function publishOneContent(Mogou|SubMogou $mougou,string|SocialChannel $socialChannel,string $content=''):string
    {
        if($socialChannel == "all"){
            $socialChannels = SocialChannel::all();
            foreach($socialChannels as $socialChannel){
                $botProvider = $socialChannel->botProvider;
                $bot = ((new GetBotServices())->getBot((int) $botProvider->id))->getPublisher();
                $bot->publishContent($mougou,$socialChannel,$content);
            }
        }else{
            $botProvider = $socialChannel->botProvider;
            $bot = ((new GetBotServices())->getBot((int) $botProvider->id))->getPublisher();
            $bot->publishContent($mougou,$socialChannel,$content);
        }

        return 'success';
    }
}
