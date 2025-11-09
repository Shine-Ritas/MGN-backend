<?php

use App\Http\Controllers\Api\Admin\MogouController;
use App\Models\BotPublisher;
use App\Models\Mogou;
use App\Services\BotPublisher\Bots\Telegram\TelegramBotPublisher;
use App\Services\BotPublisher\GetBotServices;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

Inspiring::quote();


$response = Http::post('https://e40cb1f24d22.ngrok-free.app/api/v1/telegram/set-webhook', [
  
]);

dd($response->json());