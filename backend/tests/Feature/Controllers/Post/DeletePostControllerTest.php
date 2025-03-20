<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Post\DeletePostService;
use Mockery;

class DeletePostControllerTest extends TestCase {
    use RefreshDatabase;

    protected $deletePostService;

    protected function setUp(): void{
        parent::setUp();

        $this->deletePostService = Mockery::mock(DeletePostService::class);
        $this->app->instance(DeletePostService::class, $this->deletePostService);
    }

    public function testDeletePostSuccess(){
        $userId = 1;
        $postId = 1;
        $this->deletePostService->shouldReceive('changePostAmount')
            ->once()
            ->with($userId)
            ->andReturn(['success' => true]);

        $this->deletePostService->shouldReceive('deleteImage')
            ->once()
            ->with($postId)
            ->andReturn(['success' => true]);

        $this->deletePostService->shouldReceive('deletePostToDatabase')
            ->once()
            ->with($userId, $postId)
            ->andReturn(['success' => true]);

        $response = $this->deleteJson("/api/deletePost/1/1");

        $response->assertStatus(204);
    }

    public function testChangePostAmountFails()
    {
        $this->deletePostService->shouldReceive('changePostAmount')
            ->once()
            ->with(1)
            ->andReturn(['success' => false, 'error' => '貼文數更新失敗']);

        $response = $this->deleteJson('/api/deletePost/1/1');

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'error' => '貼文數更新失敗']);
    }

    public function testDeleteImageFails() {
        $userId = 1;
        $postId = 1;
        $this->deletePostService->shouldReceive('changePostAmount')
            ->once()
            ->with($userId)
            ->andReturn(['success' => true]);

        $this->deletePostService->shouldReceive('deleteImage')
            ->once()
            ->with($postId)
            ->andReturn(['success' => false, 'error' => '圖片刪除失敗']);

        $response = $this->deleteJson('/api/deletePost/1/1');

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'error' => '圖片刪除失敗']);
    }

    public function testDeletePostToDatabaseFails() {
        $userId = 1;
        $postId = 1;
        $this->deletePostService->shouldReceive('changePostAmount')
            ->once()
            ->with($userId)
            ->andReturn(['success' => true]);

        $this->deletePostService->shouldReceive('deleteImage')
            ->once()
            ->with($postId)
            ->andReturn(['success' => true]);

        $this->deletePostService->shouldReceive('deletePostToDatabase')
            ->once()
            ->with($userId, $postId)
            ->andReturn(['success' => false, 'error' => '資料庫貼文刪除失敗']);

        $response = $this->deleteJson('/api/deletePost/1/1');

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'error' => '資料庫貼文刪除失敗']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}