<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Post\ReviewCommentService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetPostCommentControllerTest extends TestCase {
    use RefreshDatabase, WithFaker;
    protected $reviewCommentService;

    protected function setUp(): void{
        parent::setUp();
        $this->reviewCommentService = $this->createMock(ReviewCommentService::class);
        $this->app->instance(ReviewCommentService::class, $this->reviewCommentService);
    }

    public function testGetPostCommentWithValidParameters(){
        $this->reviewCommentService->method('fetchPostComment')->willReturn([
            'success' => true,
            'data' => [
                (object)[
                    "user_id" => 1,
                    "content" => "Test",
                    'avatar_url' => 'http://example.com/avatar1.jpg'
                ]
            ]
        ]);

        $this->reviewCommentService->method('generatePresignedUrl')->willReturn([
            'success' => true,
            'data' => [
                'url' => 'temporary_image_url'
            ]
        ]);

        $response = $this->getJson('/api/getPostComment/1');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         [   "user_id" => 1,
                             "content" => "Test",
                             'avatar_url' => 'temporary_image_url'
                         ]
                     ]
                 ]);
    }

    public function testGetPostCommentWithMissingParameters() {
        $response = $this->getJson('/api/getPostComment');

        $response->assertStatus(404);
    }

    public function testGetPostCommentWithInvalidParameters(){
        $this->reviewCommentService->method('fetchPostComment')->willReturn([
            'success' => false,
            'error' => '查詢貼文留言失敗'
        ]);

        $response = $this->getJson('/api/getPostComment/1');

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '查詢貼文留言失敗'
                 ]);
    }

    public function testGetPostCommentFailGetImage(){
        $this->reviewCommentService->method('fetchPostComment')->willReturn([
            'success' => true,
            'data' => [
                (object)[
                    "user_id" => 1,
                    "content" => "Test",
                    'avatar_url' => 'http://example.com/avatar1.jpg'
                ]
            ]
        ]);

        $this->reviewCommentService->method('generatePresignedUrl')->willReturn([
            'success' => false,
            'error' => '獲取url失敗'
        ]);

        $response = $this->getJson('/api/getPostComment/1');
      
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '獲取url失敗'
                 ]);
    }

    public function testGetCommentRepltWithValidParameters(){
        $this->reviewCommentService->method('fetchCommentReply')->willReturn([
            'success' => true,
            'data' => [
                (object)[
                    "user_id" => 1,
                    "content" => "Test",
                    'avatar_url' => 'http://example.com/avatar1.jpg'
                ]
            ]
        ]);

        $this->reviewCommentService->method('generatePresignedUrl')->willReturn([
            'success' => true,
            'data' => [
                'url' => 'temporary_image_url'
            ]
        ]);

        $response = $this->getJson('/api/getCommentReply/1');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         [   "user_id" => 1,
                             "content" => "Test",
                             'avatar_url' => 'temporary_image_url'
                         ]
                     ]
                 ]);
    }

    public function testGetCommentRepltWithMissingParameters() {
        $response = $this->getJson('/api/getCommentReply');

        $response->assertStatus(404);
    }

    public function testGetCommentReplyWithInvalidParameters(){
        $this->reviewCommentService->method('fetchCommentReply')->willReturn([
            'success' => false,
            'error' => '查詢該留言的回覆失敗'
        ]);

        $response = $this->getJson('/api/getCommentReply/1');

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '查詢該留言的回覆失敗'
                 ]);
    }

    public function testGetCommentReplyFailGetImage(){
        $this->reviewCommentService->method('fetchCommentReply')->willReturn([
            'success' => true,
            'data' => [
                (object)[
                    "user_id" => 1,
                    "content" => "Test",
                    'avatar_url' => 'http://example.com/avatar1.jpg'
                ]
            ]
        ]);

        $this->reviewCommentService->method('generatePresignedUrl')->willReturn([
            'success' => false,
            'error' => '獲取url失敗'
        ]);

        $response = $this->getJson('/api/getCommentReply/1');
      
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '獲取url失敗'
                 ]);
    }
}