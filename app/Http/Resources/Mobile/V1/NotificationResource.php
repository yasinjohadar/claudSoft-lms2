<?php

namespace App\Http\Resources\Mobile\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * إشعار من صندوق الطالب (نفس جدول gamification_notifications الذي تعرضه لوحة الطالب في الويب).
 *
 * @mixin \App\Models\GamificationNotification
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $metadata = is_array($this->metadata) ? $this->metadata : [];

        return [
            'id' => (int) $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->message,
            'icon' => $this->icon,
            'action_url' => $this->action_url,
            'related_type' => $this->related_type,
            'related_id' => $this->related_id !== null ? (int) $this->related_id : null,
            'image_url' => $metadata['image_url'] ?? null,
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
