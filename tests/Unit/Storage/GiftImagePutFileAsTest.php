<?php

namespace Tests\Unit\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Documents the filesystem behavior that caused gift image 403 errors.
 */
class GiftImagePutFileAsTest extends TestCase
{
    public function test_put_file_as_stores_flat_file_in_directory(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('cover.jpg');
        $filename = '1328cc38-7cb2-4f36-8163-fce6434fc704.webp';

        $storedPath = Storage::disk('public')->putFileAs('gifts/images', $file, $filename);

        $this->assertSame('gifts/images/'.$filename, $storedPath);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_put_file_with_file_path_as_directory_creates_nested_folder(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('cover.jpg');
        $wrongDirectory = 'gifts/images/1328cc38-7cb2-4f36-8163-fce6434fc704.webp';

        $storedPath = Storage::disk('public')->putFile($wrongDirectory, $file);

        $this->assertStringStartsWith($wrongDirectory.'/', $storedPath);
        $this->assertNotSame($wrongDirectory, $storedPath);
        Storage::disk('public')->assertExists($storedPath);
        Storage::disk('public')->assertExists($wrongDirectory);
    }
}
