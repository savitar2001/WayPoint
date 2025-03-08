<?php

use Tests\TestCase;
use App\Services\Auth\RegisterService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterServiceTest extends TestCase{
    private $registerService;
    private $userMock;

    protected function setUp(): void{
        $this->userMock = $this->createMock(User::class);
        $this->registerService = new RegisterService($this->userMock);
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
        $this->registerService->createUser($name, $email, $password);
        $response = $this->getObjectProperty($this->registerService, 'response');

        $this->assertTrue($response['success']);
        $this->assertEquals('帳號註冊完成，請繼續至郵箱進行驗證', $response['data'][0]);
    }

    public function testCreateUserFailure(){
        $name = 'testUser';
        $email = 'test@example.com';
        $password = 'Valid1Password!';
        
        $this->userMock->expects($this->once())->method('registration')->willReturn(false);
        $this->registerService->createUser($name, $email, $password);
        $response = $this->getObjectProperty($this->registerService, 'response');

        $this->assertFalse($response['success']);
        $this->assertEquals('帳號註冊失敗', $response['error']);
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