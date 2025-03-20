<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Mockery;
use App\Services\User\AddSubscriberService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddUserSubscriberControllerTest extends TestCase {
    use RefreshDatabase;

    protected $addSubscriberService;

    protected function setUp(): void{
        parent::setUp();
        $this->addSubscriberService =  Mockery::mock(AddSubscriberService::class);
        $this->app->instance(AddSubscriberService::class, $this->addSubscriberService);
    }

    public function testAddUserSubscriberSuccess() {
        $this->addSubscriberService->shouldReceive('addSubscriberToDatabase')
            ->once()
            ->with(1, 2)
            ->andReturn(['success' => true]);

        $this->addSubscriberService->shouldReceive('addFollowerToDatabase')
            ->once()
            ->with(2, 1)
            ->andReturn(['success' => true]);

        $this->addSubscriberService->shouldReceive('updateUserSubscriberCount')
            ->once()
            ->with(1)
            ->andReturn(['success' => true]);

        $this->addSubscriberService->shouldReceive('updateUserFollowerCount')
            ->once()
            ->with(2)
            ->andReturn(['success' => true]);


        $response = $this->postJson('/api/addSubscriber', [
            'userId' => 1,
            'userSubscriberId' => 2,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);
    }

    public function testAddUserSubscriberFailureOnAddSubscriber() {
        $this->addSubscriberService->shouldReceive('addSubscriberToDatabase')
            ->once()
            ->with(1, 2)
            ->andReturn(['success' => false, 'error' => '添加訂閱者失敗']);

        $response = $this->postJson('/api/addSubscriber', [
            'userId' => 1,
            'userSubscriberId' => 2,
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '添加訂閱者失敗',
                 ]);
    }

    public function testAddUserSubscriberFailureOnAddFollower() {
        $this->addSubscriberService->shouldReceive('addSubscriberToDatabase')
            ->once()
            ->with(1, 2)
            ->andReturn(['success' => true]);

        $this->addSubscriberService->shouldReceive('addFollowerToDatabase')
            ->once()
            ->with(2, 1)
            ->andReturn(['success' => false, 'error' => '成為粉絲失敗']);

        $response = $this->postJson('/api/addSubscriber', [
            'userId' => 1,
            'userSubscriberId' => 2,
        ]);
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '成為粉絲失敗',
                 ]);
    }

    public function testAddUserSubscriberFailureOnUpdateSubscriberCount(){
        $this->addSubscriberService->shouldReceive('addSubscriberToDatabase')
            ->once()
            ->with(1, 2)
            ->andReturn(['success' => true]);

        $this->addSubscriberService->shouldReceive('addFollowerToDatabase')
            ->once()
            ->with(2, 1)
            ->andReturn(['success' => true]);

        $this->addSubscriberService->shouldReceive('updateUserSubscriberCount')
            ->once()
            ->with(1)
            ->andReturn(['success' => false, 'error' => '修改用戶追蹤數失敗']);

        $response = $this->postJson('/api/addSubscriber', [
            'userId' => 1,
            'userSubscriberId' => 2,
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '修改用戶追蹤數失敗',
                 ]);
    }

    public function testAddUserSubscriberFailureOnUpdateFollower_count() {
        $this->addSubscriberService->shouldReceive('addSubscriberToDatabase')
            ->once()
            ->with(1, 2)
            ->andReturn(['success' => true]);

        $this->addSubscriberService->shouldReceive('addFollowerToDatabase')
            ->once()
            ->with(2, 1)
            ->andReturn(['success' => true]);

        $this->addSubscriberService->shouldReceive('updateUserSubscriberCount')
            ->once()
            ->with(1)
            ->andReturn(['success' => true]);

        $this->addSubscriberService->shouldReceive('updateUserFollowerCount')
            ->once()
            ->with(2)
            ->andReturn(['success' => false, 'error' => '修改粉絲數失敗']);

        $response = $this->postJson('/api/addSubscriber', [
            'userId' => 1,
            'userSubscriberId' => 2,
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => '修改粉絲數失敗',
                 ]);
    }

    public function testAddUserSubscriberValidationError(){
        $response = $this->postJson('/api/addSubscriber', [
            'userId' => null,
            'userSubscriberId' => null,
        ]);

        // Assert: 驗證響應
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['userId', 'userSubscriberId']);
    }
}