<?php

namespace Tests\Unit\Services\Notification;

use App\Repositories\Notification\NotificationRepositoryInterface;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\TestCase;
use Mockery;
use Exception;

class NotificationServiceTest extends TestCase
{
    protected Mockery\MockInterface $notificationRepositoryMock;
    protected NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationRepositoryMock = Mockery::mock(NotificationRepositoryInterface::class);
        $this->notificationService = new NotificationService($this->notificationRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetUnreadNotificationsReturnsSuccessWithData()
    {
        $notifiableId = 'user1';
        $type = 'test_type';
        $expectedNotifications = new Collection(['notification1', 'notification2']);

        $this->notificationRepositoryMock
            ->shouldReceive('findUnreadByNotifiable')
            ->once()
            ->with($notifiableId, $type)
            ->andReturn($expectedNotifications);

        $result = $this->notificationService->getUnreadNotifications($notifiableId, $type);

        $this->assertEquals([
            'status' => 'success',
            'data' => $expectedNotifications
        ], $result);
    }

    public function test_getUnreadNotifications_returns_error_on_exception()
    {
        $notifiableId = 'user1';
        $type = 'test_type';

        $this->notificationRepositoryMock
            ->shouldReceive('findUnreadByNotifiable')
            ->once()
            ->with($notifiableId, $type)
            ->andThrow(new Exception('DB error'));

        Log::shouldReceive('error')->once();

        $result = $this->notificationService->getUnreadNotifications($notifiableId, $type);

        $this->assertEquals([
            'status' => 'error',
            'data' => null,
            'message' => '意外錯誤發生，取得通知失敗'
        ], $result);
    }

    public function test_getUnreadNotifications_with_empty_params()
    {
        $this->notificationRepositoryMock
            ->shouldReceive('findUnreadByNotifiable')
            ->once()
            ->with('', '')
            ->andThrow(new Exception('Invalid params'));

        Log::shouldReceive('error')->once();

        $result = $this->notificationService->getUnreadNotifications('', '');

        $this->assertEquals([
            'status' => 'error',
            'data' => null,
            'message' => '意外錯誤發生，取得通知失敗'
        ], $result);
    }

    public function test_markNotificationAsRead_returns_success()
    {
        $notificationId = 'notif1';

        $this->notificationRepositoryMock
            ->shouldReceive('markAsRead')
            ->once()
            ->with($notificationId)
            ->andReturn(true);

        $result = $this->notificationService->markNotificationAsRead($notificationId);

        $this->assertEquals([
            'status' => 'success',
            'message' => '通知已成功標注為已讀'
        ], $result);
    }

    public function test_markNotificationAsRead_returns_false_on_failure()
    {
        $notificationId = 'notif1';

        $this->notificationRepositoryMock
            ->shouldReceive('markAsRead')
            ->once()
            ->with($notificationId)
            ->andReturn(false);

        $result = $this->notificationService->markNotificationAsRead($notificationId);

        $this->assertEquals([
            'status' => 'false',
            'message' => '通知標注已讀失敗'
        ], $result);
    }

    public function test_markNotificationAsRead_returns_error_on_exception()
    {
        $notificationId = 'notif1';

        $this->notificationRepositoryMock
            ->shouldReceive('markAsRead')
            ->once()
            ->with($notificationId)
            ->andThrow(new Exception('DB error'));

        Log::shouldReceive('error')->once();

        $result = $this->notificationService->markNotificationAsRead($notificationId);

        $this->assertEquals([
            'status' => 'error',
            'message' => '通知標注已讀錯誤'
        ], $result);
    }

    public function test_markNotificationAsRead_with_invalid_id()
    {
        $invalidId = 'invalid_id';

        $this->notificationRepositoryMock
            ->shouldReceive('markAsRead')
            ->once()
            ->with($invalidId)
            ->andReturn(false);

        $result = $this->notificationService->markNotificationAsRead($invalidId);

        $this->assertEquals([
            'status' => 'false',
            'message' => '通知標注已讀失敗'
        ], $result);
    }

    public function test_markAllNotificationsAsRead_returns_success()
    {
        $notifiableId = 'user1';
        $notifiableType = 'App\Models\User';

        $this->notificationRepositoryMock
            ->shouldReceive('markAllAsRead')
            ->once()
            ->with($notifiableId, $notifiableType)
            ->andReturn(5);

        $result = $this->notificationService->markAllNotificationsAsRead($notifiableId, $notifiableType);

        $this->assertEquals([
            'status' => 'success',
            'message' => '通知已成功標注為已讀'
        ], $result);
    }

    public function test_markAllNotificationsAsRead_returns_false_on_failure()
    {
        $notifiableId = 'user1';
        $notifiableType = 'App\Models\User';

        $this->notificationRepositoryMock
            ->shouldReceive('markAllAsRead')
            ->once()
            ->with($notifiableId, $notifiableType)
            ->andReturn(0);

        $result = $this->notificationService->markAllNotificationsAsRead($notifiableId, $notifiableType);

        $this->assertEquals([
            'status' => 'false',
            'message' => '通知標注已讀失敗'
        ], $result);
    }

    public function test_markAllNotificationsAsRead_with_invalid_params()
    {
        $invalidId = 'invalid_user';
        $invalidType = 'InvalidType';

        $this->notificationRepositoryMock
            ->shouldReceive('markAllAsRead')
            ->once()
            ->with($invalidId, $invalidType)
            ->andReturn(0);

        $result = $this->notificationService->markAllNotificationsAsRead($invalidId, $invalidType);

        $this->assertEquals([
            'status' => 'false',
            'message' => '通知標注已讀失敗'
        ], $result);
    }

    public function test_markAllNotificationsAsRead_returns_error_on_exception()
    {
        $notifiableId = 'user1';
        $notifiableType = 'App\Models\User';

        $this->notificationRepositoryMock
            ->shouldReceive('markAllAsRead')
            ->once()
            ->with($notifiableId, $notifiableType)
            ->andThrow(new Exception('DB error'));

        Log::shouldReceive('error')->once();

        $result = $this->notificationService->markAllNotificationsAsRead($notifiableId, $notifiableType);

        $this->assertEquals([
            'status' => 'error',
            'message' => '通知標注已讀錯誤'
        ], $result);
    }
}