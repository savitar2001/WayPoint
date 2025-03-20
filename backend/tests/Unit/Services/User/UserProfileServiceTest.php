<?php

use Tests\TestCase;
use App\Services\User\UserProfileService;
use App\Services\Image\S3StorageService;
use App\Models\User;
 
class UserProfileServiceTest extends TestCase {
    private $user;
    private $s3StorageService;
    private $userProfileService;

    protected function setUp(): void {
        $this->user = $this->createMock(User::class);
        $this->s3StorageService = $this->createMock(S3StorageService::class);
        $this->userProfileService = new UserProfileService($this->user, $this->s3StorageService);
    }

    public function testGetUserInformationSuccess() {
        $userId = 1;

        $userInformation = [
            'id' => 1,
            'name' => 'John Doe',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'post_amount' => 10,
            'subscriber_count' => 200,
            'follower_count' => 150
        ];

        $this->user->method('userInformation')->with($userId)->willReturn($userInformation);

        $response = $this->userProfileService->getUserInformation($userId);

        $this->assertTrue($response['success']);
        $this->assertEquals('John Doe', $response['data']['name']);
        $this->assertEquals('https://example.com/avatar.jpg', $response['data']['avatarUrl']);
        $this->assertEquals(10, $response['data']['postAmount']);
        $this->assertEquals(200, $response['data']['subscriberCount']);
        $this->assertEquals(150, $response['data']['followerCount']);
    }

    public function testGetUserInformationFailure() {
        $userId = 999;

        $this->user->method('userInformation')->with($userId)->willReturn(false);

        $response = $this->userProfileService->getUserInformation($userId);

        $this->assertFalse($response['success']);
        $this->assertEquals('無法取得使用者資訊', $response['error']);
    }

    public function testGeneratePresignedUrlSuccess() {
        $fileName = 'test.jpg';
        $this->s3StorageService->method('generatePresignedUrl')->with('avatar/',$fileName)->willReturn(
            ['success' => true,
             'data' => ['url' => 'https://test-bucket.s3.amazonaws.com/post/test.jpg']]);

        $response = $this->userProfileService->generatePresignedUrl($fileName);

        $this->assertTrue($response['success']);
        $this->assertEquals(['url' => 'https://test-bucket.s3.amazonaws.com/post/test.jpg'], $response['data']);
    }

    public function testGeneratePresignedUrlFailure() {
        $fileName = 'test.jpg';
        $this->s3StorageService->method('generatePresignedUrl')->with('avatar/',$fileName)->willReturn(
            [
                'success' => false,
                'message' => '獲取url失敗',
                'data' => []
            ]);

        $response = $this->userProfileService->generatePresignedUrl($fileName);

        $this->assertFalse($response['success']);
        $this->assertEquals('獲取url失敗', $response['message']);
    }

    public function testGetUserByNameSuccess() {
        $name = 'John Doe';

        $userByName = [
            'id' => 1,
            'name' => 'John Doe',
            'avatar_url' => 'https://example.com/avatar.jpg'
        ];

        $this->user->method('findUserByName')->with($name)->willReturn($userByName);

        $response = $this->userProfileService->getUserByName($name);

        $this->assertTrue($response['success']);
        $this->assertEquals(1, $response['data']['id']);
        $this->assertEquals('John Doe', $response['data']['name']);
        $this->assertEquals('https://example.com/avatar.jpg', $response['data']['avatarUrl']);
    }

    public function testGetUserByNameFailure() {
        $name = 'Nonexistent User';

        $this->user->method('findUserByName')->with($name)->willReturn(false);

        $response = $this->userProfileService->getUserByName($name);

        $this->assertFalse($response['success']);
        $this->assertEquals('無法取得使用者資訊', $response['error']);
    }
}