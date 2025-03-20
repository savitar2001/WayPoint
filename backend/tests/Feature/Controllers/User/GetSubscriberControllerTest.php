<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Mockery;
use App\Services\User\ReviewSubscriberService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetSubscriberControllerTest extends TestCase{
    use RefreshDatabase;

    private $reviewSubscriberService;

    protected function setUp(): void{
        parent::setUp();

        $this->reviewSubscriberService = Mockery::mock(ReviewSubscriberService::class);
        $this->app->instance(ReviewSubscriberService::class, $this->reviewSubscriberService);
    }

    public function testGetSubscriberSuccess(){
        $this->reviewSubscriberService->shouldReceive('getAllUserSubscribers')
            ->once()
            ->with(1)
            ->andReturn([
                'success' => true,
                'data' => [
                    ['id' => 1, 'avatar_url' => 'avatar1.jpg'],
                    ['id' => 2, 'avatar_url' => 'avatar2.jpg'],
                ],
            ]);

        $this->reviewSubscriberService->shouldReceive('generatePresignedUrl')
            ->twice()
            ->andReturn(
                ['success' => true, 'data' => ['url' => 'https://example.com/avatar1.jpg']],
                ['success' => true, 'data' => ['url' => 'https://example.com/avatar2.jpg']]
            );

        // Act: 發送請求
        $response = $this->getJson('/api/getSubscriber?userId=1');

        // Assert: 驗證響應
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         ['id' => 1, 'avatar_url' => 'https://example.com/avatar1.jpg'],
                         ['id' => 2, 'avatar_url' => 'https://example.com/avatar2.jpg'],
                     ],
                 ]);
    }

    public function testGetSubscriberMissingUserId()
    {
        // Act: 發送請求，缺少 userId
        $response = $this->getJson('/api/getSubscriber');

        // Assert: 驗證響應
        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'error' => '參數不足',
                 ]);
    }

    public function testGetSubscriberFailureOnGetAllUserSubscribers()
    {
        // Arrange: 模擬 ReviewSubscriberService 的行為
        $this->reviewSubscriberService->shouldReceive('getAllUserSubscribers')
            ->once()
            ->with(1)
            ->andReturn([
                'success' => false,
                'error' => '查詢追蹤戶失敗',
            ]);

        // Act: 發送請求
        $response = $this->getJson('/api/getSubscriber?userId=1');

        // Assert: 驗證響應
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '查詢追蹤戶失敗',
                 ]);
    }

    public function testGetSubscriberFailureOnGeneratePresignedUrl()
    {
        // Arrange: 模擬 ReviewSubscriberService 的行為
        $this->reviewSubscriberService->shouldReceive('getAllUserSubscribers')
            ->once()
            ->with(1)
            ->andReturn([
                'success' => true,
                'data' => [
                    ['id' => 1, 'avatar_url' => 'avatar1.jpg'],
                ],
            ]);

        $this->reviewSubscriberService->shouldReceive('generatePresignedUrl')
            ->once()
            ->with('avatar1.jpg')
            ->andReturn([
                'success' => false,
                'error' => '生成臨時 URL 失敗',
            ]);

        // Act: 發送請求
        $response = $this->getJson('/api/getSubscriber?userId=1');

        // Assert: 驗證響應
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '生成臨時 URL 失敗',
                 ]);
    }

    protected function tearDown(): void
    {
        Mockery::close(); // 清理 Mockery
        parent::tearDown();
    }
}