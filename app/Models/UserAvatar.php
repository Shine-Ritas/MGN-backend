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

use Database\Factories\UserAvatarFactory;
use HydraStorage\HydraStorage\Traits\HydraMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAvatar extends Model
{
    /** @use HasFactory<UserAvatarFactory> */
    use HasFactory, HydraMedia;

    protected $fillable = [
        'avatar_name',
        'avatar_path',
    ];

    protected $appends = ['avatar_url_path'];

    public function getAvatarUrlPathAttribute(): string
    {
        return $this->getMedia($this->avatar_path, 'public/user_avatars');
    }
}
