<?php

namespace App\Http\Resources\Mobile\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $completion = $this->profile_completion_data;

        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'email' => $this->email,
            'student_id' => $this->student_id,
            'country_code' => $this->country_code,
            'phone' => $this->phone,
            'full_phone' => $this->full_phone,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth
                ? \Carbon\Carbon::parse($this->date_of_birth)->format('Y-m-d')
                : null,
            'nationality' => $this->nationality_id ? [
                'id' => (int) $this->nationality_id,
                'name' => $this->whenLoaded('nationality', fn () => $this->nationality?->name),
            ] : null,
            'city' => $this->city,
            'address' => $this->address,
            'avatar_url' => $this->avatarUrl(),
            'has_avatar' => filled($this->photoPath()),
            'profile_completion' => [
                'percentage' => $completion['percentage'],
                'missing_fields' => $completion['missing_fields'],
            ],
            'last_login_at' => $this->last_login_at
                ? \Carbon\Carbon::parse($this->last_login_at)->toIso8601String()
                : null,
        ];
    }

    private ?string $resolvedPhoto = null;

    private bool $photoResolved = false;

    private function avatarUrl(): string
    {
        return student_profile_photo_url($this->resource, $this->photoPath());
    }

    /**
     * الصورة الفعلية للطالب.
     * الحساب قد يحمل صورتين: `avatar` (تُرفع من لوحة الأدمن، users/photos) و `photo` (من ملف الطالب، profile-photos).
     * نختار أول صورة ملفها موجود فعلاً — كي لا يظهر رابط مكسور إذا حُذف ملف إحداهما.
     */
    private function photoPath(): ?string
    {
        if ($this->photoResolved) {
            return $this->resolvedPhoto;
        }
        $this->photoResolved = true;

        $candidates = array_values(array_unique(array_filter([$this->photo, $this->avatar])));
        if ($candidates === []) {
            return $this->resolvedPhoto = null;
        }

        $storage = app(\App\Services\Storage\StorageHelperService::class);
        foreach ($candidates as $path) {
            $relative = preg_replace('#^/?storage/#', '', ltrim((string) $path, '/'));
            try {
                // Local disk first (fast); the failover check may reach cloud storage, so it's cached.
                $exists = filter_var($path, FILTER_VALIDATE_URL)
                    || Storage::disk('public')->exists($relative)
                    || Cache::remember('mobile:photo-exists:'.md5($relative), 600,
                        fn () => $storage->fileExistsWithFailover('public', $relative));
                if ($exists) {
                    return $this->resolvedPhoto = $path;
                }
            } catch (\Throwable $e) {
                // try the next one
            }
        }

        return $this->resolvedPhoto = null;
    }
}
