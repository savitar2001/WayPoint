<?php

use Tests\TestCase;
use Illuminate\Support\Facades\Config;
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
        $this->loginService = new LoginService( $this->userMock, $this->loginAttemptMock);
    }

    public function testIsVerifiedNotVerified() {
        $this->userMock->method('findUserByEmail')->willReturn(['verified' => 0]);
        $response = $this->loginService->isVerified('test@example.com');
        $this->assertFalse($response['success']);
        $this->assertEquals('用戶尚未經過驗證', $response['error']);
    }

    public function testIsVerifiedAlreadyVerified() {
        $this->userMock->method('findUserByEmail')->willReturn(['verified' => 1]);
        $response = $this->loginService->isVerified('test@example.com');
        $this->assertTrue($response['success']);
    }

    public function testHasExceedLoginAttemptExceeded() {
        $this->userMock->method('findUserByEmail')->willReturn(['attempts' => 6]);
        $response = $this->loginService->hasExceedLoginAttempt('test@example.com');
        $this->assertFalse($response['success']);
        $this->assertEquals('嘗試登入次數超過上限，請在一小時後嘗試', $response['error']);
    }

    public function testHasExceedLoginAttemptNotExceeded() {
        $this->userMock->method('findUserByEmail')->willReturn(['attempts' => 3]);
        $response = $this->loginService->hasExceedLoginAttempt('test@example.com');
        $this->assertTrue($response['success']);
    }

    public function testVerifyPasswordIncorrect() {
        $_SERVER['REMOTE_ADDR'] = 'http:127.000.001';
        $this->userMock->method('findUserByEmail')->willReturn(['password' => password_hash('correct_password', PASSWORD_DEFAULT),'id' => 1]);
        $this->loginAttemptMock->expects($this->once())->method('recordFailedAttempt')->with(1,$_SERVER['REMOTE_ADDR']);
        $response = $this->loginService->verifyPassword('test@example.com', 'incorrect_password');
        $this->assertFalse($response['success']);
        $this->assertEquals('密碼錯誤', $response['error']);
    }

    public function testVerifyPasswordCorrect() {
        $this->userMock->method('findUserByEmail')->willReturn(['password' => password_hash('correct_password', PASSWORD_DEFAULT),'id' => 1]);
        $this->loginAttemptMock->expects($this->once())->method('clearAttempt')->with(1);
        $response = $this->loginService->verifyPassword('test@example.com', 'correct_password');
        $this->assertTrue($response['success']);
    }

    public function testGetRemainAttempt() {
        $this->userMock->method('findUserByEmail')->willReturn(['attempts' => 3]);
        $response= $this->loginService->getRemainAttempt('test@example.com');
        $this->assertEquals(2,$response);
    }

    public function testGetId() {
        $this->userMock->method('findUserByEmail')->willReturn(['id' => 123]);
        $response = $this->loginService->getId('test@example.com');
        $this->assertEquals(123,$response);
    }

    public function testGetName() {
        $this->userMock->method('findUserByEmail')->willReturn(['name' => 'leon']);
        $response = $this->loginService->getName('test@example.com');
        $this->assertEquals('leon',$response);
    }
}