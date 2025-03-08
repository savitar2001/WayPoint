<?php

use Tests\TestCase;
use App\Services\Auth\VerifyService;
use App\Models\User;
use App\Models\Request;

class VerifyServiceTest extends TestCase
{
    private $verifyService;
    private $userMock;
    private $requestMock;

    protected function setUp(): void {
        parent::setUp();
    
        $this->userMock = $this->createMock(User::class);
        $this->requestMock = $this->createMock(Request::class);
        
        $this->verifyService = new VerifyService($this->userMock, $this->requestMock);
    }

    public function testCheckVerificationWithExpiredHash() {
        $userId = 1;
        $type = 0;
        $hash = 'valid_hash';
        
        $this->requestMock->expects($this->once())
            ->method('findSendEmailInformation')
            ->with($userId, $type) 
            ->willReturn([
                'timestamp' => time() - 60 * 60 * 25,
                'hash' => $hash
            ]);

        $result = $this->verifyService->inspectVerification($userId, $hash, $type);

        $this->assertFalse($result['success']);
        $this->assertEquals('此封信件已過期，請重新請求', $result['error']);
    }

    public function testCheckVerificationWithInvalidHash() {
        $userId = 1;
        $type = 0;
        $hash = 'invalid_hash';
        
        $this->requestMock->expects($this->once())
            ->method('findSendEmailInformation')
            ->with($userId, $type)
            ->willReturn([
                'timestamp' => time(), 
                'hash' => 'different_hash'
            ]);

        $result = $this->verifyService->inspectVerification($userId, $hash, $type);

        $this->assertFalse($result['success']);
        $this->assertEquals('無效請求', $result['error']);
    }

    public function testCheckVerificationSuccess() {
        $userId = 1;
        $type = 0;
        $hash = 'valid_hash';
        $this->requestMock->expects($this->once())
            ->method('findSendEmailInformation')
            ->with($userId, $type)
            ->willReturn([
                'timestamp' => time(),
                'hash' => $hash
            ]);

        $result = $this->verifyService->inspectVerification($userId, $hash, $type);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['error']);
    }

    public function testFailedClearRecord() {
        $userId = 1;
        $type = 0;
        
        $this->requestMock->expects($this->once())
            ->method('clearRequestRecord')
            ->with($userId, $type)
            ->willReturn(0);

        $result = $this->verifyService->clearUserRequest($userId, $type);

        $this->assertFalse($result['success']);
        $this->assertEquals('清除請求紀錄失敗', $result['error']);
    }

    public function testClearRecordSuccess() {
        $userId = 1;
        $type = 0;
            
        $this->requestMock->expects($this->once())
            ->method('clearRequestRecord')
            ->with($userId, $type)
            ->willReturn(1);

        $result = $this->verifyService->clearUserRequest($userId, $type);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['error']);
    }
}