<?php


use Tests\TestCase;
use App\Services\Post\ReviewCommentService;
use App\Models\PostComment;

class ReviewCommentServiceTest extends TestCase {
    private $postComment;
    private $reviewCommentService;

    protected function setUp(): void {
        $this->postComment = $this->createMock(PostComment::class);
        $this->reviewCommentService = new ReviewCommentService($this->postComment);
    }

    public function testFetchPostCommentSuccess() {
        $postId = 1;
        $mockData = [
            ['id' => 1, 'post_id' => $postId, 'user_id' => 2, 'content' => 'Test comment']
        ];
        
        $this->postComment->method('getPostComment')->with($postId)->willReturn($mockData);
        
        $response = $this->reviewCommentService->fetchPostComment($postId);
        
        $this->assertTrue($response['success']);
        $this->assertEquals($mockData, $response['data'][0]);
    }

    public function testFetchPostCommentFailure() {
        $postId = 1;
        
        $this->postComment->method('getPostComment')->with($postId)->willReturn(false);
        
        $response = $this->reviewCommentService->fetchPostComment($postId);
        
        $this->assertFalse($response['success']);
        $this->assertEquals('查詢貼文留言失敗', $response['error']);
    }

    public function testFetchCommentReplySuccess() {
        $commentId = 10;
        $mockData = [
            ['id' => 20, 'comment_id' => $commentId, 'user_id' => 3, 'content' => 'Reply comment']
        ];
        
        $this->postComment->method('getCommentReply')->with($commentId)->willReturn($mockData);
        
        $response = $this->reviewCommentService->fetchCommentReply($commentId);
        
        $this->assertTrue($response['success']);
        $this->assertEquals($mockData, $response['data'][0]);
    }

    public function testFetchCommentReplyFailure() {
        $commentId = 10;
        
        $this->postComment->method('getCommentReply')->with($commentId)->willReturn(false);
        
        $response = $this->reviewCommentService->fetchCommentReply($commentId);
        
        $this->assertFalse($response['success']);
        $this->assertEquals('查詢該留言的回覆失敗', $response['error']);
    }
}