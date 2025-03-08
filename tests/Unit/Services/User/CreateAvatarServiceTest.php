<?php

use Tests\TestCase;
use App\Models\User;
use App\Services\User\CreateAvatarService;

class CreateAvatarServiceTest extends TestCase {
    private $userMock;
    private $createAvatarService;

    protected function setUp(): void {
        $this->userMock = $this->createMock(User::class);
        $this->createAvatarService = new CreateAvatarService($this->userMock);
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
}