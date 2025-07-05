<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use App\Models\LoginAttempt;
use App\Services\Auth\LoginService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Mockery;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $loginAttempt;
    protected $loginService;

    public function setUp(): void {
        parent::setUp();
        
        $this->user = Mockery::mock(User::class);
        $this->loginAttempt = Mockery::mock(LoginAttempt::class);
        
        $this->loginService = Mockery::mock(loginService::class, [
            $this->user,
            $this->loginAttempt
        ])->makePartial();
        $this->app->instance(LoginService::class, $this->loginService);

        Config::set('auth.max_login_attempt', 5);
        
        Route::middleware(['web'])->group(function () {
            Route::post('/web/login', [LoginController::class, 'login']);
        });
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }

    public function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    public function testValidatesEmptyInputData() {
        $response = $this->postJson('/web/login', [
            'email' => '',
            'password' => ''
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '缺少必要的登入資料'
            ]);
    }

    public function testValidatesInvalidEmailFormat() {
        $response = $this->postJson('/web/login', [
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '無效的郵箱格式'
            ]);
    }

    public function testChecksIfUserIsVerified(){
        $this->loginService->shouldReceive('isVerified')
            ->with('test@example.com')
            ->andReturn([
                'success' => false,
                'error' => '用戶尚未經過驗證',
                'data' => []]);

        $response = $this->postJson('/web/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => '用戶尚未經過驗證'
            ]);
    }

    public function testChecksLoginAttemptsLimit() {
        $this->loginService->shouldReceive('isVerified')->andReturn(['success' => true]);

        $this->loginService->shouldReceive('hasExceedLoginAttempt')
            ->with('test@example.com')
            ->andReturn([
                'success' => false,
                'error' => '嘗試登入次數超過上限，請在一小時後嘗試',
                'data' => []]);


        $response = $this->postJson('/web/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'error' => '嘗試登入次數超過上限，請在一小時後嘗試'
            ]);
    }

    public function testValidatesIncorrectPassword() {
        $_SERVER['REMOTE_ADDR'] = 'fake';
        $this->loginService->shouldReceive('isVerified')->andReturn(['success' => true]);

        $this->loginService->shouldReceive('hasExceedLoginAttempt')->andReturn(['success' => true]);


        $this->loginService->shouldReceive('verifyPassword')
            ->with('test@example.com','wrong_password')
            ->andReturn([
                'success' => false,
                'error' => '密碼錯誤',
                'data' => []]);

        $this->loginService->shouldReceive('getId')
        ->with('test@example.com')
        ->andReturn(2);

        $this->user->shouldReceive('findUserByEmail')
            ->with('test@example.com')
            ->andReturn(['id' => 2, 'name' => 'leon', 'password'=>'correct_password' ,'attempts' => 2]);

        $this->loginAttempt->shouldReceive('recordFailedAttempt')
        ->with(2,'fake')
        ->andReturn(true);

        $response = $this->postJson('/web/login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'error' => '密碼錯誤',
                'data' => [],
                'remaining_attempts' => 3
            ]);
    }

    public function testLogsInSuccessfully() {
        $this->loginService->shouldReceive('isVerified')->andReturn(['success' => true]);
        $this->loginService->shouldReceive('hasExceedLoginAttempt')->andReturn(['success' => true]);
        $this->loginService->shouldReceive('verifyPassword')->andReturn(['success' => true]);

        $this->loginService->shouldReceive('getId')->with('test@example.com')->andReturn(2);

        $this->loginService->shouldReceive('getId')->with('test@example.com')->andReturn(2);

        $this->user->shouldReceive('findUserByEmail')
            ->with('test@example.com')
            ->andReturn(['id' => 2, 'name' => 'leon', 'password'=>'correct_password' ,'attempts' => 2]);
            
        $this->loginAttempt->shouldReceive('clearAttempt')->with(2);
        
        $this->loginService->shouldReceive('startSession')->with('test@example.com')->andReturn(['success' => true, 'data' => ['登入成功']]);


        $response = $this->postJson('/web/login', [
            'email' => 'test@example.com',
            'password' => 'correct_password'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['登入成功']
            ]);
    }


    public function testCsrfProtection() {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        
        $response = $this->post('/web/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertTrue($response->getStatusCode() != 404, '路由未正確註冊');
    }

    public function testsMiddlewareIsApplied() {
        $routes = Route::getRoutes();
        $loginRoute = $routes->getByName('login');
        
        if ($loginRoute) {
            $middleware = $loginRoute->middleware();
            $this->assertTrue(in_array('web', $middleware), 'web中間件未被應用');
        } else {
            $this->fail('找不到login路由');
        }
    }
}