<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Post\ReviewLikeService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetPostLikeControllerTest extends TestCase {
    use RefreshDatabase, WithFaker;
    protected $reviewLikeService;

    protected function setUp(): void{
        parent::setUp();
        $this->reviewLikeService = $this->createMock(ReviewLikeService::class);
        $this->app->instance(ReviewLikeService::class, $this->reviewLikeService);
    }

    public function testGetPostLikeWithValidParameters(){
        $this->reviewLikeService->method('getLikeUserByPost')->willReturn([
            'success' => true,
            'data' => [
                [
                    "user_id" => 1,
                    "name" => "User One",
                    'avatar_url' => 'original_image_url'
                ]
            ]
        ]);

        $this->reviewLikeService->method('generatePresignedUrl')->willReturn([
            'success' => true,
            'data' => [
                'url' => 'temporary_image_url'
            ]
        ]);

        $response = $this->getJson('/api/getPostLike?postId=1');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         [   "user_id" => 1,
                             "name" => "User One",
                             'avatar_url' => 'temporary_image_url'
                         ]
                     ]
                 ]);
    }

    public function testGetPostLikeWithMissingParameters() {
        $response = $this->getJson('/api/getPostLike');

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'error' => '參數不足'
                 ]);
    }

    public function testGetPostLikeWithInvalidParameters(){
        $this->reviewLikeService->method('getLikeUserByPost')->willReturn([
            'success' => false,
            'error' => '查詢按讚貼文用戶失敗'
        ]);

        $response = $this->getJson('/api/getPostLike?postId=1');

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '查詢按讚貼文用戶失敗'
                 ]);
    }

    public function testGetPostLikeFailGetImage(){
        $this->reviewLikeService->method('getLikeUserByPost')->willReturn([
            'success' => true,
            'data' => [
                [
                    "user_id" => 1,
                    "name" => "User One",
                    'avatar_url' => 'original_image_url'
                ]
            ]
        ]);

        $this->reviewLikeService->method('generatePresignedUrl')->willReturn([
            'success' => false,
            'error' => '獲取url失敗'
        ]);

        $response = $this->getJson('/api/getPostLike?postId=1');
      
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '獲取url失敗'
                 ]);
    }
}