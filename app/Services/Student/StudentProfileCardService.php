<?php

namespace App\Services\Student;

use App\Models\StudentProfileCard;
use App\Models\User;
use Illuminate\Support\Str;

class StudentProfileCardService
{
    public function __construct(
        private readonly StudentProfileCardQrService $qrService
    ) {}

    public function getOrCreateForUser(User $user): StudentProfileCard
    {
        $existing = $user->profileCard;
        if ($existing) {
            if (! $existing->qr_code_path || str_ends_with((string) $existing->qr_code_path, '.png')) {
                $this->qrService->generate($existing);

                return $existing->fresh();
            }

            return $existing;
        }

        $slug = $this->generateUniqueSlug($user);

        $card = StudentProfileCard::create([
            'user_id' => $user->id,
            'slug' => $slug,
            'theme' => config('profile-card.defaults.theme'),
            'social_links' => [],
            'is_public' => false,
            'admin_enabled' => true,
            'qr_enabled' => true,
        ]);

        $this->qrService->generate($card);

        return $card->fresh();
    }

    public function updateForUser(User $user, array $data): StudentProfileCard
    {
        $card = $this->getOrCreateForUser($user);
        $oldSlug = $card->slug;

        $payload = [
            'job_title' => $data['job_title'] ?? null,
            'bio' => $data['bio'] ?? null,
            'social_links' => $this->normalizeSocialLinks($data['social_links'] ?? []),
            'theme' => $this->normalizeTheme($data['theme'] ?? []),
            'is_public' => (bool) ($data['is_public'] ?? false),
            'qr_enabled' => (bool) ($data['qr_enabled'] ?? true),
        ];

        if (! empty($data['slug'])) {
            $payload['slug'] = $this->generateUniqueSlug($user, $data['slug'], $card->id);
        }

        $card->update($payload);
        $card->refresh();

        if ($card->slug !== $oldSlug) {
            $this->qrService->deleteStoredQrForSlug($oldSlug);
            $this->qrService->generate($card);
        } elseif (! $card->qr_code_path || str_ends_with($card->qr_code_path, '.png')) {
            $this->qrService->generate($card);
        }

        return $card->fresh();
    }

    public function togglePublic(User $user, bool $isPublic): StudentProfileCard
    {
        $card = $this->getOrCreateForUser($user);
        $card->update(['is_public' => $isPublic]);

        return $card->fresh();
    }

    public function generateUniqueSlug(User $user, ?string $preferred = null, ?int $ignoreId = null): string
    {
        $base = Str::slug($preferred ?: ($user->name_ar ?: $user->name ?: 'student'));
        if ($base === '') {
            $base = 'student';
        }

        $slug = $base;
        $counter = 1;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function normalizeSocialLinks(array $links): array
    {
        $platforms = config('profile-card.social_platforms', []);
        $normalized = [];

        foreach (array_values($links) as $index => $link) {
            if (! is_array($link)) {
                continue;
            }

            $url = trim((string) ($link['url'] ?? ''));
            if ($url === '') {
                continue;
            }

            $platform = (string) ($link['platform'] ?? 'custom');
            $preset = $platforms[$platform] ?? $platforms['custom'];

            $normalized[] = [
                'platform' => $platform,
                'url' => $url,
                'icon' => trim((string) ($link['icon'] ?? $preset['default_icon'] ?? 'fas fa-link')),
                'label' => trim((string) ($link['label'] ?? $preset['default_label'] ?? 'رابط')),
                'enabled' => filter_var($link['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'sort_order' => (int) ($link['sort_order'] ?? $index),
            ];
        }

        usort($normalized, fn ($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        return $normalized;
    }

    public function normalizeTheme(array $theme): array
    {
        $defaults = config('profile-card.defaults.theme', []);
        $presets = config('profile-card.themes', []);
        $preset = (string) ($theme['preset'] ?? $defaults['preset'] ?? 'classic');

        if (! isset($presets[$preset])) {
            $preset = 'classic';
        }

        $accent = (string) ($theme['accent_color'] ?? $presets[$preset]['accent_default'] ?? '#3b82f6');

        return [
            'preset' => $preset,
            'accent_color' => $accent,
            'card_style' => (string) ($theme['card_style'] ?? $defaults['card_style'] ?? 'rounded'),
        ];
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = StudentProfileCard::query()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
