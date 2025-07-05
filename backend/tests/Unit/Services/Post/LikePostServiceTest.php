<?php

use Tests\TestCase;
use App\Services\Post\LikePostService;
use App\Models\Post;
use App\Models\PostLike;

class LikePostServiceTest extends TestCase {
    private $post;
    private $postLike;
    private $likePostService;

    protected function setUp(): void {
        $this->post = $this->createMock(Post::class);
        $this->postLike = $this->createMock(PostLike::class);
        $this->likePostService = new LikePostService($this->post, $this->postLike);
    }

    public function testCheckIfUserLikedPostWhenAlreadyLiked() {
        $userId = 1;
        $postId = 1;

        $this->postLike->method('hasPostLike')->willReturn(1);

        $response = $this->likePostService->ifUserLikedPost($userId, $postId);

        $this->assertFalse($response['success']);
        $this->assertEquals('已經對這則貼文表達喜歡', $response['error']);
    }

    public function testCheckIfUserLikedPostWhenNotLiked() {
        $userId = 1;
        $postId = 1;

        $this->postLike->method('hasPostLike')->willReturn(null);

        $response = $this->likePostService->ifUserLikedPost($userId, $postId);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testAddPostLikeWhenSuccess() {
        $userId = 1;
        $postId = 1;

        $this->postLike->method('likePost')->willReturn(true);

        $response = $this->likePostService->addPostLike($userId, $postId);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testAddPostLikeWhenFail() {
        $userId = 1;
        $postId = 1;

        $this->postLike->method('likePost')->willReturn(false);

        $response = $this->likePostService->addPostLike($userId, $postId);

        $this->assertFalse($response['success']);
        $this->assertEquals('對該貼文按讚失敗', $response['error']);
    }
}