<?php
namespace Tests\Feature\Auth;

use Tests\TestCase;
use Mockery;
use App\Services\Auth\LogoutService;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Http\Request;

class LogoutControllerTest extends TestCase{
    public function setUp(): void {
        parent::setUp();
        
        $this->logoutService = Mockery::mock(LogoutService::class, [
        ])->makePartial();
        $this->app->instance(LogoutService::class, $this->logoutService);
    
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testLogoutServiceIsCalled()
    {
        $this->logoutService->shouldReceive('logout')->once()->andReturn([
            'success' => true,
            'error' => '',
            'data' => ['成功登出']
        ]);

        $response = $this->postJson('/logout');

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'error' => '',
            'data' => ['成功登出']
        ]);
    }
}
