<?php

use Tests\TestCase;
use App\Services\Auth\PasswordResetService;
use App\Models\User;

class PasswordResetServiceTest extends TestCase{
    private $passwordResetService;
    private $userMock;
  
    protected function setUp(): void {
        parent::setUp();
        $this->userMock = $this->createMock(User::class);
        $this->passwordResetService = new PasswordResetService($this->userMock);
    }

    public function testCheckUserExistWhenUserExists(){
        $email = 'test@example.com';
        $this->userMock->expects($this->once())->method('checkUserExistByEmail')->with($email)->willReturn(true);
        $result = $this->passwordResetService->checkUserExist($email);

        $this->assertTrue($result['success']);
    }

    public function testCheckUserExistWhenUserDoesNotExist(){
        $email = 'test@example.com';
        $this->userMock->expects($this->once())->method('checkUserExistByEmail')->with($email)->willReturn(false);

        $result = $this->passwordResetService->checkUserExist($email);

        $this->assertFalse($result['success']);
        $this->assertEquals('此帳戶尚未建立', $result['error']);
    } 

    public function testValidateWithInvalidPassword(){
        $password = ['weak','WEAK','Weak07@','Weak0721','Weakevn@']; 
        $confirmPassword = ['weak','WEAK','Weak07@','Weak0721','Weakevn@'];

        for ($i = 0; $i < count($password); $i++) {
            $result = $this->passwordResetService->validateUserPasswordReset($password[$i], $confirmPassword[$i]);

            $this->assertFalse($result['success']);
            $this->assertEquals('密碼格式不符合規範', $result['error']);
        }
    }

    public function testValidateNonMatchingPasswords(){
        $password = 'Valid1Password@';
        $confirmPassword = 'DifferentPassword1!';

        $result = $this->passwordResetService->validateUserPasswordReset($password, $confirmPassword);

        $this->assertFalse($result['success']);
        $this->assertEquals('確認密碼與密碼不同', $result['error']);
    }


    public function testValidateWithValidPassword() {
        $password = 'Valid1Password@';
        $confirmPassword = 'Valid1Password@';

        $result = $this->passwordResetService->validateUserPasswordReset($password, $confirmPassword);
        
        $this->assertTrue($result['success']);
    }

    public function testPasswordResetFail() {
        $userId = 1;
        $password = 'Valid1Password@';
        $this->userMock->expects($this->once())->method('changeUserPassword')->willReturn(0);

        $result = $this->passwordResetService->passwordReset($userId, $password);

        $this->assertFalse($result['success']);
        $this->assertEquals('更新密碼失敗', $result['error']);
    }

    public function testPasswordResetSuccess() {
        $userId = 1;
        $password = 'Valid1Password@';
        $this->userMock->expects($this->once())->method('changeUserPassword')->willReturn(1);

        $result = $this->passwordResetService->passwordReset($userId, $password);

        $this->assertTrue($result['success']);
    }
}