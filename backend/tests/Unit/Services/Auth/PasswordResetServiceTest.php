<?php

use Tests\TestCase;
use App\Services\Auth\PasswordResetService;
use App\Models\User;
use App\Services\Auth\SendEmailService;
use App\Services\Auth\VerifyService;


class PasswordResetServiceTest extends TestCase{
    private $passwordResetService;
    private $sendEmailService;
    private $verifyService;
    private $userMock;
  
    protected function setUp(): void {
        parent::setUp();
        $this->userMock = $this->createMock(User::class);
        $this->sendEmailService = $this->createMock(SendEmailService::class);
        $this->verifyService = $this->createMock(VerifyService::class);

        $this->passwordResetService = new PasswordResetService($this->userMock, $this->sendEmailService,  $this->verifyService);
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

    public function testCheckVerificationRequestFail() {
        $email = 'test@example.com';
        $this->sendEmailService->method('checkVerificationRequest')->willReturn(['success'=> false, 'error' => '此帳號已驗證過']);
        $response = $this->passwordResetService->checkVerificationRequest($email);

        $this->assertFalse($response['success']);
        $this->assertEquals('此帳號已驗證過', $response['error']);

    }

    public function testCheckVerificationRequestSuccess() {
        $email = 'test@example.com';
        $this->sendEmailService->method('checkVerificationRequest')->willReturn(['success'=> true, 'error' => '']);
        $response = $this->passwordResetService->checkVerificationRequest($email);

        $this->assertTrue($response['success']);
    }

    public function testInsertSendRecordFail() {
        $email = 'test@example.com';
        $this->sendEmailService->method('insertSendRecord')->willReturn(['success'=> false, 'error' => '紀錄插入失敗']);
        $response = $this->passwordResetService->insertSendRecord($email);

        $this->assertFalse($response['success']);
        $this->assertEquals('紀錄插入失敗', $response['error']);

    }

    public function testInsertSendRecordSuccess() {
        $email = 'test@example.com';
        $this->sendEmailService->method('insertSendRecord')->willReturn(['success'=> true, 'error' => '', 'data' =>[
                'hash' => 'test',
                'requestId' => 1,
                'userName' => 'testUser',
                'userId' => 2]]);
        $response =$this->passwordResetService->insertSendRecord($email);

        $this->assertTrue($response['success']);
        $this->assertEquals(4, count($response['data']));
    }

    public function testSendEmailFail() {
        $email = 'test@example.com';
        $hash = 'test';
        $requestId = 1;
        $toName = 'testUser';
        $userId = 2;
        $this->sendEmailService->method('sendEmail')->willReturn(['success'=> false, 'error' => '寄送驗證信失敗']);
        $response = $this->passwordResetService->sendEmail($email, $hash, $requestId, $toName, $userId);

        $this->assertFalse($response['success']);
        $this->assertEquals('寄送驗證信失敗', $response['error']);

    }

    public function testSendEmailSuccess() {
        $email = 'test@example.com';
        $hash = 'test';
        $requestId = 1;
        $toName = 'testUser';
        $userId = 2;
        $this->sendEmailService->method('sendEmail')->willReturn(['success'=> true, 'error' => '']);
        $response = $this->passwordResetService->sendEmail($email, $hash, $requestId, $toName, $userId);

        $this->assertTrue($response['success']);
    }

    public function testInspectVerificationFail() {
        $requestId = 1;
        $hash = 'test';
        $this->verifyService->method('inspectVerification')->willReturn(['success'=> false, 'error' => '無效哈希碼']);
        $response = $this->passwordResetService->inspectVerification($requestId, $hash);

        $this->assertFalse($response['success']);
        $this->assertEquals('無效哈希碼', $response['error']);
    }

    public function testInspectVerificationSuccess() {
        $requestId = 1;
        $hash = 'test';
        $this->verifyService->method('inspectVerification')->willReturn(['success'=> true, 'error' => '']);
        $response = $this->passwordResetService->inspectVerification($requestId, $hash);

        $this->assertTrue($response['success']);
    }

    public function testClearUserRequestFail() {
        $userId = 1;
        $this->verifyService->method('clearUserRequest')->willReturn(['success'=> false, 'error' => '清除請求紀錄失敗']);
        $response = $this->passwordResetService->clearUserRequest($userId);

        $this->assertFalse($response['success']);
        $this->assertEquals('清除請求紀錄失敗', $response['error']);

    }

    public function testClearUserRequestSuccess() {
        $userId = 1;
        $this->verifyService->method('clearUserRequest')->willReturn(['success'=> true, 'error' => '']);
        $response = $this->passwordResetService->clearUserRequest($userId);

        $this->assertTrue($response['success']);
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