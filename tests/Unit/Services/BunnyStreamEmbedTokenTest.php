<?php

use App\Models\BunnyStreamLibrary;
use App\Models\Video;
use App\Services\Video\BunnyStreamPlaybackService;
use App\Services\Video\BunnyStreamVideoLinker;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

beforeEach(function () {
    Config::set('services.bunny_stream.embed_token_ttl', 7200);
    Config::set('services.bunny_stream.embed_token_enabled', true);
    Config::set('services.bunny_stream.token_security_key', null);

    Schema::dropIfExists('videos');
    Schema::dropIfExists('bunny_stream_libraries');

    Schema::create('bunny_stream_libraries', function (Blueprint $table) {
        $table->id();
        $table->string('library_id')->unique();
        $table->string('library_name');
        $table->text('token_security_key');
        $table->text('api_key')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    Schema::create('videos', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->string('video_type')->default('external');
        $table->string('video_url', 500)->nullable();
        $table->text('embed_code')->nullable();
        $table->foreignId('bunny_stream_library_id')->nullable()->constrained('bunny_stream_libraries')->nullOnDelete();
        $table->string('processing_status')->default('completed');
        $table->boolean('is_published')->default(true);
        $table->boolean('is_visible')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });
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

test('signEmbedUrl uses library token security key from database', function () {
    $library = BunnyStreamLibrary::create([
        'library_id' => '488464',
        'library_name' => 'Test Library',
        'token_security_key' => '4742a81b-bf15-42fe-8b1c-8fcb9024c550',
        'is_active' => true,
    ]);

    $service = app(BunnyStreamPlaybackService::class);
    $videoId = '79b92b75-405c-4ce7-bc99-c1eb3af092c9';
    $url = $service->signEmbedUrl($library, $videoId, 3600);

    expect($url)->toBeString()
        ->and($url)->toContain('/embed/488464/'.$videoId)
        ->and($url)->toContain('token=')
        ->and($url)->toContain('expires=');
});

test('signEmbedUrlForVideo resolves library from video relation', function () {
    $library = BunnyStreamLibrary::create([
        'library_id' => '488464',
        'library_name' => 'Test Library',
        'token_security_key' => '4742a81b-bf15-42fe-8b1c-8fcb9024c550',
        'is_active' => true,
    ]);

    $video = Video::create([
        'title' => 'Test',
        'video_type' => 'external',
        'video_url' => 'https://iframe.mediadelivery.net/embed/488464/79b92b75-405c-4ce7-bc99-c1eb3af092c9',
        'bunny_stream_library_id' => $library->id,
        'processing_status' => 'completed',
        'is_published' => true,
        'is_visible' => true,
    ]);

    $src = app(BunnyStreamPlaybackService::class)->signEmbedUrlForVideo($video);

    expect($src)->toBeString()->and($src)->toContain('token=');
});

test('signEmbedUrl returns null when library is inactive', function () {
    $library = BunnyStreamLibrary::create([
        'library_id' => '999',
        'library_name' => 'Inactive',
        'token_security_key' => '4742a81b-bf15-42fe-8b1c-8fcb9024c550',
        'is_active' => false,
    ]);

    $url = app(BunnyStreamPlaybackService::class)->signEmbedUrl($library, 'video-id', 3600);

    expect($url)->toBeNull();
});

test('video linker assigns library from existing bunny url', function () {
    $library = BunnyStreamLibrary::create([
        'library_id' => '490323',
        'library_name' => 'Front End',
        'token_security_key' => 'test-key-12345678901234567890123456789012',
        'is_active' => true,
    ]);

    $video = Video::create([
        'title' => 'Linked',
        'video_type' => 'external',
        'video_url' => 'https://iframe.mediadelivery.net/embed/490323/95713963-315b-420b-951f-059b330c89fa',
        'processing_status' => 'completed',
        'is_published' => true,
        'is_visible' => true,
    ]);

    $stats = app(BunnyStreamVideoLinker::class)->linkAll();

    $video->refresh();

    expect($stats['linked'])->toBe(1)
        ->and($video->bunny_stream_library_id)->toBe($library->id);
});

test('native playback url stays disabled when embed token auth is enabled', function () {
    BunnyStreamLibrary::create([
        'library_id' => '488464',
        'library_name' => 'Test Library',
        'token_security_key' => '4742a81b-bf15-42fe-8b1c-8fcb9024c550',
        'is_active' => true,
    ]);

    $video = new Video([
        'video_type' => 'external',
        'video_url' => 'https://iframe.mediadelivery.net/embed/488464/79b92b75-405c-4ce7-bc99-c1eb3af092c9',
    ]);

    expect($video->getBunnyNativePlaybackUrl())->toBeNull();
});

test('isEmbedTokenAuthEnabled is true when active libraries exist', function () {
    BunnyStreamLibrary::create([
        'library_id' => '1',
        'library_name' => 'Lib',
        'token_security_key' => '4742a81b-bf15-42fe-8b1c-8fcb9024c550',
        'is_active' => true,
    ]);

    expect(app(BunnyStreamPlaybackService::class)->isEmbedTokenAuthEnabled())->toBeTrue();
});
