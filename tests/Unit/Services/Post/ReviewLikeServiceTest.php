<?php

use Tests\TestCase;
use App\Services\Post\ReviewLikeService;
use App\Services\Image\S3StorageService;
use App\Models\PostLike;

class ReviewLikeServiceTest extends TestCase {
    private $s3StorageService;
    private $postLike;
    private $reviewLikeService;

    protected function setUp(): void {
        $this->postLike = $this->createMock(PostLike::class);
        $this->s3StorageService = $this->createMock(S3StorageService::class);

        $this->reviewLikeService = new ReviewLikeService($this->postLike, $this->s3StorageService);
    }

    public function testGetLikeUserByPostSuccess() {
        $postId = 1;
        $mockResponse = ['user1', 'user2']; 

        $this->postLike->method('getUserLikePost')->with($postId)->willReturn($mockResponse);

        $result = $this->reviewLikeService->getLikeUserByPost($postId);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['error']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals($mockResponse, $result['data'][0]);
    }

    public function testGetLikeUserByPostFailure() {
        $postId = 1;

        $this->postLike->method('getUserLikePost')->with($postId)->willReturn(false);

        $result = $this->reviewLikeService->getLikeUserByPost($postId);

        $this->assertFalse($result['success']);
        $this->assertEquals('查詢按讚貼文用戶失敗', $result['error']);
        $this->assertEmpty($result['data']);
    }

    public function testGeneratePresignedUrlSuccess() {
        $fileName = 'test.jpg';
        $this->s3StorageService->method('generatePresignedUrl')->with('avatar/',$fileName)->willReturn(
            ['success' => true,
             'data' => ['url' => 'https://test-bucket.s3.amazonaws.com/post/test.jpg']]);

        $response = $this->reviewLikeService->generatePresignedUrl($fileName);

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

        $response = $this->reviewLikeService->generatePresignedUrl($fileName);

        $this->assertFalse($response['success']);
        $this->assertEquals('獲取url失敗', $response['message']);
    }
}

?>