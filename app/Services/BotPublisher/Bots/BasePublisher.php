<?php

namespace App\Services\BotPublisher\Bots;

use App\Services\BotPublisher\Bots\Telegram\SingleChannel;
use GuzzleHttp\Client;
use Log;
use WeStacks\TeleBot\TeleBot;

class BasePublisher
{
    protected string $providerName;

    protected ?TeleBot $serviceBot;

    protected Client $httpClient;

    protected string $clientAppUrl;

    public function __construct()
    {
        $this->httpClient = $this->createHttpClient();
        $this->clientAppUrl = app()->environment() == 'production' ? config('control.client_app_url') : 'https://mgn-mu.vercel.app';

    }

    public function self(): mixed
    {
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

    public function errorLog(string $message, ?array $context = []): void
    {
        Log::channel('automation')->error("{$this->providerName} - {$message}", $context);
    }

    public function outputLog(string $message, string $level = 'info', ?array $context = []): void
    {
        Log::channel('automation')->$level("{$this->providerName} - {$message}", $context);
    }
}
