<?php
use Tests\TestCase;
use App\Services\Auth\DeleteAccountService;
use App\Models\User;
use App\Models\Post;
use App\Models\LoginAttempt;

class DeleteAccountServiceTest extends TestCase {
    private $deleteAccountService;
    private $user;
    private $loginAttempt;
    private $post;

    protected function setUp(): void {
        $this->user = $this->createMock(User::class);
        $this->loginAttempt = $this->createMock(LoginAttempt::class);
        $this->post = $this->createMock(Post::class);
        $this->deleteAccountService = new DeleteAccountService($this->user, $this->loginAttempt, $this->post);
    }

    public function testClearLoginAttemptSuccess(){
        $userId = 1;
        $this->loginAttempt->method('clearAttempt')->with($userId)->willReturn(true);

        $response = $this->deleteAccountService->clearLoginAttempt($userId);
        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }
    public function testClearLoginAttemptFail(){
        $userId = 1;
        $this->loginAttempt->method('clearAttempt')->with($userId)->willReturn(false);

        $response = $this->deleteAccountService->clearLoginAttempt($userId);
        $this->assertFalse($response['success']);
        $this->assertEquals('清除登入請求紀錄失敗', $response['error']);
    }
    public function testClearPostSuccess(){
        $userId = 1;
        $this->post->method('deletePost')->with($userId)->willReturn(true);

        $response = $this->deleteAccountService->clearPost($userId);
        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }
    public function testClearPostFail(){
        $userId = 1;
        $this->post->method('deletePost')->with($userId)->willReturn(false);

        $response = $this->deleteAccountService->clearPost($userId);
        $this->assertFalse($response['success']);
        $this->assertEquals('刪除貼文失敗', $response['error']);

    }
    public function testClearUserInformationSuccess(){
        $userId = 1;
        $this->user->method('deleteUserInformation')->with($userId)->willReturn(true);

        $response = $this->deleteAccountService->clearUserInformation($userId);
        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }
    public function testClearUserInformationFail(){
        $userId = 1;
        $this->user->method('deleteUserInformation')->with($userId)->willReturn(false);

        $response = $this->deleteAccountService->clearUserInformation($userId);
        $this->assertFalse($response['success']);
        $this->assertEquals('刪除用戶資料失敗', $response['error']);
    }
}