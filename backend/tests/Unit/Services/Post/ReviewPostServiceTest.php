<?php

use Tests\TestCase;
use App\Services\Post\ReviewPostService;
use App\Services\Image\S3StorageService;
use App\Models\Post;


class ReviewPostServiceTest extends TestCase {
    private $reviewPostService;
    private $s3StorageService;
    private $post;

    protected function setUp(): void {
        $this->post = $this->createMock(Post::class);
        $this->s3StorageService = $this->createMock(S3StorageService::class);
    
        $this->reviewPostService = new ReviewPostService($this->post, $this->s3StorageService);
    }

    public function testFetchPostInfoSuccess() {
        $userId = 1;
        $postId = 123;
        $tag = 'test';

        $expectedResult = ['id' => 123, 'user_id' => 1, 'content' => 'Test post content', 'tag' => 'test'];

        $this->post->method('searchPost')->with($userId, $postId, $tag)->willReturn($expectedResult);

        $response = $this->reviewPostService->fetchPostInfo($userId, $postId, $tag);

        $this->assertTrue($response['success']);
        $this->assertEquals($expectedResult, $response['data'][0]);
        $this->assertEmpty($response['error']);
    }

 
    public function testFetchPostInfoFailure() {
        $userId = 1;
        $postId = 123;
        $tag = 'nonexistent_tag';

        $this->post->method('searchPost')->with($userId, $postId, $tag)->willReturn(false);

        $response = $this->reviewPostService->fetchPostInfo($userId, $postId, $tag);

        $this->assertFalse($response['success']);
        $this->assertEquals('查詢貼文失敗', $response['error']);
        $this->assertEmpty($response['data']);
    }


    public function testFetchPostInfoWithNoParams() {
        $this->post->method('searchPost')->with(null, null, null)->willReturn(['id' => 123, 'user_id' => 1, 'content' => 'Test post without filters']);

        $response = $this->reviewPostService->fetchPostInfo();

        $this->assertTrue($response['success']);
        $this->assertEquals(['id' => 123, 'user_id' => 1, 'content' => 'Test post without filters'], $response['data'][0]);
    }

    public function testGeneratePresignedUrlSuccess() {
        $fileName = 'test.jpg';
        $this->s3StorageService->method('generatePresignedUrl')->with('post/',$fileName)->willReturn(
            ['success' => true,
             'data' => ['url' => 'https://test-bucket.s3.amazonaws.com/post/test.jpg']]);

        $response = $this->reviewPostService->generatePresignedUrl($fileName);

        $this->assertTrue($response['success']);
        $this->assertEquals(['url' => 'https://test-bucket.s3.amazonaws.com/post/test.jpg'], $response['data']);
    }

    public function testGeneratePresignedUrlFailure() {
        $fileName = 'test.jpg';
        $this->s3StorageService->method('generatePresignedUrl')->with('post/',$fileName)->willReturn(
            [
                'success' => false,
                'message' => '獲取url失敗',
                'data' => []
            ]);

        $response = $this->reviewPostService->generatePresignedUrl($fileName);

        $this->assertFalse($response['success']);
        $this->assertEquals('獲取url失敗', $response['message']);
    }
}
