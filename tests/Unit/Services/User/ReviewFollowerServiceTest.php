<?php

use Tests\TestCase;
use App\Services\User\ReviewFollowerService;
use App\Models\UserFollower;

class ReviewFollowerServiceTest extends TestCase {

    private $reviewFollowerService;
    private $userFollower;

    protected function setUp(): void {
        $this->userFollower = $this->createMock(UserFollower::class);

        $this->reviewFollowerService = new ReviewFollowerService($this->userFollower);
    }

    public function testGetAllUserFollowersSuccesgetUserFollowerss() {
        $this->userFollower->method('getUserFollowers')->willReturn([['userId' => 1, 'followerId' => 2]]);

        $response = $this->reviewFollowerService->getAllUserFollowers(1);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
        $this->assertNotEmpty($response['data']);
    }

    public function testGetAllUserFollowersFailure() {
        $this->userFollower->method('getUserFollowers')->willReturn(false);

        $response = $this->reviewFollowerService->getAllUserFollowers(1);

        $this->assertFalse($response['success']);
        $this->assertEquals('查詢粉絲失敗', $response['error']);
        $this->assertEmpty($response['data']);
    }
}
