<?php

use Tests\TestCase;
use App\Models\User;
use App\Services\Image\S3StorageService;
use App\Services\User\CreateAvatarService;

class CreateAvatarServiceTest extends TestCase {
    private $userMock;
    private $s3StorageService;
    private $createAvatarService;

    protected function setUp(): void {
        $this->userMock = $this->createMock(User::class);
        $this->s3StorageService = $this->createMock(S3StorageService::class);
        $this->createAvatarService = new CreateAvatarService($this->userMock, $this->s3StorageService);
    }

    public function testCreateAvatarSuccess() {
        $userId = 1;
        $avatarUrl = 'https://s3.amazonaws.com/path/to/file.png';

        $this->userMock->method('changeUserAvatar')->willReturn(1);
        $result = $this->createAvatarService->createAvatar($userId, $avatarUrl);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['error']);
    }

    public function testCreateAvatarFailure() {
        $userId = 1;
        $avatarUrl = 'https://s3.amazonaws.com/path/to/file.png';
       
        $this->userMock->method('changeUserAvatar')->willReturn(0);

        $result = $this->createAvatarService->createAvatar($userId, $avatarUrl);

        $this->assertFalse($result['success']);
        $this->assertEquals('頭像更新失敗', $result['error']);
    }

    public function testUploadBase64ImageSuccess() {
        $this->s3StorageService->method('uploadBase64Image')->with('base64Image', 'avatar/')->willReturn(['success' => true, 'data' => ['url' => 'http://example.com/image.jpg']]);

        $response = $this->createAvatarService->uploadBase64Image('base64Image');

        $this->assertTrue($response['success']);
        $this->assertEquals('http://example.com/image.jpg', $response['data']['url']);
    }

    public function testUploadBase64ImageFail() {
        $this->s3StorageService->method('uploadBase64Image')->with('base64Image', 'avatar/')->willReturn(['success' => false, 'error' => '上傳圖片失敗']);

        $response = $this->createAvatarService->uploadBase64Image('base64Image');

        $this->assertFalse($response['success']);
        $this->assertEquals('上傳圖片失敗', $response['error']);
    }
}