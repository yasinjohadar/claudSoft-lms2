<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AIProvider extends Model
{
    use HasFactory;

    protected $table = 'ai_providers';

    protected $fillable = [
        'name',
        'type',
        'api_key',
        'api_url',
        'model_name',
        'config',
        'is_active',
        'is_default',
        'priority',
        'usage_stats',
        'rate_limits',
    ];

    protected $casts = [
        'config' => 'array',
        'usage_stats' => 'array',
        'rate_limits' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'priority' => 'integer',
    ];

    protected $hidden = [
        'api_key',
    ];

    /**
     * Get encrypted API key
     */
    public function getApiKeyAttribute($value)
    {
        if (empty($value)) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // Return as-is if decryption fails (might be plain text)
        }
    }

    /**
     * Set encrypted API key
     */
    public function setApiKeyAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['api_key'] = Crypt::encryptString($value);
        }
    }

    /**
     * Get all requests for this provider
     */
    public function requests()
    {
        return $this->hasMany(AIRequest::class, 'provider_id');
    }

    /**
     * Scope for active providers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default provider
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Set as default provider (unset others)
     */
    public function setAsDefault(): void
    {
        // Unset other defaults
        static::where('id', '!=', $this->id)->update(['is_default' => false]);
        
        // Set this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Test connection to provider
     */
    public function testConnection(): array
    {
        try {
            $manager = app(\App\Services\AI\AIManager::class);
            $provider = $manager->provider($this->name);
            return $provider->testConnection();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
