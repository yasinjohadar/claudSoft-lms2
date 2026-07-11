<?php

use App\Models\User;
use App\Rules\UniqueUserFullPhone;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class);

test('unique phone rule skips empty phone', function () {
    $failed = false;

    (new UniqueUserFullPhone)->validate('phone', '', function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
});

test('unique phone rule skips when country code is missing', function () {
    app()->instance('request', Request::create('/', 'POST', [
        'country_code' => '',
        'phone' => '5519665883',
    ]));

    $failed = false;

    (new UniqueUserFullPhone)->validate('phone', '5519665883', function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
});

test('full phone digits taken returns false for empty digits', function () {
    expect(User::fullPhoneDigitsTaken(''))->toBeFalse();
    expect(User::fullPhoneDigitsTaken('   '))->toBeFalse();
});
