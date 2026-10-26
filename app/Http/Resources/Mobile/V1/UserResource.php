<?php

namespace App\Http\Resources\Mobile\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'has_avatar' => filled($this->photo ?: $this->avatar),
            'profile_completion' => [
                'percentage' => $completion['percentage'],
                'missing_fields' => $completion['missing_fields'],
            ],
            'last_login_at' => $this->last_login_at
                ? \Carbon\Carbon::parse($this->last_login_at)->toIso8601String()
                : null,
        ];
    }

    private function avatarUrl(): string
    {
        return student_profile_photo_url($this->resource, $this->photo ?: $this->avatar);
    }
}
