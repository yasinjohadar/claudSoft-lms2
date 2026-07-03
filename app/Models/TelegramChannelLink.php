<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramChannelLink extends Model
{
    public const TYPE_GROUP = 'group';

    public const TYPE_CHANNEL = 'channel';

    public const ENTITY_COURSE = 'course';

    public const ENTITY_GROUP = 'group';

    protected $fillable = [
        'entity_type', 'entity_id', 'link_type', 'telegram_chat_id',
        'title', 'invite_link', 'is_active', 'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public static function forEntity(string $entityType, int $entityId, string $linkType = self::TYPE_GROUP): ?self
    {
        return static::query()
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('link_type', $linkType)
            ->where('is_active', true)
            ->first();
    }
}
