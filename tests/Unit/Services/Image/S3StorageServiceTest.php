<?php

use App\Services\Image\S3StorageService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class S3StorageServiceTest extends TestCase
{
    private $s3StorageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->s3StorageService = new S3StorageService();
        Storage::fake('s3');
    }

    public function testValidatesCorrectBase64Image()
    {
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/wcAAgAB/VMHEdIAAAAASUVORK5CYII=
';

        $result = $this->s3StorageService->validateFile($base64Image);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('mimeType', $result['data']);
        $this->assertArrayHasKey('extension', $result['data']);
    }

    public function testFailsValidationForUnsupportedMimeType()
    {
        $base64Image = 'data:image/xyz;base64,' . base64_encode(random_bytes(1000));

        $result = $this->s3StorageService->validateFile($base64Image);

        $this->assertFalse($result['success']);
        $this->assertEquals('不支持的文件類型', $result['error']);
    }

  
    public function testFailsValidationWhenImageIsTooLarge()
    {
        config(['filesystems.max_file_size' => 500]);

        $base64Image = 'data:image/png;base64,' . base64_encode(random_bytes(1000));

        $result = $this->s3StorageService->validateFile($base64Image);

        $this->assertFalse($result['success']);
        $this->assertEquals('文件大小超過限制', $result['error']);
    }

  
    public function testFailsValidationWhenBase64DecodingFails()
    {
        $invalidBase64Image = 'data:image/png;base64,@@@@@';

        $result = $this->s3StorageService->validateFile($invalidBase64Image);

        $this->assertFalse($result['success']);
        $this->assertEquals('不支持的文件類型', $result['error']);
    }


    public function testUploadsAValidImageSuccessfully()
    {
        Storage::fake('s3');
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/wcAAgAB/VMHEdIAAAAASUVORK5CYII=
';
        $folder = 'uploads';

        $result = $this->s3StorageService->uploadBase64Image($base64Image, $folder);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('uploads/', $result['data']['path']);
        Storage::disk('s3')->assertExists($result['data']['path']);
    }

  
    public function testHandlesUploadFailure()
    {
        Storage::shouldReceive('disk->put')->andReturn(false);
        
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/wcAAgAB/VMHEdIAAAAASUVORK5CYII=
        ';
        $folder = 'uploads';

        $result = $this->s3StorageService->uploadBase64Image($base64Image, $folder);

        $this->assertFalse($result['success']);
        $this->assertEquals('圖片上傳失敗', $result['message']);
    }

    
    public function testGeneratesPresignedUrlSuccessfully()
    {
        Storage::fake('s3');
        $folder = 'uploads';
        $filename = 'test.png';

        Storage::disk('s3')->put("{$folder}/{$filename}", 'content');

        $result = $this->s3StorageService->generatePresignedUrl($folder, $filename);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('url', $result['data']);
    }

    
    public function testHandlesPresignedUrlGenerationFailure() {
        Storage::shouldReceive('disk->temporaryUrl')->andThrow(new \Exception('獲取url失敗'));

        $folder = 'uploads';
        $filename = 'test.png';

        $result = $this->s3StorageService->generatePresignedUrl($folder, $filename);

        $this->assertFalse($result['success']);
        $this->assertEquals('獲取url失敗', $result['message']);
    }
}
