<?php

namespace App\Models;

use App\Enum\SocialMediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property mixed $providers
 */
class SocialChannel extends Model
{
    /** @use HasFactory<\Database\Factories\SocialChannelFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'token_key',
        'type',
        'is_active',
        'meta_data',
    ];

    protected $casts = [
        'type' => SocialMediaType::class,
    ];

    protected $appends = ['bot_type'];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->meta_data = json_encode($model->meta_data);
        });

        static::updating(function ($model) {
            $model->meta_data = json_encode($model->meta_data);
        });
    }

    public function getCreatedAtAttribute(string $value): string
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }

    public function getBotTypeAttribute(): string
    {
        return SocialMediaType::getKey($this->type);
    }

    /** @phpstan-ignore-next-line */
    public function botProvider(): HasOneThrough
    {
        return $this->hasOneThrough(BotPublisher::class, BotSocialChannel::class, 'social_channel_id', 'id', 'id', 'bot_publisher_id');
    }

    public function getMetaDataAttribute(?string $value): ?array
    {
        if (! $value) {
            return null;
        }

        return json_decode($value, true);
    }
}
