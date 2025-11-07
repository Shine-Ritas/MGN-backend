<?php

namespace App\Services\BotPublisher;

use App\Enum\SocialMediaType;
use App\Models\BotPublisher;
use App\Models\BotPublisherPost;
use App\Services\BotPublisher\Publisher\LinedPublisher;
use App\Services\BotPublisher\Publisher\SocialPublisher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class GetBotServices
{
    public function __construct() {}

    /**
     * getBotPublishers
     *
     * @return Collection<int, BotPublisher>
     */
    public function getBotPublishers(string $type): Collection
    {

        $labelType = SocialMediaType::getByLabel($type);

        $botPublishers = BotPublisher::where('type', $labelType)->with('socialChannels')->get();

        return $botPublishers;
    }

    public function getBotPublisher(int $id): BotPublisher
    {

        $botPublisher = BotPublisher::where('id', $id)->first();

        $SocialProviderChannels = (new SocialPublisher($botPublisher->token_key, $botPublisher->type->value))->get()->getChannelsWithSubscribers();

        $botPublisher->channels = $SocialProviderChannels;

        return $botPublisher;
    }

    /**
     * Summary of getComments
     *
     * @return LengthAwarePaginator<BotPublisherPost>
     */
    public function getPosts(int $id): LengthAwarePaginator
    {
        $botPublisher = BotPublisher::where('id', $id)->first();

        $posts = BotPublisherPost::where('bot_publisher_id', $botPublisher->id)
            ->with('mogou:id,title,slug,status', 'subMogou:id,title,slug,status', 'socialChannel:id,name,token_key,meta_data,type')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $posts;
    }

    public function getBot(int $id): LinedPublisher
    {
        // find the bot publisher first
        $botPublisher = BotPublisher::where('id', $id)->first();

        // then inject the token key and type to the SocialPublisher to get what kind of publisher it is
        $linePublisher = (new SocialPublisher($botPublisher->token_key, $botPublisher->type->value))->get();

        return $linePublisher;
    }
}
