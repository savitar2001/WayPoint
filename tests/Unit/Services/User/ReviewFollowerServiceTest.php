<?php

use Tests\TestCase;
use App\Services\User\ReviewFollowerService;
use App\Services\Image\S3StorageService;
use App\Models\UserFollower;

class ReviewFollowerServiceTest extends TestCase {

    private $reviewFollowerService;
    private $s3StorageService;
    private $userFollower;

    protected function setUp(): void {
        $this->userFollower = $this->createMock(UserFollower::class);
        $this->s3StorageService = $this->createMock(S3StorageService::class);

        $this->reviewFollowerService = new ReviewFollowerService($this->userFollower,$this->s3StorageService);
    }

    public function testGetAllUserFollowersSuccesgetUserFollowerss() {
        $this->userFollower->method('getUserFollowers')->willReturn([['userId' => 1, 'followerId' => 2]]);

        $response = $this->reviewFollowerService->getAllUserFollowers(1);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
        $this->assertNotEmpty($response['data']);
    }

    public function testGetAllUserFollowersFailure() {
        $this->userFollower->method('getUserFollowers')->willReturn(false);

        $response = $this->reviewFollowerService->getAllUserFollowers(1);

        $this->assertFalse($response['success']);
        $this->assertEquals('查詢粉絲失敗', $response['error']);
        $this->assertEmpty($response['data']);
    }

    public function testGeneratePresignedUrlSuccess() {
        $fileName = 'test.jpg';
        $this->s3StorageService->method('generatePresignedUrl')->with('avatar/',$fileName)->willReturn(
            ['success' => true,
             'data' => ['url' => 'https://test-bucket.s3.amazonaws.com/post/test.jpg']]);

        $response = $this->reviewFollowerService->generatePresignedUrl($fileName);

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

        $response = $this->reviewFollowerService->generatePresignedUrl($fileName);

        $this->assertFalse($response['success']);
        $this->assertEquals('獲取url失敗', $response['message']);
    }
}
