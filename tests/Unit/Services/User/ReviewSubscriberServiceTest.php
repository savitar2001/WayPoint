<?php

use Tests\TestCase;
use App\Services\User\ReviewSubscriberService;
use App\Models\UserSubscriber;

class ReviewSubscriberServiceTest extends TestCase {

    private $reviewSubscriberService;
    private $userSubscriber;

    protected function setUp(): void {
        $this->userSubscriber = $this->createMock(UserSubscriber::class);

        $this->reviewSubscriberService = new ReviewSubscriberService($this->userSubscriber);
    }

    public function testGetAllUserSubscribersSuccess() {
        $this->userSubscriber->method('getUserSubscribers')->willReturn(['userId' => 2, 'subscriberId' => 1]);

        $response = $this->reviewSubscriberService->getAllUserSubscribers(2);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
        $this->assertNotEmpty($response['data']);
    }

    public function testGetAllUserSubscribersFailure() {
        $this->userSubscriber->method('getUserSubscribers')->willReturn(false);

        $response = $this->reviewSubscriberService->getAllUserSubscribers(1);

        $this->assertFalse($response['success']);
        $this->assertEquals('查詢訂閱者失敗', $response['error']);
        $this->assertEmpty($response['data']);
    }
}