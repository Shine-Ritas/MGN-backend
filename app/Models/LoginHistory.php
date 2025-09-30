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

use Database\Factories\LoginHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    /** @use HasFactory<LoginHistoryFactory> */
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'login_at' => 'datetime',
    ];
}
