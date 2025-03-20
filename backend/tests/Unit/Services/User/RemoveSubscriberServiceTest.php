<?php

use Tests\TestCase;
use App\Services\User\RemoveSubscriberService;
use App\Models\User;
use App\Models\UserFollower;
use App\Models\UserSubscriber;

class RemoveSubscriberServiceTest extends TestCase {
    
    private $removeSubscriberService;
    private $user;
    private $userFollower;
    private $userSubscriber;

    protected function setUp(): void {
        $this->user = $this->createMock(User::class);
        $this->userFollower = $this->createMock(UserFollower::class);
        $this->userSubscriber = $this->createMock(UserSubscriber::class);

        $this->removeSubscriberService = new RemoveSubscriberService(
            $this->user,
            $this->userFollower,
            $this->userSubscriber
        );
    }

    public function testRemoveSubscriberFromDatabaseSuccess() {
        $this->userSubscriber->method('removeSubscriber')->willReturn(true);
        
        $response = $this->removeSubscriberService->removeSubscriberFromDatabase(1, 2);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testRemoveSubscriberFromDatabaseFailure() {
        $this->userSubscriber->method('removeSubscriber')->willReturn(false);
        
        $response = $this->removeSubscriberService->removeSubscriberFromDatabase(1, 2);

        $this->assertFalse($response['success']);
        $this->assertEquals('移除訂閱者失敗', $response['error']);
    }

    public function testRemoveFollowerToDatabaseSuccess() {
        $this->userFollower->method('removeSubscriber')->willReturn(true);

        $response = $this->removeSubscriberService->removeFollowerToDatabase(2, 1);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testRemoveFollowerToDatabaseFailure() {
        $this->userFollower->method('removeSubscriber')->willReturn(false);

        $response = $this->removeSubscriberService->removeFollowerToDatabase(2, 1);

        $this->assertFalse($response['success']);
        $this->assertEquals('移除粉絲失敗', $response['error']);
    }

    public function testUpdateUserSubscriberCountSuccess() {
        $this->user->method('updateSubscriberCount')->willReturn(true);

        $response = $this->removeSubscriberService->updateUserSubscriberCount(1);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testUpdateUserSubscriberCountFailure() {
        $this->user->method('updateSubscriberCount')->willReturn(false);

        $response = $this->removeSubscriberService->updateUserSubscriberCount(1);

        $this->assertFalse($response['success']);
        $this->assertEquals('修改用戶追蹤數失敗', $response['error']);
    }

    public function testUpdateUserFollowerCountSuccess() {
        $this->user->method('updateFollowerCount')->willReturn(true);

        $response = $this->removeSubscriberService->updateUserFollowerCount(1);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testUpdateUserFollowerCountFailure() {
        $this->user->method('updateFollowerCount')->willReturn(false);

        $response = $this->removeSubscriberService->updateUserFollowerCount(1);

        $this->assertFalse($response['success']);
        $this->assertEquals('修改粉絲數失敗', $response['error']);
    }
}
