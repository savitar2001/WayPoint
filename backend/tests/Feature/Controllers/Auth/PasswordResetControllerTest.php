<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Request as EmailRequest;
use App\Services\Auth\PasswordResetService;
use App\Services\Auth\SendEmailService;
use App\Services\Auth\VerifyService;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase {
    use RefreshDatabase, WithFaker;

    protected $passwordResetService;
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
        
        $this->passwordResetService = Mockery::mock(PasswordResetService::class, [
            $this->user,
            $this->sendEmailService,
            $this->verifyService
        ])->makePartial();
        
        $this->app->instance(PasswordResetService::class, $this->passwordResetService);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    public function testUserPasswordResetSuccessfully() {
        $userData = [
            'name' => 'leon',
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];
        $this->passwordResetService->shouldReceive('checkUserExist')->andReturn(['success'=>true]);
        $this->passwordResetService->shouldReceive('checkVerificationRequest')->andReturn(['success' => true]);
        
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
        $this->passwordResetService->shouldReceive('insertSendRecord')
            ->once()
            ->with($userData['email'])
            ->andReturn($insertSendRecordResponse);

        $this->passwordResetService->shouldReceive('sendEmail')
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

        $response = $this->postJson('/passwordReset', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'error' => '',
                'data' => ['請至郵件繼續完成密碼重設流程']
            ]);
    }

    public function testPasswordResetFailsWhenUserNotExists() {
        $userData = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];

        $this->passwordResetService->shouldReceive('checkUserExist')->andReturn(['success'=>false, 'error' => '此帳戶尚未建立']);

        $response = $this->postJson('/passwordReset', $userData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '此帳戶尚未建立'
            ]);
    }

    public function testPasswordResetFailsWhenVerificationRateLimitExceeded() {
         $userData = [
            'name' => 'leon',
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];

        $this->passwordResetService->shouldReceive('checkUserExist')->andReturn(['success'=>true]);
        $this->passwordResetService->shouldReceive('checkVerificationRequest')->andReturn(['success' => false, 'error' => '重設密碼次數超過當日上限']);

        $response = $this->postJson('/passwordReset', $userData);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'error' => '重設密碼次數超過當日上限'
            ]);
    }
    public function testPasswordResetFailsWhenInsertSendRecordFails() {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];

         $this->passwordResetService->shouldReceive('checkUserExist')->andReturn(['success'=>true]);
        $this->passwordResetService->shouldReceive('checkVerificationRequest')->andReturn(['success' => true]);
        
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
        $this->passwordResetService->shouldReceive('insertSendRecord')
            ->once()
            ->with($userData['email'])
            ->andReturn(['success' => false, 'error' => '紀錄插入失敗']);

        $response = $this->postJson('/passwordReset', $userData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '紀錄插入失敗'
            ]);
    }

    public function testPasswordResetFailsWhenEmailSendingFails() {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];

         $this->passwordResetService->shouldReceive('checkUserExist')->andReturn(['success'=>true]);
        $this->passwordResetService->shouldReceive('checkVerificationRequest')->andReturn(['success' => true]);
        
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
        $this->passwordResetService->shouldReceive('insertSendRecord')
            ->once()
            ->with($userData['email'])
            ->andReturn($insertSendRecordResponse);

        $this->passwordResetService->shouldReceive('sendEmail')
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

        $response = $this->postJson('/passwordReset', $userData);

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
            'userId' => 1,
            'password' => 'Password1!',
            'confirm_password' => 'Password1!'
        ];

        $this->passwordResetService->shouldReceive('inspectVerification')->andReturn(['success' => true]);
        $this->passwordResetService->shouldReceive('validateUserPasswordReset')->andReturn(['success'=>true]);
        $this->passwordResetService->shouldReceive('clearUserRequest')->andReturn(['success' => true]);
        $this->passwordResetService->shouldReceive('passwordReset')->andReturn(['success' => true,'data' => ['密碼更新完成']]);

        $response = $this->postJson('/passwordResetVerify', $verificationData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['密碼更新完成']
            ]);
    }

    public function testPasswordResetFailsWithInvalidPassword() {
        $verificationData = [
            'requestId' => 123,
            'hash' => 'test-hash',
            'userId' => 1,
            'password' => 'weakPass',
            'confirm_password' => 'weakPass'
        ];

        $this->passwordResetService->shouldReceive('inspectVerification')->andReturn(['success' => true]);
        $this->passwordResetService->shouldReceive('validateUserPasswordReset')->andReturn(['success'=>false, 'error'=> '密碼格式不符合規範']);

        $response = $this->postJson('/passwordResetVerify', $verificationData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '密碼格式不符合規範'
            ]);
    }

    public function testPasswordResetFailsWithMismatchedPasswords() {
        $verificationData = [
            'requestId' => 123,
            'hash' => 'test-hash',
            'userId' => 1,
            'password' => 'weakPass',
            'confirm_password' => 'weakPass1'
        ];

        $this->passwordResetService->shouldReceive('inspectVerification')->andReturn(['success' => true]);
        $this->passwordResetService->shouldReceive('validateUserPasswordReset')->andReturn(['success'=>false, 'error'=> '確認密碼與密碼不同']);


        $response = $this->postJson('/passwordResetVerify', $verificationData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => '確認密碼與密碼不同'
            ]);
    }

    public function testVerificationFailsWithInvalidVerificationData() {
        $verificationData = [
            'requestId' => 123,
            'hash' => 'test-hash',
            'userId' => 1,
            'password' => 'weakPass',
            'confirm_password' => 'weakPass1'
        ];

         $this->passwordResetService->shouldReceive('inspectVerification')->andReturn([
                'success' => false,
                'error' => '無效哈希碼',
                'data' => []
            ]);
            $this->passwordResetService->shouldReceive('validateUserPasswordReset')->andReturn(['success'=>true]);

        $response = $this->postJson('/passwordResetVerify', $verificationData);

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
            'userId' => 1,
            'password' => 'weakPass',
            'confirm_password' => 'weakPass1'
        ];

        $this->passwordResetService->shouldReceive('inspectVerification')->andReturn(['success' => true]);
        $this->passwordResetService->shouldReceive('validateUserPasswordReset')->andReturn(['success'=>true]);
        $this->passwordResetService->shouldReceive('clearUserRequest')->andReturn([
                'success' => false,
                'error' => '清除請求紀錄失敗'
            ]);

        $response = $this->postJson('/passwordResetVerify', $verificationData);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'error' => '清除請求紀錄失敗'
            ]);
    }

    public function testVerificationFailsWhenUpdatingUserPasswordFails() {
        $verificationData = [
            'requestId' => 123,
            'hash' => 'test-hash',
            'userId' => 1,
            'password' => 'weakPass',
            'confirm_password' => 'weakPass1'
        ];

        $this->passwordResetService->shouldReceive('inspectVerification')->andReturn(['success' => true]);
        $this->passwordResetService->shouldReceive('validateUserPasswordReset')->andReturn(['success'=>true]);
        $this->passwordResetService->shouldReceive('clearUserRequest')->andReturn(['success'=>true]);
        $this->passwordResetService->shouldReceive('passwordReset')->andReturn(['success'=>false, 'error' => '更新密碼失敗']);


        $response = $this->postJson('/passwordResetVerify', $verificationData);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'error' => '更新密碼失敗'
            ]);
    }
   
}