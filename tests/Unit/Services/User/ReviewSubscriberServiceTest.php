<?php

use Tests\TestCase;
use App\Services\User\ReviewSubscriberService;
use App\Services\Image\S3StorageService;
use App\Models\UserSubscriber;

class ReviewSubscriberServiceTest extends TestCase {

    private $reviewSubscriberService;
    private $s3StorageService;
    private $userSubscriber;

    protected function setUp(): void {
        $this->userSubscriber = $this->createMock(UserSubscriber::class);
        $this->s3StorageService = $this->createMock(S3StorageService::class);

        $this->reviewSubscriberService = new ReviewSubscriberService($this->userSubscriber,$this->s3StorageService);
    }

    public function testGetAllUserSubscribersSuccess() {
        $this->userSubscriber->method('getUserSubscribers')->willReturn(['userId' => 2, 'subscriberId' => 1]);

        $response = $this->reviewSubscriberService->getAllUserSubscribers(2);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
        $this->assertNotEmpty($response['data']);
    }

    public function testGetAllUserSubscribersFailure() {
        $this->userSubscriber->method('getUserSubscribers')->willReturn(false);

        $response = $this->reviewSubscriberService->getAllUserSubscribers(1);

        $this->assertFalse($response['success']);
        $this->assertEquals('查詢訂閱者失敗', $response['error']);
        $this->assertEmpty($response['data']);
    }

    public function testGeneratePresignedUrlSuccess() {
        $fileName = 'test.jpg';
        $this->s3StorageService->method('generatePresignedUrl')->with('avatar/',$fileName)->willReturn(
            ['success' => true,
             'data' => ['url' => 'https://test-bucket.s3.amazonaws.com/post/test.jpg']]);

        $response = $this->reviewSubscriberService->generatePresignedUrl($fileName);

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

        $response = $this->reviewSubscriberService->generatePresignedUrl($fileName);

        $this->assertFalse($response['success']);
        $this->assertEquals('獲取url失敗', $response['message']);
    }
}