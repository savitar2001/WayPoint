<?php

use Tests\TestCase;
use App\Services\Post\ReviewLikeService;
use App\Models\PostLike;

class ReviewLikeServiceTest extends TestCase {
    private $postLike;
    private $reviewLikeService;

    protected function setUp(): void {
        $this->postLike = $this->createMock(PostLike::class);

        $this->reviewLikeService = new ReviewLikeService($this->postLike);
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
}

?>