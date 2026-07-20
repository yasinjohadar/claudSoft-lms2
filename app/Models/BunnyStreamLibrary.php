<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BunnyStreamLibrary extends Model
{
    protected $fillable = [
        'library_id',
        'library_name',
        'token_security_key',
        'api_key',
        'is_active',
    ];

    protected $casts = [
        'token_security_key' => 'encrypted',
        'api_key' => 'encrypted',
        'is_active' => 'boolean',
    ];

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class, 'bunny_stream_library_id');
    }

    public function hasTokenSecurityKey(): bool
    {
        return is_string($this->token_security_key) && $this->token_security_key !== '';
    }

    public function displayLabel(): string
    {
        return "{$this->library_name} ({$this->library_id})";
    }
}
