<?php

use Tests\TestCase;
use App\Services\Auth\RegisterService;
use App\Services\Auth\SendEmailService;
use App\Services\Auth\VerifyService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterServiceTest extends TestCase{
    private $registerService;
    private $sendEmailService;
    private $verifyService;
    private $userMock;

    protected function setUp(): void{
        $this->userMock = $this->createMock(User::class);
        $this->sendEmailService = $this->createMock(SendEmailService::class);
        $this->verifyService = $this->createMock(VerifyService::class);
        $this->registerService = new RegisterService($this->userMock, $this->sendEmailService,  $this->verifyService);
    }

    public function testCheckUserExistWhenUserExists(){
        $email = 'test@example.com';
        $this->userMock->expects($this->once())->method('checkUserExistByEmail')->with($email)->willReturn(true);
        $result = $this->registerService->checkUserExist($email);

        $this->assertFalse($result['success']);
        $this->assertEquals('此帳號已經存在', $result['error']);
    }

    public function testCheckUserExistWhenUserDoesNotExist(){
        $email = 'test@example.com';
        $this->userMock->expects($this->once())->method('checkUserExistByEmail')->with($email)->willReturn(false);

        $result = $this->registerService->checkUserExist($email);

        $this->assertTrue($result['success']);
        $this->assertEquals('', $result['error']);
    } 

    public function testValidateUserRegistrationWithInvalidhName(){
        $name = [str_repeat('a', 256),'秦始皇','ajfihe32!'];
        $email = 'test@example.com';
        $password = 'Valid1Password@';
        $confirmPassword = 'Valid1Password@';
        foreach($name as $i){
            $result = $this->registerService->validateUserRegistration($i, $email, $password, $confirmPassword);

            $this->assertFalse($result['success']);
            $this->assertEquals('名字格式不符合規範', $result['error']);
        }
    }

    public function testValidateUserRegistrationWithInvalidEmail(){
        $name = 'validName';
        $email = 'invalid-email';
        $password = 'Valid1Password@';
        $confirmPassword = 'Valid1Password@';

        $result = $this->registerService->validateUserRegistration($name, $email, $password, $confirmPassword);

        $this->assertFalse($result['success']);
        $this->assertEquals('email格式不符合規範', $result['error']);
    }

    public function testValidateUserRegistrationWithInvalidPassword(){
        $name = 'validName';
        $email = 'test@example.com';
        $password = ['weak','WEAK','Weak07@','Weak0721','Weakevn@']; 
        $confirmPassword = ['weak','WEAK','Weak07@','Weak0721','Weakevn@'];
        for ($i = 0; $i < count($password); $i++) {
            $result = $this->registerService->validateUserRegistration($name, $email, $password[$i], $confirmPassword[$i]);

            $this->assertFalse($result['success']);
            $this->assertEquals('密碼格式不符合規範', $result['error']);
        }
    }

    public function testValidateUserRegistrationWithNonMatchingPasswords(){
        $name = 'validName';
        $email = 'test@example.com';
        $password = 'Valid1Password@';
        $confirmPassword = 'DifferentPassword1!';

        $result = $this->registerService->validateUserRegistration($name, $email, $password, $confirmPassword);

        $this->assertFalse($result['success']);
        $this->assertEquals('確認密碼與密碼不同', $result['error']);
    }

 
    public function testCreateUserSuccess(){
        $name = 'testUser';
        $email = 'test@example.com';
        $password = 'Valid1Password@';
        
        $this->userMock->expects($this->once())->method('registration')->willReturn(true);
        $response = $this->registerService->createUser($name, $email, $password);

        $this->assertTrue($response['success']);
        $this->assertEquals('帳號註冊完成，請繼續至郵箱進行驗證', $response['data'][0]);
    }

    public function testCreateUserFailure(){
        $name = 'testUser';
        $email = 'test@example.com';
        $password = 'Valid1Password!';
        
        $this->userMock->expects($this->once())->method('registration')->willReturn(false);
        $response = $this->registerService->createUser($name, $email, $password);

        $this->assertFalse($response['success']);
        $this->assertEquals('帳號註冊失敗', $response['error']);
    }

    public function testCheckVerificationRequestFail() {
        $email = 'test@example.com';
        $this->sendEmailService->method('checkVerificationRequest')->willReturn(['success'=> false, 'error' => '此帳號已驗證過']);
        $response = $this->registerService->checkVerificationRequest($email);

        $this->assertFalse($response['success']);
        $this->assertEquals('此帳號已驗證過', $response['error']);

    }

    public function testCheckVerificationRequestSuccess() {
        $email = 'test@example.com';
        $this->sendEmailService->method('checkVerificationRequest')->willReturn(['success'=> true, 'error' => '']);
        $response = $this->registerService->checkVerificationRequest($email);

        $this->assertTrue($response['success']);
    }

    public function testInsertSendRecordFail() {
        $email = 'test@example.com';
        $this->sendEmailService->method('insertSendRecord')->willReturn(['success'=> false, 'error' => '紀錄插入失敗']);
        $response = $this->registerService->insertSendRecord($email);

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
        $response =$this->registerService->insertSendRecord($email);

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
        $response = $this->registerService->sendEmail($email, $hash, $requestId, $toName, $userId);

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
        $response = $this->registerService->sendEmail($email, $hash, $requestId, $toName, $userId);

        $this->assertTrue($response['success']);
    }

    public function testInspectVerificationFail() {
        $requestId = 1;
        $hash = 'test';
        $this->verifyService->method('inspectVerification')->willReturn(['success'=> false, 'error' => '無效哈希碼']);
        $response = $this->registerService->inspectVerification($requestId, $hash);

        $this->assertFalse($response['success']);
        $this->assertEquals('無效哈希碼', $response['error']);
    }

    public function testInspectVerificationSuccess() {
        $requestId = 1;
        $hash = 'test';
        $this->verifyService->method('inspectVerification')->willReturn(['success'=> true, 'error' => '']);
        $response = $this->registerService->inspectVerification($requestId, $hash);

        $this->assertTrue($response['success']);
    }

    public function testClearUserRequestFail() {
        $userId = 1;
        $this->verifyService->method('clearUserRequest')->willReturn(['success'=> false, 'error' => '清除請求紀錄失敗']);
        $response = $this->registerService->clearUserRequest($userId);

        $this->assertFalse($response['success']);
        $this->assertEquals('清除請求紀錄失敗', $response['error']);

    }

    public function testClearUserRequestSuccess() {
        $userId = 1;
        $this->verifyService->method('clearUserRequest')->willReturn(['success'=> true, 'error' => '']);
        $response = $this->registerService->clearUserRequest($userId);

        $this->assertTrue($response['success']);
    }

    public function testUpdateUserStateFailedValidation() {
        $userId = 1;
        
        $this->userMock->expects($this->once())
            ->method('updateValidationState')
            ->with($userId)
            ->willReturn(0);

        $result = $this->registerService->updateUserState($userId);

        $this->assertFalse($result['success']);
        $this->assertEquals('更新驗證狀態失敗', $result['error']);
    }

    public function testUpdateUserStateSuccess() {
        $userId = 1;
        
        $this->userMock->expects($this->once())
            ->method('updateValidationState')
            ->with($userId)
            ->willReturn(true);

        $result = $this->registerService->updateUserState($userId);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['error']);
    }

    private function getObjectProperty($object, $propertyName)
    {
        $reflection = new ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}