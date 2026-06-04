<?php

if (!function_exists('storage_url')) {
    /**
     * Get the URL for a file stored in storage/app/public
     * Works correctly with/without /public in URL
     * 
     * @param string $path The file path relative to storage/app/public
     * @return string The full URL to the file
     */
    function storage_url($path)
    {
        // Remove 'storage/' prefix if exists (already handled by symbolic link)
        $cleanPath = ltrim($path, '/');
        $cleanPath = str_replace('storage/', '', $cleanPath);
        
        // Use asset() which works correctly with symbolic links
        return asset('storage/' . $cleanPath);
    }
}

if (!function_exists('resolve_storage_image_url')) {
    /**
     * Resolve a stored image path to a public URL (S3/CDN or local).
     * Does not require fileExists — S3 HeadObject may fail while the object is still readable.
     *
     * @param  array<int, string>  $disks
     */
    function resolve_storage_image_url(array $disks, ?string $imagePath, string $defaultUrl): string
    {
        if (empty($imagePath)) {
            return $defaultUrl;
        }

        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $imagePath;
        }

        $imagePath = ltrim($imagePath, '/');
        $imagePath = preg_replace('#^storage/#', '', $imagePath);

        $pathsToTry = array_values(array_unique(array_filter([
            $imagePath,
            ! str_contains($imagePath, '/') ? 'courses/thumbnails/' . $imagePath : null,
            ! str_contains($imagePath, '/') ? 'courses/images/' . $imagePath : null,
        ])));

        try {
            $storageHelper = app(\App\Services\Storage\StorageHelperService::class);

            foreach ($disks as $disk) {
                foreach ($pathsToTry as $path) {
                    $url = $storageHelper->getFileUrl($disk, $path);
                    if (! empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                        return $url;
                    }

                    try {
                        $url = $storageHelper->getDisk($disk)->url($path);
                        if (! empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                            return $url;
                        }
                    } catch (\Exception $e) {
                        // try next path / disk
                    }
                }
            }
        } catch (\Exception $e) {
            // fall through to local / default
        }

        $localPath = storage_path('app/public/' . $imagePath);
        if (file_exists($localPath)) {
            return asset('storage/' . $imagePath);
        }

        return $defaultUrl;
    }
}

if (!function_exists('blog_image_url')) {
    /**
     * Get the URL for a blog post featured image
     * Works with S3 and local storage through the dynamic storage system
     * 
     * @param string|null $imagePath The image path from database
     * @return string The full URL to the image
     */
    function blog_image_url($imagePath)
    {
        return resolve_storage_image_url(
            ['blog_images', 'public'],
            $imagePath,
            asset('frontend/assets/images/placeholder.jpg')
        );
    }
}

if (!function_exists('course_image_url')) {
    /**
     * Get the URL for a course image
     * Works with S3 and local storage through the dynamic storage system
     * 
     * @param string|null $imagePath The image path from database
     * @return string The full URL to the image
     */
    function course_image_url($imagePath)
    {
        return resolve_storage_image_url(
            ['course_thumbnails', 'public'],
            $imagePath,
            asset('frontend/assets/img/default-course.jpg')
        );
    }
}

if (!function_exists('storage_disk_url')) {
    /**
     * Get the URL for a file stored in a specific disk (dynamic storage)
     * 
     * @param string $disk The disk name (e.g., 'public', 'images')
     * @param string $path The file path
     * @return string The full URL to the file
     */
    function storage_disk_url(string $disk, string $path): string
    {
        try {
            $storageHelper = app(\App\Services\Storage\StorageHelperService::class);
            $url = $storageHelper->getFileUrl($disk, $path);
            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }
        } catch (\Exception $e) {
            // Fallback to default storage URL
        }
        
        // Fallback to asset if dynamic storage fails
        return asset('storage/' . ltrim($path, '/'));
    }
}

if (!function_exists('student_profile_photo_url')) {
    /**
     * Get the URL for a student profile photo
     * Works with S3 and local storage through the dynamic storage system
     * 
     * @param \App\Models\User|null $student The student user model
     * @param string|null $photoPath The photo path from database
     * @return string The full URL to the photo or empty string
     */
    function student_profile_photo_url($student = null, $photoPath = null): string
    {
        // If no photo path provided, try to get from student
        if (empty($photoPath)) {
            if ($student && !empty($student->photo)) {
                $photoPath = $student->photo;
            } else {
                return '';
            }
        }

        // If already a full URL, return as-is
        if (filter_var($photoPath, FILTER_VALIDATE_URL)) {
            return $photoPath;
        }

        // Clean the path
        $photoPath = ltrim($photoPath, '/');

        // Try dynamic storage system
        try {
            $storageHelper = app(\App\Services\Storage\StorageHelperService::class);
            
            // Get the disk (will use cloud storage if available)
            $disk = $storageHelper->getDisk('public');
            
            // Check if file exists on the active storage
            if ($disk->exists($photoPath)) {
                // Try to get URL through the storage manager
                $url = $storageHelper->getFileUrl('public', $photoPath);
                if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                    return $url;
                }
                
                // If URL generation failed but file exists, generate URL manually
                $url = $disk->url($photoPath);
                if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                    return $url;
                }
            }
        } catch (\Exception $e) {
            // Continue to fallback
        }

        // Fallback: check if file exists in local storage
        $localPath = storage_path('app/public/' . $photoPath);
        if (file_exists($localPath)) {
            return asset('storage/' . $photoPath);
        }

        // Last fallback: return asset URL (might work if storage:link exists)
        return asset('storage/' . $photoPath);
    }
}
