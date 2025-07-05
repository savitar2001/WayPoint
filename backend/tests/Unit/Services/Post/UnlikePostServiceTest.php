<?php

use Tests\TestCase;
use App\Models\Post;
use App\Models\PostLike;
use App\Services\Post\UnlikePostService;

class UnlikePostServiceTest extends TestCase {
    private $post;
    private $postLike;
    private $unlikePostService;

    protected function setUp(): void {
        $this->post = $this->createMock(Post::class);
        $this->postLike = $this->createMock(PostLike::class);
        $this->unlikePostService = new UnlikePostService($this->post, $this->postLike);
    }

    public function testRemovePostLikeSuccess() {
        $userId = 1;
        $postId = 100;

        $this->postLike->method('unlikePost')->with($userId, $postId)->willReturn(true);
        
        $result = $this->unlikePostService->removePostLike($userId, $postId);
        
        $this->assertTrue($result['success']);
        $this->assertEmpty($result['error']);
    }

    public function testRemovePostLikeFailure() {
        $userId = 1;
        $postId = 100;

        $this->postLike->method('unlikePost')->with($userId, $postId)->willReturn(false);
        
        $result = $this->unlikePostService->removePostLike($userId, $postId);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('對該貼文取消按讚失敗', $result['error']);
    }

   }
