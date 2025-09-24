<?php

namespace App\Services\Publishing;

use App\Models\BotPublisherPost;
use App\Models\Mogou;
use App\Models\SocialChannel;
use App\Models\SubMogou;
use App\Repo\Admin\SubMogouRepo\MogouPartitionFind;
use App\Services\BotPublisher\GetBotServices;

class PublishingService
{
    public function __construct() {}

    public function getContentModel(string $mogou_slug, ?string $sub_mogou_slug, string $type): Mogou|SubMogou|null
    {
        if ($type == 'mogou') {
            return Mogou::where('slug', $mogou_slug)->first();
        }

        if ($type == 'sub_mogou') {
            $sub_mogou = MogouPartitionFind::getSubMogou('slug', $mogou_slug);

            return $sub_mogou->where('slug', $sub_mogou_slug)->firstOrFail();
        }

        return null;
    }

    public function publishContent(Mogou|SubMogou $mougou, array|string $socialChannel, ?string $content = ''): bool
    {

        if ($socialChannel == 'all') {
            $socialChannels = SocialChannel::all();
            foreach ($socialChannels as $socialChannel) {
                $this->upload($socialChannel, $mougou, $content);
            }
        } else {
            $socialChannel = SocialChannel::whereIn('id', $socialChannel)->get();
            $socialChannel = $socialChannel->unique('token_key');
            foreach ($socialChannel as $channel) {
                $this->upload($channel, $mougou, $content);
            }
        }

        return true;
    }

    public function upload(SocialChannel $socialChannel, Mogou|SubMogou $modal, ?string $content = ''): void
    {
        $botProvider = $socialChannel->botProvider;
        $bot = ((new GetBotServices)->getBot((int) $botProvider->id))->getPublisher();
        $publishing = $bot->publishContent($modal, $socialChannel, $content);

        if (! $publishing) {
            throw new \Exception('Publishing failed');
        }

        $Mogou = $modal instanceof Mogou ? $modal : $modal->mogou;

        $this->savedToBotPublisher([
            'bot_publisher_id' => $botProvider->id,
            'mogou_id' => $Mogou->id,
            'sub_mogou_id' => $modal->id,
            'social_channel_id' => $socialChannel->id,
            'data' => $content,
        ]);
    }

    public function savedToBotPublisher(array $data): void
    {
        BotPublisherPost::create([
            'bot_publisher_id' => $data['bot_publisher_id'],
            'mogou_id' => $data['mogou_id'],
            'sub_mogou_id' => $data['sub_mogou_id'] ?? null,
            'social_channel_id' => $data['social_channel_id'],
            'data' => $data['data'] ?? [],
        ]);
    }
}
