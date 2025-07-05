<?php

use Tests\TestCase;
use App\Services\Post\DeleteCommentService;
use App\Models\Post;
use App\Models\PostComment;

class DeleteCommentServiceTest extends TestCase {
    private $post;
    private $deleteCommentService;
    private $postComment;

    protected function setUp(): void {
        $this->post = $this->createMock(Post::class);
        $this->postComment = $this->createMock(PostComment::class);
        $this->deleteCommentService = new DeleteCommentService($this->post, $this->postComment);
    }
 
    public function testDeleteCommentToPostSuccess() {
        $commentId = 1;
        $userId = 123;

        $this->postComment->method('deleteComment')->with($commentId, $userId)->willReturn(true); 

        $response = $this->deleteCommentService->deleteCommentToPost($userId, $commentId);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testDeleteCommentToPostFail() {
        $commentId = 1;
        $userId = 123;

        $this->postComment->method('deleteComment')->with($commentId, $userId)->willReturn(false); 

        $response = $this->deleteCommentService->deleteCommentToPost($userId, $commentId);

        $this->assertFalse($response['success']);
        $this->assertEquals('刪除貼文留言失敗', $response['error']);
    }

    public function testDeleteReplyToCommentSuccess() {
        $replyId = 2;
        $userId = 123;

        $this->postComment->method('deleteReply')->with($replyId, $userId)->willReturn(true);

        $response = $this->deleteCommentService->deleteReplyToComment($userId, $replyId);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testDeleteReplyToCommentFail() {
        $replyId = 2;
        $userId = 123;

        $this->postComment->method('deleteReply')->with($replyId, $userId)->willReturn(false);

        $response = $this->deleteCommentService->deleteReplyToComment($userId, $replyId);

        $this->assertFalse($response['success']);
        $this->assertEquals('刪除回覆其他用戶留言失敗', $response['error']);
    }

    public function testUpdateCommentreplyCountSuccess() {
        $this->postComment->method('updateReplyCount')->willReturn(true);

        $response = $this->deleteCommentService->updateCommentReplyCount(1,-1);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testUpdateCommentreplyCountFailure() {
        $this->postComment->method('updateReplyCount')->willReturn(false);

        $response = $this->deleteCommentService->updateCommentReplyCount(1, -1);

        $this->assertFalse($response['success']);
        $this->assertEquals('更新留言回覆數失敗', $response['error']);
    }
}