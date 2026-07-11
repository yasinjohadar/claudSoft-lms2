<?php

namespace App\Support;

/**
 * Generate and format credential passwords for email/WhatsApp delivery.
 */
final class CredentialPassword
{
    /**
     * Alphabet avoids HTML-sensitive chars and BiDi-confusing punctuation (- _ $)
     * that WhatsApp RTL chats may visually reorder.
     */
    private const ALPHABET = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#*';

    public static function generate(int $length = 16): string
    {
        $password = '';
        $max = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= self::ALPHABET[random_int(0, $max)];
        }

        return $password;
    }

    /**
     * Wrap password in Unicode LTR override so RTL WhatsApp clients show true byte order.
     * Callers that accept pasted passwords must run {@see sanitizeForAuth()} first.
     */
    public static function forWhatsAppDisplay(string $plainPassword): string
    {
        return "\u{202D}".$plainPassword."\u{202C}";
    }

    /**
     * Strip invisible Unicode format/bidi marks that WhatsApp may include when copying.
     */
    public static function sanitizeForAuth(string $password): string
    {
        // Remove Cf (format) characters: LTR/RTL marks, embeddings, overrides, ZWSP, etc.
        $cleaned = preg_replace('/\p{Cf}/u', '', $password) ?? $password;

        return trim($cleaned);
    }
}
