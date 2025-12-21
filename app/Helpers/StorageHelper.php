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

if (!function_exists('blog_image_url')) {
    /**
     * Get the URL for a blog post featured image
     * Tries multiple methods to ensure the image is accessible
     * 
     * @param string|null $imagePath The image path from database
     * @return string The full URL to the image
     */
    function blog_image_url($imagePath)
    {
        if (empty($imagePath)) {
            return asset('frontend/assets/images/placeholder.jpg');
        }

        // Clean the path
        $imagePath = ltrim($imagePath, '/');
        $filename = basename($imagePath);
        
        // Method 1: Try route (works even if storage link doesn't work on server)
        try {
            if (strpos($imagePath, 'blog/images/') !== false) {
                return route('blog.image', ['filename' => $filename]);
            }
        } catch (\Exception $e) {
            // Continue to next method
        }
        
        // Method 2: Try Storage facade
        try {
            $url = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }
        } catch (\Exception $e) {
            // Continue to next method
        }
        
        // Method 3: Fallback to asset (requires storage link)
        return asset('storage/' . $imagePath);
    }
}

if (!function_exists('course_image_url')) {
    /**
     * Get the URL for a course image
     * Tries multiple methods to ensure the image is accessible
     * 
     * @param string|null $imagePath The image path from database
     * @return string The full URL to the image
     */
    function course_image_url($imagePath)
    {
        if (empty($imagePath)) {
            return asset('frontend/assets/img/default-course.jpg');
        }

        // Clean the path
        $imagePath = ltrim($imagePath, '/');
        $filename = basename($imagePath);
        
        // Method 1: Try route (works even if storage link doesn't work on server)
        try {
            if (strpos($imagePath, 'courses/images/') !== false) {
                return route('course.image', ['filename' => $filename]);
            }
        } catch (\Exception $e) {
            // Continue to next method
        }
        
        // Method 2: Try Storage facade
        try {
            $url = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }
        } catch (\Exception $e) {
            // Continue to next method
        }
        
        // Method 3: Fallback to asset (requires storage link)
        return asset('storage/' . $imagePath);
    }
}

