<?php

namespace App\Services\BotPublisher\Bots;

use App\Services\BotPublisher\Bots\Telegram\SingleChannel;
use WeStacks\TeleBot\Laravel\TeleBot;
use GuzzleHttp\Client;

class BasePublisher
{
    protected TeleBot|null $serviceBot;
    protected Client $httpClient;

    public function __construct()
    {
        $this->httpClient = $this->createHttpClient();
    }

    public function self(): mixed{
        return $this->serviceBot;
    }

    public function getPublisherDetail(): mixed
    {
        $botDetails = $this->individualChannel('-1002198423534')->getTotalMembers();
        return json_encode($botDetails);
    }

    public function individualChannel(string $channel_id): SingleChannel
    {
        return new SingleChannel($this->serviceBot, $channel_id);
    }


    protected function createHttpClient(): Client
    {
        return new Client([
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }
}
