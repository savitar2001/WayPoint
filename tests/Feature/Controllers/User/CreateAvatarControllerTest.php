<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Mockery;
use App\Services\User\CreateAvatarService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateAvatarControllerTest extends TestCase{
    use RefreshDatabase;
    private $createAvatarService;

    protected function setUp(): void{
        parent::setUp();

        $this->createAvatarService = Mockery::mock(CreateAvatarService::class);
        $this->app->instance(CreateAvatarService::class, $this->createAvatarService);
    }

    public function testCreateAvatarSuccess() {
        $this->createAvatarService->shouldReceive('uploadBase64Image')
            ->once()
            ->with('base64_image_data')
            ->andReturn([
                'success' => true,
                'data' => ['url' => 'https://example.com/avatar.jpg'],
            ]);

        $this->createAvatarService->shouldReceive('createAvatar')
            ->once()
            ->with(1, 'https://example.com/avatar.jpg')
            ->andReturn(['success' => true]);

        // Act: 發送請求
        $response = $this->postJson('/api/createAvatar', [
            'userId' => 1,
            'base64Image' => 'base64_image_data',
        ]);

        // Assert: 驗證響應
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);
    }

    public function testCreateAvatarUploadImageFailure(){
        $this->createAvatarService->shouldReceive('uploadBase64Image')
            ->once()
            ->with('base64_image_data')
            ->andReturn([
                'success' => false,
                'error' => '圖片上傳失敗',
            ]);

        $response = $this->postJson('/api/createAvatar', [
            'userId' => 1,
            'base64Image' => 'base64_image_data',
        ]);

        $response->assertStatus(500)
                 ->assertJson([
                     'success' => false,
                     'error' => '圖片上傳失敗',
                 ]);
    }

    public function testCreateAvatarUpdateAvatarFailure() {
        $this->createAvatarService->shouldReceive('uploadBase64Image')
            ->once()
            ->with('base64_image_data')
            ->andReturn([
                'success' => true,
                'data' => ['url' => 'https://example.com/avatar.jpg'],
            ]);

        $this->createAvatarService->shouldReceive('createAvatar')
            ->once()
            ->with(1, 'https://example.com/avatar.jpg')
            ->andReturn([
                'success' => false,
                'error' => '頭像更新失敗',
            ]);
        $response = $this->postJson('/api/createAvatar', [
            'userId' => 1,
            'base64Image' => 'base64_image_data',
        ]);

        $response->assertStatus(500)
                 ->assertJson([
                     'success' => false,
                     'error' => '頭像更新失敗',
                 ]);
    }

    public function testCreateAvatarValidationError(){
        // Act: 發送請求，缺少必要參數
        $response = $this->postJson('/api/createAvatar', [
            'userId' => null,
            'base64Image' => null,
        ]);

        // Assert: 驗證響應
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['userId', 'base64Image']);
    }

    protected function tearDown(): void
    {
        Mockery::close(); 
        parent::tearDown();
    }
}