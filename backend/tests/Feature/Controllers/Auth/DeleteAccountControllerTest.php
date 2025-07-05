<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\LoginAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Auth\DeleteAccountService;
use Mockery;
use Illuminate\Support\Facades\Session;

class DeleteAccountControllerTest extends TestCase {
    use RefreshDatabase;

    public function setUp(): void {
        parent::setUp();
        
        $this->user = Mockery::mock(User::class);
        $this->loginAttempt = Mockery::mock(LoginAttempt::class);
        $this->post = Mockery::mock(Post::class);
        
        $this->deleteAccountService = Mockery::mock(DeleteAccountService::class, [
            $this->user,
            $this->loginAttempt,
            $this->post
        ])->makePartial();

        $this->app->instance(DeleteAccountService::class, $this->deleteAccountService);
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        
    }

    public function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    public function testDeleteAccountSuccess(){
        Session::put('userId', 1);
        $this->deleteAccountService->shouldReceive('clearLoginAttempt')->andReturn(['success' => true]);
        $this->deleteAccountService->shouldReceive('clearPost')->andReturn(['success' => true]);
        $this->deleteAccountService->shouldReceive('clearUserInformation')->andReturn(['success' => true]);
        $this->deleteAccountService->shouldReceive('clearSession')->andReturn([
            'success' => true,
            'error' => '',
            'data' => ['成功刪除帳戶']
        ]);

        $response = $this->delete('/deleteAccount');

        // 斷言回應
        $response->assertStatus(200)
                 ->assertJson([
                    'success' => true,
                    'error' => '',
                    'data' => ['成功刪除帳戶']
                ]);
    }
    public function testDeleteAccountFailsOnLoginAttempt() {
        Session::put('userId', 1);
        $this->deleteAccountService->shouldReceive('clearLoginAttempt')->andReturn(['success' => false, 'error'=>'清除登入請求紀錄失敗']);

      
        $response = $this->delete('/deleteAccount');

        $response->assertStatus(400)
                 ->assertJson(['success' => false, 'error' => '清除登入請求紀錄失敗']);
    }
    public function testDeleteAccountFailsOnClearPost() {
        Session::put('userId', 1);
        $this->deleteAccountService->shouldReceive('clearLoginAttempt')->andReturn(['success' => true]);
        $this->deleteAccountService->shouldReceive('clearPost')->andReturn(['success' => false, 'error'=>'刪除貼文失敗']);

        $response = $this->delete('/deleteAccount');

        $response->assertStatus(500)
                 ->assertJson(['success' => false, 'error' => '刪除貼文失敗']);
    }

    public function testDeleteAccountFailsOnClearUserInformation() {
        Session::put('userId', 1);
        $this->deleteAccountService->shouldReceive('clearLoginAttempt')->andReturn(['success' => true]);
        $this->deleteAccountService->shouldReceive('clearPost')->andReturn(['success' => true]);
        $this->deleteAccountService->shouldReceive('clearUserInformation')->andReturn(['success' => false, 'error'=>'刪除用戶資料失敗']);

        $response = $this->delete('/deleteAccount');

        $response->assertStatus(500)
                 ->assertJson(['success' => false, 'error' => '刪除用戶資料失敗']);
    }

    public function testDeleteAccountFailsOnClearSession() {
        Session::put('userId', 1);
        $this->deleteAccountService->shouldReceive('clearLoginAttempt')->andReturn(['success' => true]);
        $this->deleteAccountService->shouldReceive('clearPost')->andReturn(['success' => true]);
        $this->deleteAccountService->shouldReceive('clearUserInformation')->andReturn(['success' => true]);
        $this->deleteAccountService->shouldReceive('clearSession')->andReturn(['success' => false, 'error' => '清除會話資料時發生錯誤']);
        
        $response = $this->delete('/deleteAccount');
        
        $response->assertStatus(500)
            ->assertJson(['success' => false, 'error' => '清除會話資料時發生錯誤']);
    }
}
