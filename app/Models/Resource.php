<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'resource_type',
        'resource_source',
        'resource_url',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'is_published',
        'is_visible',
        'available_from',
        'available_until',
        'allow_download',
        'preview_available',
        'download_count',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_visible' => 'boolean',
        'allow_download' => 'boolean',
        'preview_available' => 'boolean',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    // Relationships

    /**
     * Get all of the module's course modules.
     */
    public function courseModules()
    {
        return $this->morphMany(CourseModule::class, 'modulable');
    }

    /**
     * Get the user who created the resource.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the resource.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes

    /**
     * Scope a query to only include published resources.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include visible resources.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to only include available resources based on dates.
     */
    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where(function($q) use ($now) {
            $q->whereNull('available_from')->orWhere('available_from', '<=', $now);
        })->where(function($q) use ($now) {
            $q->whereNull('available_until')->orWhere('available_until', '>=', $now);
        });
    }

    /**
     * Scope a query to only include resources by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('resource_type', $type);
    }

    // Helper Methods

    /**
     * Check if the resource is available.
     */
    public function isAvailable(): bool
    {
        $now = now();

        if ($this->available_from && $this->available_from > $now) {
            return false;
        }

        if ($this->available_until && $this->available_until < $now) {
            return false;
        }

        return true;
    }

    /**
     * Get file size in human readable format.
     */
    public function getFormattedFileSize(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->file_size;

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Increment download count.
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    /**
     * Check if resource is a document (PDF, DOC, PPT, etc.).
     */
    public function isDocument(): bool
    {
        return in_array($this->resource_type, ['pdf', 'doc', 'ppt', 'excel']);
    }

    /**
     * Check if resource is an image.
     */
    public function isImage(): bool
    {
        return $this->resource_type === 'image';
    }

    /**
     * Check if resource is audio.
     */
    public function isAudio(): bool
    {
        return $this->resource_type === 'audio';
    }

    /**
     * Check if resource is an archive.
     */
    public function isArchive(): bool
    {
        return $this->resource_type === 'archive';
    }

    /**
     * Get file extension from file name.
     */
    public function getFileExtension(): ?string
    {
        if (!$this->file_name) {
            return null;
        }

        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Check if file can be previewed in browser.
     */
    public function canPreview(): bool
    {
        if (!$this->preview_available) {
            return false;
        }

        // Common previewable file types
        $previewableTypes = ['pdf', 'image'];
        return in_array($this->resource_type, $previewableTypes);
    }

    /**
     * Get icon class based on resource type.
     */
    public function getIconClass(): string
    {
        $icons = [
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word',
            'ppt' => 'fa-file-powerpoint',
            'excel' => 'fa-file-excel',
            'image' => 'fa-file-image',
            'audio' => 'fa-file-audio',
            'archive' => 'fa-file-archive',
            'other' => 'fa-file',
        ];

        return $icons[$this->resource_type] ?? $icons['other'];
    }
}
