<?php
use Tests\TestCase;
use App\Services\User\AddSubscriberService;
use App\Models\User;
use App\Models\UserFollower;
use App\Models\UserSubscriber;

class AddSubscriberServiceTest extends TestCase {
    private $user;
    private $userFollower;
    private $userSubscriber;
    private $addSubscriberService;

    protected function setUp(): void {
        $this->user = $this->createMock(User::class);
        $this->userFollower = $this->createMock(UserFollower::class);
        $this->userSubscriber = $this->createMock(UserSubscriber::class);

        $this->addSubscriberService = new AddSubscriberService($this->user, $this->userFollower, $this->userSubscriber);
    }

    public function testAddSubscriberToDatabaseSuccess() {
        $this->userSubscriber->method('addSubscriber')->willReturn(true);

        $response = $this->addSubscriberService->addSubscriberToDatabase(1, 2);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testAddSubscriberToDatabaseFailure() {
        $this->userSubscriber->method('addSubscriber')->willReturn(false);

        $response = $this->addSubscriberService->addSubscriberToDatabase(1, 2);

        $this->assertFalse($response['success']);
        $this->assertEquals('添加訂閱者失敗', $response['error']);
    }

    public function testAddFollowerToDatabaseSuccess() {
        $this->userFollower->method('addFollower')->willReturn(true);

        $response = $this->addSubscriberService->addFollowerToDatabase(2, 1);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testAddFollowerToDatabaseFailure() {
        $this->userFollower->method('addFollower')->willReturn(false);

        $response = $this->addSubscriberService->addFollowerToDatabase(2, 1);

        $this->assertFalse($response['success']);
        $this->assertEquals('成為粉絲失敗', $response['error']);
    }

    public function testUpdateUserSubscriberCountFailure(){
        $userId = 1;

        $this->user->method('updateSubscriberCount')->with($userId, 1)->willReturn(false);

        $result = $this->addSubscriberService->updateUserSubscriberCount($userId);

        $this->assertFalse($result['success']);
        $this->assertEquals('修改用戶追蹤數失敗', $result['error']);
    }

    public function testUpdateUserFollowerCountSuccess(){
        $userId = 1;

        $this->user->method('updateFollowerCount')->with($userId, 1)->willReturn(true);

        $result = $this->addSubscriberService->updateUserFollowerCount($userId);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['error']);
    }

    public function testUpdateUserFollowerCountFailure(){
        $userId = 1;

        $this->user->method('updateFollowerCount')->with($userId, 1)->willReturn(false);

        $result = $this->addSubscriberService->updateUserFollowerCount($userId);

        $this->assertFalse($result['success']);
        $this->assertEquals('修改粉絲數失敗', $result['error']);
    }
}