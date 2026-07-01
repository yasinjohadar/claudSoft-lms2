<?php

namespace App\Services\Admin;

use App\Support\WapiPhoneNormalizer;
use Illuminate\Database\Eloquent\Builder;

class AdminUserListQueryService
{
    private const PROFILE_FIELD_COUNT = 9;

    /**
     * @param  Builder<\App\Models\User>  $query
     */
    public function applySearch(Builder $query, ?string $search): void
    {
        $search = trim((string) $search);
        if ($search === '') {
            return;
        }

        $digits = WapiPhoneNormalizer::normalize($search);
        $likeTerm = '%'.$this->escapeLike($search).'%';
        $digitLike = $digits !== '' ? '%'.$this->escapeLike($digits).'%' : null;

        $query->where(function (Builder $q) use ($search, $likeTerm, $digits, $digitLike) {
            $q->where('name', 'like', $likeTerm)
                ->orWhere('name_ar', 'like', $likeTerm)
                ->orWhere('email', 'like', $likeTerm);

            if ($digitLike !== null) {
                $q->orWhere(function (Builder $phoneQ) use ($likeTerm, $digitLike, $digits) {
                    $phoneQ->where('phone', 'like', $likeTerm)
                        ->orWhere('full_phone', 'like', $likeTerm)
                        ->orWhereRaw($this->digitsOnlyColumn('phone').' LIKE ?', [$digitLike])
                        ->orWhereRaw($this->digitsOnlyColumn('full_phone').' LIKE ?', [$digitLike])
                        ->orWhereRaw($this->digitsOnlyColumn('CONCAT(COALESCE(country_code, ""), COALESCE(phone, ""))').' LIKE ?', [$digitLike]);

                    if (strlen($digits) >= 4) {
                        $phoneQ->orWhereRaw($this->digitsOnlyColumn('phone').' LIKE ?', ['%'.substr($digits, -min(9, strlen($digits))).'%'])
                            ->orWhereRaw($this->digitsOnlyColumn('full_phone').' LIKE ?', ['%'.substr($digits, -min(9, strlen($digits))).'%']);
                    }
                });
            } else {
                $q->orWhere('phone', 'like', $likeTerm)
                    ->orWhere('full_phone', 'like', $likeTerm);
            }
        });
    }

    /**
     * @param  Builder<\App\Models\User>  $query
     */
    public function applyProfileCompletionFilter(Builder $query, ?string $filter): void
    {
        $filter = trim((string) $filter);
        if ($filter === '') {
            return;
        }

        $score = $this->profileCompletionScoreExpression();
        $total = self::PROFILE_FIELD_COUNT;

        match ($filter) {
            'complete' => $query->whereRaw("({$score}) = ?", [$total]),
            'incomplete' => $query->whereRaw("({$score}) < ?", [$total]),
            'low' => $query->whereRaw("({$score}) < ?", [(int) ceil($total / 2)]),
            'medium' => $query->whereRaw("({$score}) >= ? AND ({$score}) < ?", [
                (int) ceil($total / 2),
                $total,
            ]),
            default => null,
        };
    }

    private function profileCompletionScoreExpression(): string
    {
        return '(
            (CASE WHEN name_ar IS NOT NULL AND TRIM(name_ar) <> "" THEN 1 ELSE 0 END) +
            (CASE WHEN name IS NOT NULL AND TRIM(name) <> "" THEN 1 ELSE 0 END) +
            (CASE WHEN email IS NOT NULL AND TRIM(email) <> "" THEN 1 ELSE 0 END) +
            (CASE WHEN (
                (country_code IS NOT NULL AND TRIM(country_code) <> "" AND phone IS NOT NULL AND TRIM(phone) <> "")
                OR (full_phone IS NOT NULL AND TRIM(full_phone) <> "")
            ) THEN 1 ELSE 0 END) +
            (CASE WHEN date_of_birth IS NOT NULL THEN 1 ELSE 0 END) +
            (CASE WHEN gender IS NOT NULL AND TRIM(gender) <> "" THEN 1 ELSE 0 END) +
            (CASE WHEN nationality_id IS NOT NULL THEN 1 ELSE 0 END) +
            (CASE WHEN city IS NOT NULL AND TRIM(city) <> "" THEN 1 ELSE 0 END) +
            (CASE WHEN address IS NOT NULL AND TRIM(address) <> "" THEN 1 ELSE 0 END)
        )';
    }

    private function digitsOnlyColumn(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({$column}, ' ', ''), '-', ''), '+', ''), '(', ''), ')', '')";
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }
}
