<?php

/**
 * Project: MGN-Backend
 * Owner: @Htet_Shine
 * Email: whoishsh@gmail.com
 *
 * This file is part of the proprietary source code owned by @Htet_Shine.
 * Unauthorized copying, distribution, or modification is prohibited.
 */

namespace App\Models;

use Database\Factories\BotSocialChannelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotSocialChannel extends Model
{
    /** @use HasFactory<BotSocialChannelFactory> */
    use HasFactory;

    protected $fillable = [
        'bot_publisher_id',
        'social_channel_id',
    ];
}
