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

        if ($this->isStudentSerialSearch($search)) {
            $this->applyStudentSerialSearch($query, $search);

            return;
        }

        if (ctype_digit($search)) {
            $this->applyIdOrPhoneSearch($query, $search);

            return;
        }

        if ($this->isEmailSearch($search)) {
            $this->applyEmailSearch($query, $search);

            return;
        }

        if ($this->isPhoneSearch($search)) {
            $this->applyPhoneSearch($query, $search);

            return;
        }

        $this->applyNameSearch($query, $search);
    }

    /**
     * @param  Builder<\App\Models\User>  $query
     */
    private function applyStudentSerialSearch(Builder $query, string $search): void
    {
        $normalized = mb_strtoupper(trim($search));

        if (preg_match('/^STD(?:-\d{4}-\d{5}|\d+)$/', $normalized) === 1) {
            $query->whereRaw('UPPER(TRIM(student_id)) = ?', [$normalized]);

            return;
        }

        $query->whereRaw('UPPER(student_id) LIKE ?', [
            '%'.$this->escapeLike($normalized).'%',
        ]);
    }

    /**
     * @param  Builder<\App\Models\User>  $query
     */
    private function applyEmailSearch(Builder $query, string $search): void
    {
        $normalized = mb_strtolower($search);

        $query->where(function (Builder $q) use ($normalized, $search) {
            $q->whereRaw('LOWER(TRIM(email)) = ?', [$normalized]);

            if (! str_contains($search, '@')) {
                $like = '%'.$this->escapeLike(mb_strtolower($search)).'%';
                $q->orWhereRaw('LOWER(email) LIKE ?', [$like]);
            }
        });
    }

    /**
     * @param  Builder<\App\Models\User>  $query
     */
    private function applyPhoneSearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $phoneQ) use ($search) {
            $this->appendPhoneConditions($phoneQ, $search);
        });
    }

    private function appendPhoneConditions(Builder $phoneQ, string $search): void
    {
        $digits = WapiPhoneNormalizer::normalize($search);
        if ($digits === '') {
            $phoneQ->whereRaw('0 = 1');

            return;
        }

        $likeTerm = '%'.$this->escapeLike($search).'%';
        $digitLike = '%'.$this->escapeLike($digits).'%';

        $phoneQ->where('phone', 'like', $likeTerm)
            ->orWhere('full_phone', 'like', $likeTerm)
            ->orWhereRaw($this->digitsOnlyColumn('phone').' LIKE ?', [$digitLike])
            ->orWhereRaw($this->digitsOnlyColumn('full_phone').' LIKE ?', [$digitLike])
            ->orWhereRaw($this->digitsOnlyColumn('CONCAT(COALESCE(country_code, ""), COALESCE(phone, ""))').' LIKE ?', [$digitLike]);

        if (strlen($digits) >= 4) {
            $suffix = substr($digits, -min(9, strlen($digits)));
            $phoneQ->orWhereRaw($this->digitsOnlyColumn('phone').' LIKE ?', ['%'.$suffix.'%'])
                ->orWhereRaw($this->digitsOnlyColumn('full_phone').' LIKE ?', ['%'.$suffix.'%']);
        }

        if (strlen($digits) >= 7) {
            $phoneQ->orWhereRaw($this->digitsOnlyColumn('phone').' = ?', [$digits])
                ->orWhereRaw($this->digitsOnlyColumn('full_phone').' = ?', [$digits])
                ->orWhereRaw($this->digitsOnlyColumn('CONCAT(COALESCE(country_code, ""), COALESCE(phone, ""))').' = ?', [$digits]);
        }
    }

    /**
     * @param  Builder<\App\Models\User>  $query
     */
    private function applyIdOrPhoneSearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $q) use ($search) {
            $q->where('id', (int) $search)
                ->orWhere(function (Builder $phoneQ) use ($search) {
                    $this->appendPhoneConditions($phoneQ, $search);
                });
        });
    }

    /**
     * @param  Builder<\App\Models\User>  $query
     */
    private function applyNameSearch(Builder $query, string $search): void
    {
        $likeTerm = '%'.$this->escapeLike($search).'%';

        $query->where(function (Builder $q) use ($likeTerm) {
            $q->where('name', 'like', $likeTerm)
                ->orWhere('name_ar', 'like', $likeTerm)
                ->orWhere('student_id', 'like', $likeTerm);
        });
    }

    private function isStudentSerialSearch(string $search): bool
    {
        return str_starts_with(mb_strtoupper(trim($search)), 'STD');
    }

    private function isEmailSearch(string $search): bool
    {
        if (str_contains($search, '@')) {
            return true;
        }

        return filter_var($search, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function isPhoneSearch(string $search): bool
    {
        $digits = WapiPhoneNormalizer::normalize($search);
        if ($digits === '' || strlen($digits) < 4) {
            return false;
        }

        if (str_contains($search, '@')) {
            return false;
        }

        $withoutSpaces = preg_replace('/\s+/', '', $search) ?? '';
        $digitRatio = strlen($digits) / max(1, strlen($withoutSpaces));

        return $digitRatio >= 0.5;
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
