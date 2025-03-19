<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Post\ReviewPostService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetPostControllerTest extends TestCase {
    use RefreshDatabase, WithFaker;
    protected $reviewPostService;

    protected function setUp(): void{
        parent::setUp();
        $this->reviewPostService = $this->createMock(ReviewPostService::class);
        $this->app->instance(ReviewPostService::class, $this->reviewPostService);
    }

    public function testGetPostWithValidParameters(){
        $this->reviewPostService->method('fetchPostInfo')->willReturn([
            'success' => true,
            'data' => [
                [
                    'image_url' => 'original_image_url'
                ]
            ]
        ]);

        $this->reviewPostService->method('generatePresignedUrl')->willReturn([
            'success' => true,
            'data' => [
                'url' => 'temporary_image_url'
            ]
        ]);

        $response = $this->getJson('/api/getPost?userId=1&postId=1&tag=test');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         [
                             'image_url' => 'temporary_image_url'
                         ]
                     ]
                 ]);
    }

    public function testGetPostWithMissingParameters() {
        $response = $this->getJson('/api/getPost');

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'error' => '參數不足'
                 ]);
    }

    public function testGetPostWithInvalidParameters(){
        $this->reviewPostService->method('fetchPostInfo')->willReturn([
            'success' => false,
            'error' => '查詢貼文失敗'
        ]);

        $response = $this->getJson('/api/getPost?userId=1&postId=1&tag=test');

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '查詢貼文失敗'
                 ]);
    }

    public function testGetPostFailGetImage(){
        $this->reviewPostService->method('fetchPostInfo')->willReturn([
            'success' => true,
            'data' => [
                [
                    'image_url' => 'original_image_url'
                ]
            ]
        ]);

        $this->reviewPostService->method('generatePresignedUrl')->willReturn([
            'success' => false,
            'error' => '獲取url失敗'
        ]);

        $response = $this->getJson('/api/getPost?userId=1&postId=1&tag=test');
      
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '獲取url失敗'
                 ]);
    }
}