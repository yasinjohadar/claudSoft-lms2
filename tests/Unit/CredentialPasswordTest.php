<?php

use App\Support\CredentialPassword;
use Tests\TestCase;

uses(TestCase::class);

test('credential password alphabet avoids bidi confusing punctuation', function () {
    $password = CredentialPassword::generate(32);

    expect($password)
        ->toHaveLength(32)
        ->not->toContain('-')
        ->not->toContain('_')
        ->not->toContain('%')
        ->not->toContain('$')
        ->not->toContain('<')
        ->not->toContain('>');
});

test('whatsapp password display wraps with ltr override markers', function () {
    $plain = 'Ab12Cd34Ef56Gh78';
    $display = CredentialPassword::forWhatsAppDisplay($plain);

    expect($display)->toBe("\u{202D}{$plain}\u{202C}")
        ->and($display)->toContain($plain);
});

test('sanitize for auth strips whatsapp ltr copy artifacts', function () {
    $plain = 'R#CJ6HB3i4x2ZBq';
    $wrapped = CredentialPassword::forWhatsAppDisplay($plain);

    expect($wrapped)->not->toBe($plain)
        ->and(CredentialPassword::sanitizeForAuth($wrapped))->toBe($plain)
        ->and(CredentialPassword::sanitizeForAuth("  {$wrapped}  "))->toBe($plain);
});
