<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Mockery;
use App\Services\User\ReviewFollowerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetFollowerControllerTest extends TestCase{
    use RefreshDatabase;

    private $reviewFollowerService;

    protected function setUp(): void{
        parent::setUp();

        $this->reviewFollowerService = Mockery::mock(ReviewFollowerService::class);
        $this->app->instance(ReviewFollowerService::class, $this->reviewFollowerService);
    }

    public function testGetFollowerSuccess(){
        $this->reviewFollowerService->shouldReceive('getAllUserFollowers')
            ->once()
            ->with('1')
            ->andReturn([
                'success' => true,
                'data' => [
                    (object)['id' => 1, 'avatar_url' => 'http://example.com/avatar1.jpg'],
                    (object)['id' => 2, 'avatar_url' => 'http://example.com/avatar2.jpg'],
                ],
            ]);

        $this->reviewFollowerService->shouldReceive('generatePresignedUrl')
            ->twice()
            ->andReturn(
                ['success' => true, 'data' => ['url' => 'https://example.com/avatar1.jpg']],
                ['success' => true, 'data' => ['url' => 'https://example.com/avatar2.jpg']]
            );

        $response = $this->getJson('/api/getFollower/' . '1');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         ['id' => 1, 'avatar_url' => 'https://example.com/avatar1.jpg'],
                         ['id' => 2, 'avatar_url' => 'https://example.com/avatar2.jpg'],
                     ],
                 ]);
    }

    public function testGetFollowerMissingUserId()
    {
        // Act: 發送請求，缺少 userId
        $response = $this->getJson('/api/getFollower');

        $response->assertStatus(404);
    }

    public function testGetFollowerFailureOnGetAllUserFollowers(){
        $this->reviewFollowerService->shouldReceive('getAllUserFollowers')
            ->once()
            ->with('1')
            ->andReturn([
                'success' => false,
                'error' => '查詢粉絲失敗',
            ]);

        $response = $this->getJson('/api/getFollower/' . '1');

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '查詢粉絲失敗',
                 ]);
    }

    public function testGetFollowerFailureOnGeneratePresignedUrl(){
        $this->reviewFollowerService->shouldReceive('getAllUserFollowers')
            ->once()
            ->with(1)
            ->andReturn([
                'success' => true,
                'data' => [
                    (object)['id' => 1, 'avatar_url' => 'http://example.com/avatar1.jpg'],
                ],
            ]);

        $this->reviewFollowerService->shouldReceive('generatePresignedUrl')
            ->once()
            ->with('avatar1.jpg')
            ->andReturn([
                'success' => false,
                'error' => '生成臨時 URL 失敗',
            ]);

        $response = $this->getJson('/api/getFollower/' . '1');

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '生成臨時 URL 失敗',
                 ]);
    }

    protected function tearDown(): void
    {
        Mockery::close(); 
        parent::tearDown();
    }
}