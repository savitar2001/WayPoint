<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Request as EmailRequest;
use App\Services\Auth\RegisterService;
use App\Services\Auth\SendEmailService;
use App\Services\Auth\VerifyService;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class RegisterControllerTest extends TestCase {
    use RefreshDatabase, WithFaker;

    protected $registerService;
    protected $user;
    protected $emailRequest;
    protected $sendEmailService;
    protected $verifyService;

    public function setUp(): void{
        parent::setUp();
    
        Mail::fake();
        
        $this->user = Mockery::mock(User::class);
        $this->emailRequest = Mockery::mock(EmailRequest::class);
        
        $this->sendEmailService = Mockery::mock(SendEmailService::class, [
            $this->user,
            $this->emailRequest
        ])->makePartial();
        
        $this->verifyService = Mockery::mock(VerifyService::class, [
            $this->user,
            $this->emailRequest
        ])->makePartial();
        
        $this->registerService = Mockery::mock(RegisterService::class, [
            $this->user,
            $this->sendEmailService,
            $this->verifyService
        ])->makePartial();
        
        $this->app->instance(RegisterService::class, $this->registerService);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testUserRegisterSuccessfully()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];

        $this->mockCheckUserExist($userData['email'], true);
        $this->mockValidateUserRegistration($userData['name'], $userData['email'], $userData['password'], $userData['confirm_password'], true);
        $this->mockCreateUser($userData['name'], $userData['email'], $userData['password'], true);
        
        $this->registerService->shouldReceive('checkVerificationRequest')
            ->once()
            ->with($userData['email'])
            ->andReturn([
                'success' => true,
                'error' => '',
                'data' => []
            ]);
        
        $insertSendRecordResponse = [
            'success' => true,
            'error' => '',
            'data' => [
                'hash' => 'test-hash',
                'requestId' => 123,
                'userName' => $userData['name'],
                'userId' => 1
            ]
        ];
        $this->registerService->shouldReceive('insertSendRecord')
            ->once()
            ->with($userData['email'])
            ->andReturn($insertSendRecordResponse);

        $this->registerService->shouldReceive('sendEmail')
            ->once()
            ->with(
                $userData['email'], 
                $insertSendRecordResponse['data']['hash'], 
                $insertSendRecordResponse['data']['requestId'], 
                $insertSendRecordResponse['data']['userName'], 
                $insertSendRecordResponse['data']['userId']
            )
            ->andReturn([
                'success' => true,
                'error' => '',
                'data' => []
            ]);

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => ['帳號註冊完成，請繼續至郵箱進行驗證']
            ]);
    }

    public function testRegisterFailsWhenUserAlreadyExists()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];

        $this->mockCheckUserExist($userData['email'], false, '此帳號已經存在');

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '此帳號已經存在'
            ]);
    }

    public function testRegisterFailsWithInvalidName()
    {
        $userData = [
            'name' => 'Test%%%User@#',
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];

        $this->mockCheckUserExist($userData['email'], true);
        $this->mockValidateUserRegistration(
            $userData['name'], 
            $userData['email'], 
            $userData['password'], 
            $userData['confirm_password'], 
            false, 
            '名字格式不符合規範'
        );

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '名字格式不符合規範'
            ]);
    }

    public function testRegisterFailsWithInvalidEmail()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];

        $this->mockCheckUserExist($userData['email'], true);
        $this->mockValidateUserRegistration(
            $userData['name'], 
            $userData['email'], 
            $userData['password'], 
            $userData['confirm_password'], 
            false, 
            'email格式不符合規範'
        );

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'email格式不符合規範'
            ]);
    }

    public function testRegisterFailsWithInvalidPassword()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'weakpass',
            'confirm_password' => 'weakpass'
        ];

        $this->mockCheckUserExist($userData['email'], true);
        $this->mockValidateUserRegistration(
            $userData['name'], 
            $userData['email'], 
            $userData['password'], 
            $userData['confirm_password'], 
            false, 
            '密碼格式不符合規範'
        );

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '密碼格式不符合規範'
            ]);
    }

    public function testRegisterFailsWithMismatchedPasswords()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'confirm_password' => 'Password2!'
        ];

        $this->mockCheckUserExist($userData['email'], true);
        $this->mockValidateUserRegistration(
            $userData['name'], 
            $userData['email'], 
            $userData['password'], 
            $userData['confirm_password'], 
            false, 
            '確認密碼與密碼不同'
        );

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '確認密碼與密碼不同'
            ]);
    }

    public function testRegisterFailsWhenUserCreationFails()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];

        $this->mockCheckUserExist($userData['email'], true);
        $this->mockValidateUserRegistration(
            $userData['name'], 
            $userData['email'], 
            $userData['password'], 
            $userData['confirm_password'], 
            true
        );
        $this->mockCreateUser(
            $userData['name'], 
            $userData['email'], 
            $userData['password'], 
            false, 
            '帳號註冊失敗'
        );

        // Send request
        $response = $this->postJson('/api/register', $userData);

        // Assert response
        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'error' => '帳號註冊失敗'
            ]);
    }

    public function testRegisterFailsWhenVerificationRateLimitExceeded()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];

        $this->mockCheckUserExist($userData['email'], true);
        $this->mockValidateUserRegistration(
            $userData['name'], 
            $userData['email'], 
            $userData['password'], 
            $userData['confirm_password'], 
            true
        );
        $this->mockCreateUser($userData['name'], $userData['email'], $userData['password'], true);
        
        $this->registerService->shouldReceive('checkVerificationRequest')
            ->once()
            ->with($userData['email'])
            ->andReturn([
                'success' => false,
                'error' => '驗證次數超過當日上限',
                'data' => []
            ]);

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'error' => '驗證次數超過當日上限'
            ]);
    }

    public function testRegisterFailsWhenInsertSendRecordFails()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];

        $this->mockCheckUserExist($userData['email'], true);
        $this->mockValidateUserRegistration(
            $userData['name'], 
            $userData['email'], 
            $userData['password'], 
            $userData['confirm_password'], 
            true
        );
        $this->mockCreateUser($userData['name'], $userData['email'], $userData['password'], true);
        
        $this->registerService->shouldReceive('checkVerificationRequest')
            ->once()
            ->with($userData['email'])
            ->andReturn([
                'success' => true,
                'error' => '',
                'data' => []
            ]);
        
        $this->registerService->shouldReceive('insertSendRecord')
            ->once()
            ->with($userData['email'])
            ->andReturn([
                'success' => false,
                'error' => '紀錄插入失敗',
                'data' => []
            ]);

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '紀錄插入失敗'
            ]);
    }

    public function testRegisterFailsWhenEmailSendingFails()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];

        $this->mockCheckUserExist($userData['email'], true);
        $this->mockValidateUserRegistration(
            $userData['name'], 
            $userData['email'], 
            $userData['password'], 
            $userData['confirm_password'], 
            true
        );
        $this->mockCreateUser($userData['name'], $userData['email'], $userData['password'], true);
        
        $this->registerService->shouldReceive('checkVerificationRequest')
            ->once()
            ->with($userData['email'])
            ->andReturn([
                'success' => true,
                'error' => '',
                'data' => []
            ]);
        
        $insertSendRecordResponse = [
            'success' => true,
            'error' => '',
            'data' => [
                'hash' => 'test-hash',
                'requestId' => 123,
                'userName' => $userData['name'],
                'userId' => 1
            ]
        ];
        $this->registerService->shouldReceive('insertSendRecord')
            ->once()
            ->with($userData['email'])
            ->andReturn($insertSendRecordResponse);

        $this->registerService->shouldReceive('sendEmail')
            ->once()
            ->with(
                $userData['email'], 
                $insertSendRecordResponse['data']['hash'], 
                $insertSendRecordResponse['data']['requestId'], 
                $insertSendRecordResponse['data']['userName'], 
                $insertSendRecordResponse['data']['userId']
            )
            ->andReturn([
                'success' => false,
                'error' => '寄送驗證信失敗',
                'data' => []
            ]);

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'error' => '寄送驗證信失敗'
            ]);
    }

    public function testVerificationSuccessfully()
    {
        $verificationData = [
            'requestId' => 123,
            'hash' => 'test-hash',
            'userId' => 1
        ];

        $this->registerService->shouldReceive('inspectVerification')
            ->once()
            ->with($verificationData['requestId'], $verificationData['hash'])
            ->andReturn([
                'success' => true,
                'error' => '',
                'data' => []
            ]);

        $this->registerService->shouldReceive('clearUserRequest')
            ->once()
            ->with($verificationData['userId'])
            ->andReturn([
                'success' => true,
                'error' => '',
                'data' => []
            ]);

        $this->mockUpdateUserState($verificationData['userId'], true);

        $response = $this->postJson('/api/verify', $verificationData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['帳號驗證完成']
            ]);
    }

    public function testVerificationFailsWithInvalidVerificationData()
    {
        $verificationData = [
            'requestId' => 999,
            'hash' => 'invalid-hash',
            'userId' => 1
        ];

        $this->registerService->shouldReceive('inspectVerification')
            ->once()
            ->with($verificationData['requestId'], $verificationData['hash'])
            ->andReturn([
                'success' => false,
                'error' => '無效哈希碼',
                'data' => []
            ]);

        $response = $this->postJson('/api/verify', $verificationData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '無效哈希碼'
            ]);
    }


    public function testVerificationFailsWhenClearingRequestFails()
    {
        $verificationData = [
            'requestId' => 123,
            'hash' => 'test-hash',
            'userId' => 1
        ];
        $this->registerService->shouldReceive('inspectVerification')
            ->once()
            ->with($verificationData['requestId'], $verificationData['hash'])
            ->andReturn([
                'success' => true,
                'error' => '',
                'data' => []
            ]);

        $this->registerService->shouldReceive('clearUserRequest')
            ->once()
            ->with($verificationData['userId'])
            ->andReturn([
                'success' => false,
                'error' => '清除請求紀錄失敗',
                'data' => []
            ]);

        $response = $this->postJson('/api/verify', $verificationData);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'error' => '清除請求紀錄失敗'
            ]);
    }

    public function testVerificationFailsWhenUpdatingUserStateFails()
    {
        $verificationData = [
            'requestId' => 123,
            'hash' => 'test-hash',
            'userId' => 1
        ];

        $this->registerService->shouldReceive('inspectVerification')
            ->once()
            ->with($verificationData['requestId'], $verificationData['hash'])
            ->andReturn([
                'success' => true,
                'error' => '',
                'data' => []
            ]);

        $this->registerService->shouldReceive('clearUserRequest')
            ->once()
            ->with($verificationData['userId'])
            ->andReturn([
                'success' => true,
                'error' => '',
                'data' => []
            ]);

        $this->mockUpdateUserState($verificationData['userId'], false, '更新驗證狀態失敗');

        $response = $this->postJson('/api/verify', $verificationData);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'error' => '更新驗證狀態失敗'
            ]);
    }

    //模擬帳號已經建立
    private function mockCheckUserExist($email, $success, $error = '')
    {
        $response = [
            'success' => $success,
            'error' => $error,
            'data' => []
        ];
        
        $this->registerService->shouldReceive('checkUserExist')
            ->once()
            ->with($email)
            ->andReturn($response);
    }

    private function mockValidateUserRegistration($name, $email, $password, $confirmPassword, $success, $error = '')
    {
        $response = [
            'success' => $success,
            'error' => $error,
            'data' => []
        ];
        
        $this->registerService->shouldReceive('validateUserRegistration')
            ->once()
            ->with($name, $email, $password, $confirmPassword)
            ->andReturn($response);
    }

    private function mockCreateUser($name, $email, $password, $success, $error = '')
    {
        $data = [];
        if ($success) {
            $data = ['帳號註冊完成，請繼續至郵箱進行驗證'];
        }
        
        $response = [
            'success' => $success,
            'error' => $error,
            'data' => $data
        ];
        
        $this->registerService->shouldReceive('createUser')
            ->once()
            ->with($name, $email, $password)
            ->andReturn($response);
    }

    private function mockUpdateUserState($userId, $success, $error = '')
    {
        $data = [];
        if ($success) {
            $data = ['帳號驗證完成'];
        }
        
        $response = [
            'success' => $success,
            'error' => $error,
            'data' => $data
        ];
        
        $this->registerService->shouldReceive('updateUserState')
            ->once()
            ->with($userId)
            ->andReturn($response);
    }
}