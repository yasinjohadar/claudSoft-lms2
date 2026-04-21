<?php

namespace App\Services\Flaxxa;

use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;
use App\Services\WhatsApp\BroadcastWhatsAppMessage;

/**
 * جمع متغيرات قوالب Flaxxa بنفس منطق لوحة الإرسال، مع دعم حقول إضافية للأتمتة.
 */
final class FlaxxaTemplateVariableResolver
{
    public function __construct(
        private BroadcastWhatsAppMessage $broadcastWhatsApp
    ) {}

    /**
     * @param  array<int, string>  $headerVars
     * @param  array<int, string>  $bodyVars
     * @param  array<string, string|int|float|null>  $extraPlaceholders
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    public function resolveArrays(
        array $headerVars,
        array $bodyVars,
        User $student,
        ?Course $course,
        ?CourseGroup $group,
        array $extraPlaceholders = []
    ): array {
        $h = array_map(
            fn ($v) => $this->resolveLine((string) $v, $student, $course, $group, $extraPlaceholders),
            $headerVars
        );
        $b = array_map(
            fn ($v) => $this->resolveLine((string) $v, $student, $course, $group, $extraPlaceholders),
            $bodyVars
        );

        return [$h, $b];
    }

    /**
     * @param  array<string, string|int|float|null>  $extraPlaceholders
     */
    public function resolveLine(
        string $line,
        User $student,
        ?Course $course,
        ?CourseGroup $group,
        array $extraPlaceholders = []
    ): string {
        $out = $this->broadcastWhatsApp->replacePlaceholders($line, $student, $course, $group);
        foreach ($extraPlaceholders as $key => $value) {
            $k = (string) $key;
            $rep = (string) ($value ?? '');
            $out = str_replace(
                ['{'.$k.'}', '{{'.$k.'}}'],
                [$rep, $rep],
                $out
            );
        }

        return $out;
    }

    /**
     * @param  array<int, string>  $headerVars
     * @param  array<int, string>  $bodyVars
     * @param  array<string, string|int|float|null>  $placeholders
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    public function resolveArraysWithoutUser(
        array $headerVars,
        array $bodyVars,
        array $placeholders
    ): array {
        $replace = static function (string $s) use ($placeholders): string {
            $out = $s;
            foreach ($placeholders as $key => $value) {
                $k = (string) $key;
                $rep = (string) ($value ?? '');
                $out = str_replace(['{'.$k.'}', '{{'.$k.'}}'], [$rep, $rep], $out);
            }

            return $out;
        };

        return [
            array_map($replace, $headerVars),
            array_map($replace, $bodyVars),
        ];
    }
}
