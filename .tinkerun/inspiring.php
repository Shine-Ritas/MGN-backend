<?php

use App\Http\Controllers\Api\Admin\MogouController;
use App\Models\Mogou;
use App\Services\BotPublisher\GetBotServices;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;

Inspiring::quote();

dd((New GetBotServices())->getPosts(1)->toArray());