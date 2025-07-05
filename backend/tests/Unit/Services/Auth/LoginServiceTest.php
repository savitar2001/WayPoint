<?php

use Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Services\Auth\LoginService;
use App\Models\User;
use App\Models\LoginAttempt;

class LoginServiceTest extends TestCase {
    private $loginService;
    private $userMock;
    private $loginAttemptMock;

    protected function setUp(): void {
        parent::setUp();
        $this->userMock = $this->createMock(User::class);
        $this->loginAttemptMock = $this->createMock(LoginAttempt::class);
        $this->loginService = new LoginService($this->userMock, $this->loginAttemptMock);
        Config::set('auth.max_login_attempt', 5);
    }

    public function testIsVerifiedNotVerified() {
        $userObj = new \stdClass();
        $userObj->verified = 0;
        $this->userMock->method('findUserByEmail')->willReturn($userObj);
        $response = $this->loginService->isVerified('test@example.com');
        $this->assertFalse($response['success']);
        $this->assertEquals('用戶尚未經過驗證', $response['error']);
    }

    public function testIsVerifiedAlreadyVerified() {
        $userObj = new \stdClass();
        $userObj->verified = 1;
        $this->userMock->method('findUserByEmail')->willReturn($userObj);
        $response = $this->loginService->isVerified('test@example.com');
        $this->assertTrue($response['success']);
    }

    public function testHasExceedLoginAttemptExceeded() {
        $userObj = new \stdClass();
        $userObj->attempts = 6;
        $this->userMock->method('findUserByEmail')->willReturn($userObj);
        $response = $this->loginService->hasExceedLoginAttempt('test@example.com');
        $this->assertFalse($response['success']);
        $this->assertEquals('嘗試登入次數超過上限，請在一小時後嘗試', $response['error']);
    }

    public function testHasExceedLoginAttemptNotExceeded() {
        $userObj = new \stdClass();
        $userObj->attempts = 3;
        $this->userMock->method('findUserByEmail')->willReturn($userObj);
        $response = $this->loginService->hasExceedLoginAttempt('test@example.com');
        $this->assertTrue($response['success']);
    }

    public function testVerifyPasswordIncorrect() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $userObj = new \stdClass();
        $userObj->password = password_hash('correct_password', PASSWORD_DEFAULT);
        $userObj->id = 1;
        $this->userMock->method('findUserByEmail')->willReturn($userObj);
        $this->loginAttemptMock->expects($this->once())->method('recordFailedAttempt')->with(1, $_SERVER['REMOTE_ADDR']);
        $response = $this->loginService->verifyPassword('test@example.com', 'incorrect_password');
        $this->assertFalse($response['success']);
        $this->assertEquals('密碼錯誤', $response['error']);
    }

    public function testVerifyPasswordCorrect() {
        $userObj = new \stdClass();
        $userObj->password = password_hash('correct_password', PASSWORD_DEFAULT);
        $userObj->id = 1;
        $this->userMock->method('findUserByEmail')->willReturn($userObj);
        $this->loginAttemptMock->expects($this->once())->method('clearAttempt')->with(1);
        Auth::shouldReceive('login')->once();
        $response = $this->loginService->verifyPassword('test@example.com', 'correct_password');
        $this->assertTrue($response['success']);
    }

    public function testGetRemainAttempt() {
        $userObj = ['attempts' => 3];
        $this->userMock->method('findUserByEmail')->willReturn($userObj);
        $response = $this->loginService->getRemainAttempt('test@example.com');
        $this->assertEquals(2, $response);
    }

    public function testGetId() {
        $userObj = new \stdClass();
        $userObj->id = 123;
        $this->userMock->method('findUserByEmail')->willReturn($userObj);
        $response = $this->loginService->getId('test@example.com');
        $this->assertEquals(123, $response);
    }

    public function testGetName() {
        $userObj = new \stdClass();
        $userObj->name = 'leon';
        $this->userMock->method('findUserByEmail')->willReturn($userObj);
        $response = $this->loginService->getName('test@example.com');
        $this->assertEquals('leon', $response);
    }

    public function testStartSessionSuccess() {
        $email = 'test@example.com';
        $userObj = new \stdClass();
        $userObj->id = 123;
        $userObj->name = 'leon';
        $this->userMock->method('findUserByEmail')->willReturn($userObj);
        $this->userMock->method('findUserByEmail')->willReturn($userObj);
        Session::shouldReceive('put')->with('loggedin', true)->once();
        Session::shouldReceive('put')->with('userId', 123)->once();
        Session::shouldReceive('put')->with('userName', 'leon')->once();

        $response = $this->loginService->startSession($email);

        $this->assertTrue($response['success']);
        $this->assertEquals([
            'loggedin' => true,
            'userId' => 123,
            'userName' => 'leon'
        ], $response['data']);
    }

    public function testStartSessionError() {
        $email = 'test@example.com';
        $userObj = new \stdClass();
        $userObj->id = 123;
        $userObj->name = 'leon';
        $this->userMock->method('findUserByEmail')->willReturn($userObj);
        Session::shouldReceive('put')->andThrow(new \Exception('Session 失敗'));

        $response = $this->loginService->startSession($email);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('加入會話資料時發生錯誤: Session 失敗', $response['error']);
    }
}