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

if (!function_exists('storage_proxy_image_url')) {
    /**
     * Build an app URL that proxies the file from S3/local storage.
     */
    function storage_proxy_image_url(?string $imagePath, array $disks = []): ?string
    {
        if (empty($imagePath)) {
            return null;
        }

        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $parsedPath = parse_url($imagePath, PHP_URL_PATH);
            $imagePath = is_string($parsedPath) ? ltrim($parsedPath, '/') : '';
            if ($imagePath === '') {
                return null;
            }
        }

        $imagePath = ltrim($imagePath, '/');
        $imagePath = preg_replace('#^storage/#', '', $imagePath);
        $filename = basename($imagePath);

        if (str_starts_with($imagePath, 'blog/images/')) {
            return route('blog.image', ['filename' => $filename]);
        }

        if (str_starts_with($imagePath, 'courses/thumbnails/')) {
            return route('course.thumbnail', ['filename' => $filename]);
        }

        if (str_starts_with($imagePath, 'courses/images/')) {
            return route('course.image', ['filename' => $filename]);
        }

        if (str_starts_with($imagePath, 'profile-photos/')) {
            return route('profile.photo', ['filename' => $filename]);
        }

        if (str_starts_with($imagePath, 'gifts/images/')) {
            return route('gift.image', ['filename' => $filename]);
        }

        if (! str_contains($imagePath, '/')) {
            if (in_array('blog_images', $disks, true)) {
                return route('blog.image', ['filename' => $filename]);
            }

            if (in_array('course_thumbnails', $disks, true)) {
                return route('course.thumbnail', ['filename' => $filename]);
            }

            if (in_array('public', $disks, true)) {
                return route('course.image', ['filename' => $filename]);
            }
        }

        return null;
    }
}

if (!function_exists('serve_storage_image_response')) {
    /**
     * Stream an image from dynamic storage disks, with local fallback.
     *
     * @param  array<int, string>  $diskCandidates
     */
    function serve_storage_image_response(array $diskCandidates, string $filePath, string $localRelativePath)
    {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

        try {
            $storageHelper = app(\App\Services\Storage\StorageHelperService::class);

            foreach ($diskCandidates as $diskName) {
                try {
                    $disk = $storageHelper->getDisk($diskName);
                    $content = $disk->get($filePath);

                    if ($content !== false && $content !== '') {
                        $mimeType = 'image/jpeg';

                        try {
                            $mimeType = $disk->mimeType($filePath) ?: $mimeType;
                        } catch (\Exception $e) {
                            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                            $mimeType = match ($extension) {
                                'png' => 'image/png',
                                'gif' => 'image/gif',
                                'webp' => 'image/webp',
                                'svg' => 'image/svg+xml',
                                default => $mimeType,
                            };
                        }

                        return response($content, 200, [
                            'Content-Type' => $mimeType,
                            'Cache-Control' => 'public, max-age=31536000, immutable',
                        ]);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            // fall through to local file
        }

        $path = storage_path('app/public/' . ltrim($localRelativePath, '/'));

        if (is_dir($path)) {
            $innerFiles = array_values(array_filter(
                scandir($path) ?: [],
                fn (string $entry) => ! in_array($entry, ['.', '..'], true)
                    && is_file($path . DIRECTORY_SEPARATOR . $entry)
            ));

            if (count($innerFiles) === 1) {
                $path = $path . DIRECTORY_SEPARATOR . $innerFiles[0];
            } else {
                abort(404, 'الصورة غير موجودة');
            }
        }

        if (! is_file($path)) {
            abort(404, 'الصورة غير موجودة');
        }

        $mimeType = mime_content_type($path);
        if (! in_array($mimeType, $allowedMimeTypes, true)) {
            abort(403, 'نوع الملف غير مسموح');
        }

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}

if (!function_exists('serve_storage_file_response')) {
    /**
     * Stream a file (image or PDF) from dynamic storage disks, with local fallback.
     *
     * @param  array<int, string>  $diskCandidates
     */
    function serve_storage_file_response(array $diskCandidates, string $filePath, ?string $downloadName = null)
    {
        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
        ];

        try {
            $storageHelper = app(\App\Services\Storage\StorageHelperService::class);
            $seenDisks = [];

            foreach ($diskCandidates as $diskName) {
                if (in_array($diskName, $seenDisks, true)) {
                    continue;
                }
                $seenDisks[] = $diskName;

                $failoverResult = $storageHelper->retrieveFileWithFailover($diskName, $filePath);
                if ($failoverResult) {
                    $mimeType = $failoverResult['mime_type'];

                    if (! in_array($mimeType, $allowedMimeTypes, true)) {
                        abort(403, 'نوع الملف غير مسموح');
                    }

                    $headers = [
                        'Content-Type' => $mimeType,
                        'Cache-Control' => 'private, max-age=3600',
                    ];

                    if ($downloadName) {
                        $headers['Content-Disposition'] = 'inline; filename="' . $downloadName . '"';
                    }

                    return response($failoverResult['content'], 200, $headers);
                }

                try {
                    $disk = $storageHelper->getDisk($diskName);
                    $content = $disk->get($filePath);

                    if ($content !== false && $content !== '') {
                        $mimeType = 'application/octet-stream';

                        try {
                            $mimeType = $disk->mimeType($filePath) ?: $mimeType;
                        } catch (\Exception $e) {
                            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                            $mimeType = match ($extension) {
                                'png' => 'image/png',
                                'gif' => 'image/gif',
                                'webp' => 'image/webp',
                                'jpg', 'jpeg' => 'image/jpeg',
                                'pdf' => 'application/pdf',
                                default => $mimeType,
                            };
                        }

                        if (! in_array($mimeType, $allowedMimeTypes, true)) {
                            abort(403, 'نوع الملف غير مسموح');
                        }

                        $headers = [
                            'Content-Type' => $mimeType,
                            'Cache-Control' => 'private, max-age=3600',
                        ];

                        if ($downloadName) {
                            $headers['Content-Disposition'] = 'inline; filename="' . $downloadName . '"';
                        }

                        return response($content, 200, $headers);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            // fall through to local file
        }

        $path = storage_path('app/public/' . ltrim($filePath, '/'));

        if (! file_exists($path)) {
            abort(404, 'الملف غير موجود');
        }

        $mimeType = mime_content_type($path);
        if (! in_array($mimeType, $allowedMimeTypes, true)) {
            abort(403, 'نوع الملف غير مسموح');
        }

        $headers = ['Content-Type' => $mimeType];

        if ($downloadName) {
            $headers['Content-Disposition'] = 'inline; filename="' . $downloadName . '"';
        }

        return response()->file($path, $headers);
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

        $delivery = config('filesystems.image_delivery', 'proxy');

        $normalizedPath = $imagePath;
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            if ($delivery === 'cdn') {
                return $imagePath;
            }

            $parsedPath = parse_url($imagePath, PHP_URL_PATH);
            $normalizedPath = is_string($parsedPath) ? ltrim($parsedPath, '/') : $imagePath;
        } else {
            $normalizedPath = ltrim($imagePath, '/');
            $normalizedPath = preg_replace('#^storage/#', '', $normalizedPath);
        }

        if ($delivery === 'proxy') {
            $proxyUrl = storage_proxy_image_url($normalizedPath, $disks);
            if ($proxyUrl) {
                return $proxyUrl;
            }
        }

        $pathsToTry = array_values(array_unique(array_filter([
            $normalizedPath,
            ! str_contains($normalizedPath, '/') ? 'blog/images/' . $normalizedPath : null,
            ! str_contains($normalizedPath, '/') ? 'courses/thumbnails/' . $normalizedPath : null,
            ! str_contains($normalizedPath, '/') ? 'courses/images/' . $normalizedPath : null,
        ])));

        if ($delivery !== 'proxy') {
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
                // fall through
            }
        }

        $proxyUrl = storage_proxy_image_url($normalizedPath, $disks);
        if ($proxyUrl) {
            return $proxyUrl;
        }

        $localPath = storage_path('app/public/' . $normalizedPath);
        if (is_file($localPath)) {
            return asset('storage/' . $normalizedPath);
        }

        if (is_dir($localPath)) {
            $innerFiles = array_values(array_filter(
                scandir($localPath) ?: [],
                fn (string $entry) => ! in_array($entry, ['.', '..'], true)
                    && is_file($localPath . DIRECTORY_SEPARATOR . $entry)
            ));

            if (count($innerFiles) === 1) {
                $flatPath = dirname($normalizedPath) . '/' . $innerFiles[0];

                return asset('storage/' . $flatPath);
            }
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

if (!function_exists('student_default_avatar_url')) {
    /**
     * Default avatar when the student has no profile photo.
     */
    function student_default_avatar_url(): string
    {
        return asset('frontend2/assets/images/logo.png');
    }
}

if (!function_exists('student_profile_photo_url')) {
    /**
     * Get the URL for a student profile photo
     * Works with S3 and local storage through the dynamic storage system
     * 
     * @param \App\Models\User|null $student The student user model
     * @param string|null $photoPath The photo path from database
     * @return string The full URL to the photo or the site logo as fallback
     */
    function student_profile_photo_url($student = null, $photoPath = null): string
    {
        if (empty($photoPath)) {
            if ($student) {
                $photoPath = $student->photo ?: $student->avatar;
            }

            if (empty($photoPath)) {
                return student_default_avatar_url();
            }
        }

        return resolve_storage_image_url(
            ['public'],
            $photoPath,
            student_default_avatar_url()
        );
    }
}
