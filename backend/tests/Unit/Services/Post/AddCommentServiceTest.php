<?php

use Tests\TestCase;
use App\Services\Post\AddCommentService;
use App\Models\Post;
use App\Models\PostComment;

class AddCommentServiceTest extends TestCase {
    private $post;
    private $addCommentService;
    private $postComment;

    protected function setUp(): void {
        $this->post = $this->createMock(Post::class);
        $this->postComment = $this->createMock(PostComment::class);

        $this->addCommentService = new AddCommentService($this->post, $this->postComment);
    }

    public function testAddCommentToPostSuccess() {
        $this->postComment->method('addComment')->willReturn(true);

        $response = $this->addCommentService->addCommentToPost(1, 1, '這是一條測試留言');

        $this->assertTrue($response['success']);
        $this->assertEquals('', $response['error']);
    }

    public function testAddCommentToPostFail() {
        $this->postComment->method('addComment')->willReturn(false);

        $response = $this->addCommentService->addCommentToPost(1, 1, '這是一條測試留言');
        $this->assertFalse($response['success']);
        $this->assertEquals('新增貼文留言失敗', $response['error']);

    }

    public function testAddReplyToCommentSuccess() {
        $this->postComment->method('addReplyToComment')->willReturn(true);

        $response = $this->addCommentService->addReplyToComment(1, 1, '這是一條回覆');

        $this->assertTrue($response['success']);
        $this->assertEquals('', $response['error']);
    }

    public function testAddReplyToCommentFail() {
        $this->postComment->method('addReplyToComment')->willReturn(false);

        $response = $this->addCommentService->addReplyToComment(1, 1, '這是一條回覆');

        $this->assertFalse($response['success']);
        $this->assertEquals('新增回覆其他用戶留言失敗', $response['error']);
    }

    public function testUpdateCommentreplyCountSuccess() {
        $this->postComment->method('updateReplyCount')->willReturn(true);

        $response = $this->addCommentService->updateCommentReplyCount(1, 1);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testUpdateCommentreplyCountFailure() {
        $this->postComment->method('updateReplyCount')->willReturn(false);

        $response = $this->addCommentService->updateCommentReplyCount(1, 1);

        $this->assertFalse($response['success']);
        $this->assertEquals('更新留言回覆數失敗', $response['error']);
    }
}