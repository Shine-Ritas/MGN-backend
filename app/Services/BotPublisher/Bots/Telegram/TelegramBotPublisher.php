<?php

namespace App\Services\BotPublisher\Bots\Telegram;

use App\Contracts\PublisherInterface;
use App\Models\Mogou;
use App\Models\SocialChannel;
use App\Models\SubMogou;
use App\Services\BotPublisher\Bots\BasePublisher;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;
use WeStacks\TeleBot\TeleBot;

class TelegramBotPublisher extends BasePublisher implements PublisherInterface
{

    public function __construct(protected string $api_key)
    {
        parent::__construct();
        $this->serviceBot = new TeleBot($api_key);
        $this->providerName = 'Telegram';
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
        return $this->individualChannel($channel_token_key)->getChatDetail();
    }

    protected function makeTelegramRequest(string $endpoint, array $queryParams = []): \Psr\Http\Message\ResponseInterface
    {
        $url = "https://api.telegram.org/{$endpoint}";
        return $this->httpClient->get($url, ['query' => $queryParams]);
    }

    public function publishContent(Mogou|SubMogou $content, SocialChannel $socialChannel,?string $textContent = ''):bool
    {
        try {
            $chapterHrefHtml = "";
            $mougou = null;
            if ($content instanceof Mogou) {
                $latestThreeChapters = $content->subMogous()->latest('chapter_number')->limit(3)->get();
                $mougou = $content;
                $title = $content->title;
                $reply_url = "{$this->clientAppUrl}/mogou/{$mougou->slug}";
            } else {
                $latestThreeChapters = $content->mogou->subMogous()->latest('chapter_number')->limit(3)->get();
                $mougou = $content->mogou;
                $title = "$mougou->title - Chapter {$content->chapter_number}";
                $reply_url = "{$this->clientAppUrl}/mogou/{$mougou->slug}/chapter/{$content->slug}";
            }

            foreach ($latestThreeChapters as $chapter) {
                $chapterHrefHtml .= "<a href='{$this->clientAppUrl}/mogou/{$mougou->slug}/chapter/{$chapter->slug}'>Chapter {$chapter->chapter_number}</a>\n";
            }
            $chapterHrefHtml = "<b>Chapters:</b>\n" . $chapterHrefHtml;

            if($textContent){
                $textContent = "\n\n" . $textContent . "\n\n";
            }

            $this->serviceBot->sendPhoto([
                'chat_id' => $socialChannel->token_key,
                'photo' => $mougou->cover,
                'parse_mode' => 'html',
                'caption' => "{$title}\n\n{$content->description}{$textContent}{$chapterHrefHtml}",
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'Read Here', 'url' => $reply_url],
                        ],
                    ],
                ],
            ]);

            $this->outputLog("{$socialChannel->name} - {$content->id} at - " . now()->toDateTimeString());

            return true;

        } catch (\Exception $e) {
            $this->outputLog("{$socialChannel->name} - {$content->id} at - " . now()->toDateTimeString(),'error');
            $this->outputLog($e->getMessage(),'error');
            return false;
        }
    }
}
