<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Post\CreatePostService;
use App\Services\Broadcast\CreatePostBroadcastService;
use Mockery;

class CreatePostControllerTest extends TestCase {
    use RefreshDatabase;

    protected $createPostService;

    protected function setUp(): void {
        parent::setUp();

        $this->createPostService = Mockery::mock(CreatePostService::class);
        $this->app->instance(CreatePostService::class, $this->createPostService);
        $this->createPostBroadcastService = Mockery::mock(CreatePostBroadcastService::class);
        $this->app->instance(CreatePostBroadcastService::class, $this->createPostBroadcastService);
    }

    public function testCreatePostSuccess() {
        $fakeUserId = 999;
        $this->createPostService->shouldReceive('changePostAmount')
            ->once()
            ->with(1)
            ->andReturn(['success' => true]);

        $this->createPostService->shouldReceive('uploadBase64Image')
            ->once()
            ->with('base64string')
            ->andReturn(['success' => true, 'data' => ['url' => 'http://example.com/image.jpg']]);

        $this->createPostService->shouldReceive('createPostToDatabase')
            ->once()
            ->with(1, 'Sample Post', 'This is a sample post.', ['tag1', 'tag2'], 'http://example.com/image.jpg')
            ->andReturn(['success' => true]);

        $this->createPostService->shouldReceive('notifyFollowersOfNewPost')
            ->once()
            ->with(1)
            ->andReturn(['success' => true]);
        $this->createPostBroadcastService->shouldReceive('dispatchPostPublishedEvent')
        ->once()
        ->with(1);

        $response = $this->postJson('/api/createPost', [
            'userId' => 1,
            'name' => 'Sample Post',
            'content' => 'This is a sample post.',
            'tag' => ['tag1', 'tag2'],
            'base64' => 'base64string'
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testChangePostAmountFails() {
        $this->createPostService->shouldReceive('changePostAmount')
            ->once()
            ->with(1)
            ->andReturn(['success' => false, 'error' => '貼文數更新失敗']);

        $response = $this->postJson('/api/createPost', [
            'userId' => 1,
            'name' => 'Sample Post',
            'content' => 'This is a sample post.',
            'tag' => ['tag1', 'tag2'],
            'base64' => 'base64string'
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'error' => '貼文數更新失敗']);
    }

    public function testUploadBase64ImageFails() {
        $this->createPostService->shouldReceive('changePostAmount')
            ->once()
            ->with(1)
            ->andReturn(['success' => true]);

        $this->createPostService->shouldReceive('uploadBase64Image')
            ->once()
            ->with('base64string')
            ->andReturn(['success' => false, 'error' => '上傳圖片失敗']);

        $response = $this->postJson('/api/createPost', [
            'userId' => 1,
            'name' => 'Sample Post',
            'content' => 'This is a sample post.',
            'tag' => ['tag1', 'tag2'],
            'base64' => 'base64string'
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'error' => '上傳圖片失敗']);
    }

    public function testCreatePostToDatabaseFails() {
        $this->createPostService->shouldReceive('changePostAmount')
            ->once()
            ->with(1)
            ->andReturn(['success' => true]);

        $this->createPostService->shouldReceive('uploadBase64Image')
            ->once()
            ->with('base64string')
            ->andReturn(['success' => true, 'data' => ['url' => 'http://example.com/image.jpg']]);

        $this->createPostService->shouldReceive('createPostToDatabase')
            ->once()
            ->with(1, 'Sample Post', 'This is a sample post.', ['tag1', 'tag2'], 'http://example.com/image.jpg')
            ->andReturn(['success' => false, 'error' => '新增貼文至資料庫失敗']);

        $response = $this->postJson('/api/createPost', [
            'userId' => 1,
            'name' => 'Sample Post',
            'content' => 'This is a sample post.',
            'tag' => ['tag1', 'tag2'],
            'base64' => 'base64string'
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'error' => '新增貼文至資料庫失敗']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}