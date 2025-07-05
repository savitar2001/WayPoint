<?php


use Tests\TestCase;
use App\Services\Post\ReviewCommentService;
use App\Services\Image\S3StorageService;
use App\Models\PostComment;

class ReviewCommentServiceTest extends TestCase {
    private $postComment;
    private $s3StorageService;
    private $reviewCommentService;

    protected function setUp(): void {
        $this->postComment = $this->createMock(PostComment::class);
        $this->s3StorageService = $this->createMock(S3StorageService::class);
        $this->reviewCommentService = new ReviewCommentService($this->postComment, $this->s3StorageService);
    }

    public function testFetchPostCommentSuccess() {
        $postId = 1;
        $mockData = [
            ['id' => 1, 'post_id' => $postId, 'user_id' => 2, 'content' => 'Test comment']
        ];
         
        $this->postComment->method('getPostComment')->with($postId)->willReturn($mockData);
        
        $response = $this->reviewCommentService->fetchPostComment($postId);
        
        $this->assertTrue($response['success']);
        $this->assertEquals($mockData, $response['data']);
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
        $this->assertEquals($mockData, $response['data']);
    }

    public function testFetchCommentReplyFailure() {
        $commentId = 10;
        
        $this->postComment->method('getCommentReply')->with($commentId)->willReturn(false);
        
        $response = $this->reviewCommentService->fetchCommentReply($commentId);
        
        $this->assertFalse($response['success']);
        $this->assertEquals('查詢該留言的回覆失敗', $response['error']);
    }

    public function testGeneratePresignedUrlSuccess() {
        $fileName = 'test.jpg';
        $this->s3StorageService->method('generatePresignedUrl')->with($fileName)->willReturn(
            ['success' => true,
             'data' => ['url' => 'https://test-bucket.s3.amazonaws.com/post/test.jpg']]);

        $response = $this->reviewCommentService->generatePresignedUrl($fileName);

        $this->assertTrue($response['success']);
        $this->assertEquals(['url' => 'https://test-bucket.s3.amazonaws.com/post/test.jpg'], $response['data']);
    }

    public function testGeneratePresignedUrlFailure() {
        $fileName = 'test.jpg';
        $this->s3StorageService->method('generatePresignedUrl')->with($fileName)->willReturn(
            [
                'success' => false,
                'message' => '獲取url失敗',
                'data' => []
            ]);

        $response = $this->reviewCommentService->generatePresignedUrl($fileName);

        $this->assertFalse($response['success']);
        $this->assertEquals('獲取url失敗', $response['message']);
    }
}