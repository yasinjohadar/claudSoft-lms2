<?php

namespace Tests\Unit;

use App\Services\GroupRegistrationReceiptService;
use App\Services\Storage\StorageHelperService;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Tests\TestCase;

class GroupRegistrationReceiptServiceTest extends TestCase
{
    public function test_it_stores_receipt_on_payment_receipts_disk(): void
    {
        $file = UploadedFile::fake()->image('receipt.jpg');
        $expectedPath = 'group-registrations/payment-receipts/2026/37/receipt.jpg';

        $this->mock(StorageHelperService::class, function ($mock) use ($expectedPath) {
            $mock->shouldReceive('storeUploadedFileWithFailover')
                ->once()
                ->with(
                    'payment_receipts',
                    'group-registrations/payment-receipts/' . date('Y') . '/37',
                    \Mockery::type(UploadedFile::class),
                    'document'
                )
                ->andReturn($expectedPath);
        });

        $path = app(GroupRegistrationReceiptService::class)->store($file, 37);

        $this->assertSame($expectedPath, $path);
    }

    public function test_it_throws_when_receipt_cannot_be_stored(): void
    {
        $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

        $this->mock(StorageHelperService::class, function ($mock) {
            $mock->shouldReceive('storeUploadedFileWithFailover')
                ->once()
                ->andReturnNull();
        });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('تعذر رفع وصل الانتساب');

        app(GroupRegistrationReceiptService::class)->store($file, 37);
    }

    public function test_it_retrieves_receipt_with_failover(): void
    {
        $path = 'group-registrations/payment-receipts/2026/37/receipt.pdf';
        $expected = [
            'content' => '%PDF-test',
            'mime_type' => 'application/pdf',
        ];

        $this->mock(StorageHelperService::class, function ($mock) use ($path, $expected) {
            $mock->shouldReceive('retrieveFileWithFailover')
                ->once()
                ->with(GroupRegistrationReceiptService::DISK, $path)
                ->andReturn($expected);
        });

        $receipt = app(GroupRegistrationReceiptService::class)->retrieve($path);

        $this->assertSame($expected, $receipt);
    }

    public function test_it_returns_null_when_receipt_path_is_empty(): void
    {
        $this->mock(StorageHelperService::class, function ($mock) {
            $mock->shouldNotReceive('retrieveFileWithFailover');
        });

        $this->assertNull(app(GroupRegistrationReceiptService::class)->retrieve(null));
    }
}
