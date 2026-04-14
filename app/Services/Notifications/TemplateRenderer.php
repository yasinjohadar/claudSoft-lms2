<?php

namespace App\Services\Notifications;

class TemplateRenderer
{
    public function render(string $template, array $data): string
    {
        $replacements = [];

        foreach ($data as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $replacements['{{'.$key.'}}'] = (string) ($value ?? '');
            }
        }

        return strtr($template, $replacements);
    }
}
