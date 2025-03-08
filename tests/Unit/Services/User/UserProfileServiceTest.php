<?php

use Tests\TestCase;
use App\Services\User\UserProfileService;
use App\Models\User;

class UserProfileServiceTest extends TestCase {
    private $user;
    private $userProfileService;

    protected function setUp(): void {
        $this->user = $this->createMock(User::class);
        $this->userProfileService = new UserProfileService($this->user);
    }

    public function testGetUserInformationSuccess() {
        $userId = 1;

        $userInformation = [
            'id' => 1,
            'name' => 'John Doe',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'post_amount' => 10,
            'subscriber_count' => 200,
            'follower_count' => 150
        ];

        $this->user->method('userInformation')->with($userId)->willReturn($userInformation);

        $response = $this->userProfileService->getUserInformation($userId);

        $this->assertTrue($response['success']);
        $this->assertEquals('John Doe', $response['data']['name']);
        $this->assertEquals('https://example.com/avatar.jpg', $response['data']['avatarUrl']);
        $this->assertEquals(10, $response['data']['postAmount']);
        $this->assertEquals(200, $response['data']['subscriberCount']);
        $this->assertEquals(150, $response['data']['followerCount']);
    }

    public function testGetUserInformationFailure() {
        $userId = 999;

        $this->user->method('userInformation')->with($userId)->willReturn(false);

        $response = $this->userProfileService->getUserInformation($userId);

        $this->assertFalse($response['success']);
        $this->assertEquals('無法取得使用者資訊', $response['error']);
    }
}