<?php

namespace App\Services\Telegram;

use Exception;

class TelegramApiException extends Exception
{
    public static function resolveUserMessage(\Throwable $e): string
    {
        if ($e instanceof self) {
            return $e->getMessage();
        }

        return 'حدث خطأ أثناء الاتصال بـ Telegram: '.$e->getMessage();
    }
}
