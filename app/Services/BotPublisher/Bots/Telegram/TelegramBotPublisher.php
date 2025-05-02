<?php

namespace App\Services\BotPublisher\Bots\Telegram;

use App\Contracts\PublisherInterface;
use App\Services\BotPublisher\Bots\BasePublisher;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;
use WeStacks\TeleBot\TeleBot;

class TelegramBotPublisher extends BasePublisher implements PublisherInterface
{
    protected TeleBot $serviceBot;

    public function __construct(protected string $api_key)
    {
        parent::__construct();
        $this->serviceBot = new TeleBot($api_key);
    }

    public static function provider(string $api_key): self
    {
        return new self($api_key);
    }

    public function checkIsExistOnProvider(string $id): bool
    {
        $response = $this->makeTelegramRequest("bot{$id}/getMe");
        return $response->getStatusCode() === 200;
    }

    public function getChannelsWithSubscribers(Collection $channels): mixed
    {
        return $channels->map(function ($channel) {
            $channel->providers = $this->individualChannel($channel->token_key)->getChatInfo();
            return $channel;
        });
    }

    public function checkChannelExistOnProvider(int $id, string $channel_token_key): mixed
    {
        return  $this->individualChannel($channel_token_key)->getChatDetail();
    }

    protected function makeTelegramRequest(string $endpoint, array $queryParams = []): \Psr\Http\Message\ResponseInterface
    {
        $url = "https://api.telegram.org/{$endpoint}";
        return $this->httpClient->get($url, ['query' => $queryParams]);
    }

  
}
