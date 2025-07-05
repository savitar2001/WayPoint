<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Post\LikePostService;
use App\Services\Post\UnlikePostService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LikePostControllerTest extends TestCase{
    use RefreshDatabase, WithFaker;

    protected $likePostService;
    protected $unlikePostService;

    protected function setUp(): void {
        parent::setUp();
        $this->likePostService = $this->createMock(LikePostService::class);
        $this->unlikePostService = $this->createMock(UnlikePostService::class);
        $this->app->instance(LikePostService::class, $this->likePostService);
        $this->app->instance(UnlikePostService::class, $this->unlikePostService);
    }

    public function testLikePostSuccess() {
        $this->likePostService->method('ifUserLikedPost')->willReturn(['success' => true]);
        $this->likePostService->method('addPostLike')->willReturn(['success' => true]);

        $response = $this->postJson('/api/likePost', [
            'userId' => 1,
            'postId' => 1
        ]);

        $response->assertStatus(200);
    }

    public function testRemoveLikeSuccess() {
        $this->likePostService->method('ifUserLikedPost')->willReturn(['success' => false, 'error' => '已經對這則貼文表達喜歡']);
        $this->unlikePostService->method('removePostLike')->willReturn(['success' => true]);
        $response = $this->postJson('/api/likePost', [
            'userId' => 1,
            'postId' => 1
        ]);

        $response->assertStatus(200);
    }

    public function testLikePostFailureInAddPostLike() {
        $this->likePostService->method('ifUserLikedPost')->willReturn(['success' => true]);
        $this->likePostService->method('addPostLike')->willReturn(['success' => false, 'error' => '對該貼文按讚失敗']);

        $response = $this->postJson('/api/likePost', [
            'userId' => 1,
            'postId' => 1
        ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false, 'error' => '對該貼文按讚失敗']);
    }

    public function testUnlikePostFailureInRemovePostLike() {
        $this->likePostService->method('ifUserLikedPost')->willReturn(['success' => false]);
        $this->unlikePostService->method('removePostLike')->willReturn(['success' => false, 'error' => '對該貼文取消按讚失敗']);

        $response = $this->postJson('/api/likePost', [
            'userId' => 1,
            'postId' => 1
        ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false, 'error' => '對該貼文取消按讚失敗']);
    }
}