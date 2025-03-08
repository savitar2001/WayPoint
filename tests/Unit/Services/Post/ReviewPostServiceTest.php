<?php

use Tests\TestCase;
use App\Services\Post\ReviewPostService;
use App\Models\Post;


class ReviewPostServiceTest extends TestCase {
    private $reviewPostService;
    private $post;

    protected function setUp(): void {
        $this->post = $this->createMock(Post::class);
        
        $this->reviewPostService = new ReviewPostService($this->post);
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
}
