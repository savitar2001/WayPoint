<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Mockery;
use App\Services\User\RemoveSubscriberService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RemoveSubscriberControllerTest extends TestCase {
    use RefreshDatabase;

    private $removeSubscriberService;

    protected function setUp(): void{
        parent::setUp();

        $this->removeSubscriberService = Mockery::mock(RemoveSubscriberService::class);
        $this->app->instance(RemoveSubscriberService::class, $this->removeSubscriberService);
    }

    public function testRemoveSubscriberSuccess() {
        $this->removeSubscriberService->shouldReceive('removeSubscriberFromDatabase')
            ->once()
            ->with(1, 2)
            ->andReturn(['success' => true]);

        $this->removeSubscriberService->shouldReceive('removeFollowerToDatabase')
            ->once()
            ->with(2, 1)
            ->andReturn(['success' => true]);

        $this->removeSubscriberService->shouldReceive('updateUserSubscriberCount')
            ->once()
            ->with(1)
            ->andReturn(['success' => true]);

        $this->removeSubscriberService->shouldReceive('updateUserFollowerCount')
            ->once()
            ->with(2)
            ->andReturn(['success' => true]);

        $response = $this->deleteJson('/api/removeSubscriber/1/2');

        $response->assertStatus(204);
    }

    public function testRemoveSubscriberFailureOnRemoveSubscriberFromDatabase()
    {
        // Arrange: 模擬 RemoveSubscriberService 的行為
        $this->removeSubscriberService->shouldReceive('removeSubscriberFromDatabase')
            ->once()
            ->with(1, 2)
            ->andReturn(['success' => false, 'error' => '移除訂閱者失敗']);

        // Act: 發送請求
        $response = $this->deleteJson('/api/removeSubscriber/1/2');

        // Assert: 驗證響應
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '移除訂閱者失敗',
                 ]);
    }

    public function testRemoveSubscriberFailureOnRemoveFollowerToDatabase()
    {
        // Arrange: 模擬 RemoveSubscriberService 的行為
        $this->removeSubscriberService->shouldReceive('removeSubscriberFromDatabase')
            ->once()
            ->with(1, 2)
            ->andReturn(['success' => true]);

        $this->removeSubscriberService->shouldReceive('removeFollowerToDatabase')
            ->once()
            ->with(2, 1)
            ->andReturn(['success' => false, 'error' => '移除粉絲失敗']);

        // Act: 發送請求
        $response = $this->deleteJson('/api/removeSubscriber/1/2');

        // Assert: 驗證響應
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '移除粉絲失敗',
                 ]);
    }

    public function testRemoveSubscriberFailureOnUpdateSubscriberCount()
    {
        // Arrange: 模擬 RemoveSubscriberService 的行為
        $this->removeSubscriberService->shouldReceive('removeSubscriberFromDatabase')
            ->once()
            ->with(1, 2)
            ->andReturn(['success' => true]);

        $this->removeSubscriberService->shouldReceive('removeFollowerToDatabase')
            ->once()
            ->with(2, 1)
            ->andReturn(['success' => true]);

        $this->removeSubscriberService->shouldReceive('updateUserSubscriberCount')
            ->once()
            ->with(1)
            ->andReturn(['success' => false, 'error' => '修改用戶追蹤數失敗']);

        // Act: 發送請求
        $response = $this->deleteJson('/api/removeSubscriber/1/2');

        // Assert: 驗證響應
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '修改用戶追蹤數失敗',
                 ]);
    }

    public function testRemoveSubscriberFailureOnUpdateFollowerCount()
    {
        // Arrange: 模擬 RemoveSubscriberService 的行為
        $this->removeSubscriberService->shouldReceive('removeSubscriberFromDatabase')
            ->once()
            ->with(1, 2)
            ->andReturn(['success' => true]);

        $this->removeSubscriberService->shouldReceive('removeFollowerToDatabase')
            ->once()
            ->with(2, 1)
            ->andReturn(['success' => true]);

        $this->removeSubscriberService->shouldReceive('updateUserSubscriberCount')
            ->once()
            ->with(1)
            ->andReturn(['success' => true]);

        $this->removeSubscriberService->shouldReceive('updateUserFollowerCount')
            ->once()
            ->with(2)
            ->andReturn(['success' => false, 'error' => '修改粉絲數失敗']);

        // Act: 發送請求
        $response = $this->deleteJson('/api/removeSubscriber/1/2');

        // Assert: 驗證響應
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '修改粉絲數失敗',
                 ]);
    }

    protected function tearDown(): void
    {
        Mockery::close(); // 清理 Mockery
        parent::tearDown();
    }
}