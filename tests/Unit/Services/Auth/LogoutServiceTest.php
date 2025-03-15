<?php

namespace Tests\Unit\Services\Auth;

use App\Services\Auth\LogoutService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Mockery;
use Exception;

class LogoutServiceTest extends TestCase {
    protected $logoutService;

    public function setUp(): void {
        parent::setUp();
        $this->logoutService = new LogoutService();
    }

    public function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    public function testLogoutSuccess() {
        Auth::shouldReceive('logout')->once();

        Session::shouldReceive('invalidate')->once();

        Session::shouldReceive('regenerateToken')->once();

        $response = $this->logoutService->logout();

        $this->assertTrue($response['success']);
        $this->assertEquals(['成功登出'], $response['data']);
        $this->assertEquals('', $response['error']);
    }

    public function testLogoutFailureOnClearAuth() {
        Auth::shouldReceive('logout')
            ->once()
            ->andThrow(new Exception('Auth 錯誤'));

        $response = $this->logoutService->logout();

        $this->assertFalse($response['success']);
        $this->assertEquals([], $response['data']);
        $this->assertEquals('登出發生錯誤: Auth 錯誤', $response['error']);
    }

    public function testLogoutFailureOnClearSession() {
        Auth::shouldReceive('logout')->once();

        Session::shouldReceive('invalidate')->once()->andThrow(new Exception('Session 錯誤'));

        $response = $this->logoutService->logout();

        $this->assertFalse($response['success']);
        $this->assertEquals([], $response['data']);
        $this->assertEquals('登出發生錯誤: Session 錯誤', $response['error']);
    }

    public function testLogoutFailureOnRegenerateToken() {
        Auth::shouldReceive('logout')->once();

        Session::shouldReceive('invalidate')->once();

        Session::shouldReceive('regenerateToken')->once()->andThrow(new Exception('Token 錯誤'));

        $response = $this->logoutService->logout();

        $this->assertFalse($response['success']);
        $this->assertEquals([], $response['data']);
        $this->assertEquals('登出發生錯誤: Token 錯誤', $response['error']);
    }
}