<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvolutionInstance extends Model
{
    protected $fillable = [
        'instance_name',
        'evolution_uuid',
        'connection_status',
        'owner_jid',
        'profile_name',
        'phone_number',
        'profile_pic_url',
        'qr_code',
        'is_default',
        'rotation_enabled',
        'last_used_at',
        'metadata',
        'connected_at',
        'disconnected_at',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'rotation_enabled' => 'boolean',
        'metadata' => 'array',
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function isConnected(): bool
    {
        return $this->connection_status === 'open';
    }

    public function scopeConnected($query)
    {
        return $query->where('connection_status', 'open');
    }

    public function scopeRotationEligible($query)
    {
        return $query->connected()->where('rotation_enabled', true);
    }

    public static function rotationPoolCount(): int
    {
        return static::rotationEligible()->count();
    }

    public static function syncFromApiArray(array $instance, bool $markDefault = false): self
    {
        $name = (string) ($instance['name'] ?? '');
        $status = strtolower((string) ($instance['connectionStatus'] ?? 'close'));

        $model = static::updateOrCreate(
            ['instance_name' => $name],
            [
                'evolution_uuid' => $instance['id'] ?? null,
                'connection_status' => $status === 'open' ? 'open' : $status,
                'owner_jid' => $instance['ownerJid'] ?? null,
                'profile_name' => $instance['profileName'] ?? null,
                'phone_number' => $instance['number'] ?? null,
                'profile_pic_url' => $instance['profilePicUrl'] ?? null,
                'metadata' => [
                    'integration' => $instance['integration'] ?? null,
                    'counts' => $instance['_count'] ?? null,
                ],
                'connected_at' => $status === 'open' ? now() : null,
                'disconnected_at' => $status === 'open' ? null : now(),
            ]
        );

        if ($markDefault) {
            static::where('id', '!=', $model->id)->update(['is_default' => false]);
            $model->update(['is_default' => true]);
        }

        return $model;
    }

    public static function defaultInstance(): ?self
    {
        return static::where('is_default', true)->first()
            ?? static::where('connection_status', 'open')->latest()->first();
    }
}
