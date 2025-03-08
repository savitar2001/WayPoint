<?php

use Tests\TestCase;
use App\Services\Auth\SendEmailService;
use App\Models\User;
use App\Models\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEmailServiceTest extends TestCase {
    private $userMock;
    private $requestMock;
    private $sendEmailService;

    protected function setUp(): void {
        parent::setUp();
        $this->userMock = $this->createMock(User::class);
        $this->requestMock = $this->createMock(Request::class);
        $this->sendEmailService = new SendEmailService($this->userMock, $this->requestMock);
    }

    public function testCheckVerificationRequestWithAlreadyVerifiedUser() {
        $email = 'test@example.com';
        $type = 0;
        $mockResult = [
            'verified' => 1,
            'COUNT(requests.id)' => 0
        ];

        $this->userMock->expects($this->once())
            ->method('findUserWithSendEmailRequest')
            ->with($email, $type)
            ->willReturn($mockResult);

        $result = $this->sendEmailService->checkVerificationRequest($email, $type);

        $this->assertFalse($result['success']);
        $this->assertEquals('此帳號已驗證過', $result['error']);
    }

    public function testCheckVerificationRequestWithExceededLimit() {
        $email = 'test@example.com';
        $type = 0;
        $mockResult = [
            'verified' => 0,
            'COUNT(requests.id)' => config('mail.max_verification_requests')
        ];

        $this->userMock->expects($this->once())
            ->method('findUserWithSendEmailRequest')
            ->with($email, $type)
            ->willReturn($mockResult);

        $result = $this->sendEmailService->checkVerificationRequest($email, $type);

        $this->assertFalse($result['success']);
        $this->assertEquals('驗證次數超過當日上限', $result['error']);
    }

    public function testCheckVerificationRequestSuccess() {
        $email = 'test@example.com';
        $type = 0;
        $mockResult = [
            'verified' => 0,
            'COUNT(requests.id)' => 0
        ];

        $this->userMock->expects($this->once())
            ->method('findUserWithSendEmailRequest')
            ->with($email, $type)
            ->willReturn($mockResult);

        $result = $this->sendEmailService->checkVerificationRequest($email, $type);

        $this->assertTrue($result['success']);
        $this->assertEquals('', $result['error']);
    }

    public function testResetPasswordRequestWithExceededLimit() {
        $email = 'test@example.com';
        $type = 1;
        $mockResult = [
            'verified' => 1,
            'COUNT(requests.id)' => config('mail.max_passwordreset_requests')
        ];

        $this->userMock->expects($this->once())
            ->method('findUserWithSendEmailRequest')
            ->with($email, $type)
            ->willReturn($mockResult);

        $result = $this->sendEmailService->checkVerificationRequest($email, $type);

        $this->assertFalse($result['success']);
        $this->assertEquals('重設密碼次數超過當日上限', $result['error']);
    }

    public function testPasswordResetRequestSuccess() {
        $email = 'test@example.com';
        $type = 1;
        $mockResult = [
            'verified' => 0,
            'COUNT(requests.id)' => 0
        ];

        $this->userMock->expects($this->once())
            ->method('findUserWithSendEmailRequest')
            ->with($email, $type)
            ->willReturn($mockResult);

        $result = $this->sendEmailService->checkVerificationRequest($email, $type);

        $this->assertTrue($result['success']);
        $this->assertEquals('', $result['error']);
    }

    public function testInsertSendRecordFailure() {
        $email = 'test@example.com';
        $type = 0;
        $mockResult = [
            'id' => 1,
            'name' => 'Test User'
        ];

        $this->userMock->expects($this->once())
            ->method('findUserWithSendEmailRequest')
            ->with($email, $type)
            ->willReturn($mockResult);

        $this->requestMock->expects($this->once())
            ->method('recordSendEmailInformation')
            ->willReturn(-1);

        $result = $this->sendEmailService->insertSendRecord($email,$type);

        $this->assertFalse($result['success']);
        $this->assertEquals('紀錄插入失敗', $result['error']);
    }

    public function testInsertSendRecordSuccess() {
        $email = 'test@example.com';
        $type = 0;
        $mockResult = [
            'id' => 1,
            'name' => 'Test User'
        ];

        $this->userMock->expects($this->once())
            ->method('findUserWithSendEmailRequest')
            ->with($email, $type)
            ->willReturn($mockResult);

        $this->requestMock->expects($this->once())
            ->method('recordSendEmailInformation')
            ->willReturn(1);

        $result = $this->sendEmailService->insertSendRecord($email, $type);

        $this->assertTrue($result['success']);
        $this->assertEquals('', $result['error']);
    }

    public function testSendValidationEmailFailure() {
        Mail::fake();

        $email = 'test@example.com';
        $type = 0;
        
        $reflectionClass = new \ReflectionClass(SendEmailService::class);
        $tempProperty = $reflectionClass->getProperty('temp');
        $tempProperty->setAccessible(true);
        $tempProperty->setValue($this->sendEmailService, ['hash123', 1, 'Test User', 1]);

        Mail::shouldReceive('to')->andThrow(new \Exception('SMTP 連線失敗'));

        $result = $this->sendEmailService->sendEmail($email, $type);

        $this->assertFalse($result['success']);
        $this->assertEquals('寄送驗證信失敗', $result['error']);
    }

    public function testSendValidationEmailSuccess() {
        Mail::fake();

        $email = 'test@example.com';
        $type = 0;

        $reflectionClass = new \ReflectionClass(SendEmailService::class);
        $tempProperty = $reflectionClass->getProperty('temp');
        $tempProperty->setAccessible(true);
        $tempProperty->setValue($this->sendEmailService, ['hash123', 1, 'Test User', 1]);

        $result = $this->sendEmailService->sendEmail($email, $type);

        Mail::assertSent(\App\Mail\VerificationMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });

        $this->assertTrue($result['success']);
        $this->assertEquals('', $result['error']);

    }
}