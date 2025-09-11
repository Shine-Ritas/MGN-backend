<?php

namespace App\Contracts;

use App\Models\SocialChannel;
use Illuminate\Database\Eloquent\Collection;

interface PublisherInterface
{
    public function self(): mixed;

    /**
     * get the detail of the publisher
     */
    public function getPublisherDetail(): mixed;

    /**
     * status check of the bot id on related social provider
     */
    public function checkIsExistOnProvider(string $id): bool;

    /**
     * get the channels with subscribers
     *
     * @param  Collection<int, SocialChannel>  $channels
     */
    public function getChannelsWithSubscribers(Collection $channels): mixed;

    /**
     * check the channel exist on provider with bot id
     */
    public function checkChannelExistOnProvider(int $id, string $channel_token_key): mixed;
}
