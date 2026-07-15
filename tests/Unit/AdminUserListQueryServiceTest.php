<?php

use App\Services\Admin\AdminUserListQueryService;

function invokeSearchHelper(AdminUserListQueryService $service, string $method, string $value): bool
{
    $ref = new ReflectionMethod($service, $method);
    $ref->setAccessible(true);

    return (bool) $ref->invoke($service, $value);
}

test('detects email search when at sign is present', function () {
    $service = new AdminUserListQueryService;

    expect(invokeSearchHelper($service, 'isEmailSearch', 'yasinjohadar@gmail.com'))->toBeTrue();
    expect(invokeSearchHelper($service, 'isPhoneSearch', 'yasinjohadar@gmail.com'))->toBeFalse();
});

test('detects phone search for numeric input', function () {
    $service = new AdminUserListQueryService;

    expect(invokeSearchHelper($service, 'isPhoneSearch', '+905519665883'))->toBeTrue();
    expect(invokeSearchHelper($service, 'isEmailSearch', '+905519665883'))->toBeFalse();
});

test('does not treat email with digits as phone search', function () {
    $service = new AdminUserListQueryService;

    expect(invokeSearchHelper($service, 'isEmailSearch', 'user123@gmail.com'))->toBeTrue();
    expect(invokeSearchHelper($service, 'isPhoneSearch', 'user123@gmail.com'))->toBeFalse();
});

test('treats plain text as name search not email or phone', function () {
    $service = new AdminUserListQueryService;

    expect(invokeSearchHelper($service, 'isEmailSearch', 'ياسين جوخدار'))->toBeFalse();
    expect(invokeSearchHelper($service, 'isPhoneSearch', 'ياسين جوخدار'))->toBeFalse();
});

test('detects full and partial student serial searches', function () {
    $service = new AdminUserListQueryService;

    expect(invokeSearchHelper($service, 'isStudentSerialSearch', 'STD-2026-00001'))->toBeTrue()
        ->and(invokeSearchHelper($service, 'isStudentSerialSearch', 'std-2026'))->toBeTrue()
        ->and(invokeSearchHelper($service, 'isStudentSerialSearch', 'STD'))->toBeTrue();
});

test('does not confuse student serial with phone search', function () {
    $service = new AdminUserListQueryService;

    expect(invokeSearchHelper($service, 'isStudentSerialSearch', 'STD-2026-00001'))->toBeTrue()
        ->and(invokeSearchHelper($service, 'isPhoneSearch', 'STD-2026-00001'))->toBeTrue();
});
