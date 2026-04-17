<?php

namespace App\Support;

final class WapiTemplatePayloadBuilder
{
    /**
     * Map ordered lists to Flaxxa body/header keys "1", "2", ...
     *
     * @param  array<int, string>  $headerVariables
     * @param  array<int, string>  $bodyVariables
     * @return array{header?: array<string, string>, body?: array<string, string>}
     */
    public static function flaxxaComponentsFromVariables(array $headerVariables, array $bodyVariables): array
    {
        $out = [];

        if ($headerVariables !== []) {
            $out['header'] = self::indexed($headerVariables);
        }
        if ($bodyVariables !== []) {
            $out['body'] = self::indexed($bodyVariables);
        }

        return $out;
    }

    /**
     * بناء components بنمط Meta Cloud API (المستخدم في sendtemplatemessage).
     *
     * @param  array<int, string>  $headerVariables
     * @param  array<int, string>  $bodyVariables
     * @return array<int, array<string, mixed>>
     */
    public static function cloudApiComponentsFromVariables(array $headerVariables, array $bodyVariables): array
    {
        $components = [];

        if ($headerVariables !== []) {
            $components[] = [
                'type' => 'header',
                'parameters' => array_map(
                    fn ($v) => ['type' => 'text', 'text' => (string) $v],
                    array_values($headerVariables)
                ),
            ];
        }

        if ($bodyVariables !== []) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(
                    fn ($v) => ['type' => 'text', 'text' => (string) $v],
                    array_values($bodyVariables)
                ),
            ];
        }

        return $components;
    }

    /**
     * @param  array<int, string>  $values
     * @return array<string, string>
     */
    public static function indexed(array $values): array
    {
        $map = [];
        $i = 1;
        foreach ($values as $v) {
            $map[(string) $i] = (string) $v;
            $i++;
        }

        return $map;
    }

    /**
     * Replace {{1}}, {{2}} in a preview string (1-based).
     */
    public static function previewBody(string $template, array $orderedValues): string
    {
        $result = $template;
        $i = 1;
        foreach ($orderedValues as $v) {
            $result = str_replace('{{'.$i.'}}', (string) $v, $result);
            $i++;
        }

        return $result;
    }
}
