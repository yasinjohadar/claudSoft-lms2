<?php

namespace App\Http\Controllers\Admin\Concerns;

trait CleansUtf8AiResponse
{
    /**
     * @param  mixed  $data
     * @return mixed
     */
    protected function cleanUtf8Data($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'cleanUtf8Data'], $data);
        }

        if (is_string($data)) {
            if (! mb_check_encoding($data, 'UTF-8')) {
                $data = mb_convert_encoding($data, 'UTF-8', 'auto');
            }
            $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            $data = preg_replace('/^\xEF\xBB\xBF/', '', $data);

            return $data;
        }

        return $data;
    }
}
