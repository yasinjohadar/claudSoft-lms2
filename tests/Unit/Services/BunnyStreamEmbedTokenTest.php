<?php

use App\Models\Video;
use App\Services\Video\BunnyStreamPlaybackService;
use Illuminate\Support\Facades\Config;

uses(Tests\TestCase::class);

beforeEach(function () {
    Config::set('services.bunny_stream.token_security_key', '4742a81b-bf15-42fe-8b1c-8fcb9024c550');
    Config::set('services.bunny_stream.embed_token_ttl', 7200);
    Config::set('services.bunny_stream.embed_token_enabled', true);
});

test('generateEmbedToken matches Bunny SHA256 formula', function () {
    $service = app(BunnyStreamPlaybackService::class);

    $token = $service->generateEmbedToken(
        '4742a81b-bf15-42fe-8b1c-8fcb9024c550',
        '32d140e2-e4f4-4eec-9d53-20371e9be607',
        1623440202
    );

    expect($token)->toBe(hash(
        'sha256',
        '4742a81b-bf15-42fe-8b1c-8fcb9024c550'.'32d140e2-e4f4-4eec-9d53-20371e9be607'.'1623440202'
    ));
});

test('signEmbedUrl includes token and expires when auth enabled', function () {
    $service = app(BunnyStreamPlaybackService::class);
    $libraryId = '759';
    $videoId = 'eb1c4f77-0cda-46be-b47d-1118ad7c2ffe';

    $url = $service->signEmbedUrl($libraryId, $videoId, 3600);

    expect($url)->toBeString()
        ->and($url)->toStartWith("https://iframe.mediadelivery.net/embed/{$libraryId}/{$videoId}?")
        ->and($url)->toContain('token=')
        ->and($url)->toContain('expires=')
        ->and($url)->toContain('responsive=true')
        ->and($url)->toContain('preload=false');

    parse_str(parse_url($url, PHP_URL_QUERY), $query);

    expect($query)->toHaveKeys(['token', 'expires', 'responsive', 'preload'])
        ->and($query['token'])->toBe($service->generateEmbedToken(
            '4742a81b-bf15-42fe-8b1c-8fcb9024c550',
            $videoId,
            (int) $query['expires']
        ));
});

test('signEmbedUrl returns unsigned url when auth disabled', function () {
    Config::set('services.bunny_stream.embed_token_enabled', false);

    $url = app(BunnyStreamPlaybackService::class)
        ->signEmbedUrl('759', 'eb1c4f77-0cda-46be-b47d-1118ad7c2ffe');

    expect($url)->toBeString()
        ->and($url)->not->toContain('token=')
        ->and($url)->not->toContain('expires=');
});

test('signEmbedUrl returns null when auth enabled without security key', function () {
    Config::set('services.bunny_stream.token_security_key', '');
    Config::set('services.bunny_stream.embed_token_enabled', true);

    $url = app(BunnyStreamPlaybackService::class)
        ->signEmbedUrl('759', 'eb1c4f77-0cda-46be-b47d-1118ad7c2ffe');

    expect($url)->toBeNull();
});

test('video getBunnyIframeSrc signs from stored mediadelivery url', function () {
    $video = new Video([
        'video_type' => 'external',
        'video_url' => 'https://iframe.mediadelivery.net/embed/488464/79b92b75-405c-4ce7-bc99-c1eb3af092c9',
    ]);

    $src = $video->getBunnyIframeSrc();

    expect($src)->toBeString()
        ->and($src)->toContain('token=')
        ->and($src)->toContain('expires=')
        ->and($src)->toContain('/embed/488464/79b92b75-405c-4ce7-bc99-c1eb3af092c9');
});

test('native playback url is disabled when embed token auth is enabled', function () {
    Config::set('services.bunny_stream.cdn_hostname', 'vz-example.b-cdn.net');

    $video = new Video([
        'video_type' => 'external',
        'video_url' => 'https://iframe.mediadelivery.net/embed/488464/79b92b75-405c-4ce7-bc99-c1eb3af092c9',
    ]);

    expect($video->getBunnyNativePlaybackUrl())->toBeNull();
});

test('isEmbedTokenAuthEnabled auto enables when key present and flag unset', function () {
    Config::set('services.bunny_stream.embed_token_enabled', null);
    Config::set('services.bunny_stream.token_security_key', 'some-key');

    expect(app(BunnyStreamPlaybackService::class)->isEmbedTokenAuthEnabled())->toBeTrue();
});

test('isEmbedTokenAuthEnabled stays off when flag false even with key', function () {
    Config::set('services.bunny_stream.embed_token_enabled', false);
    Config::set('services.bunny_stream.token_security_key', 'some-key');

    expect(app(BunnyStreamPlaybackService::class)->isEmbedTokenAuthEnabled())->toBeFalse();
});
