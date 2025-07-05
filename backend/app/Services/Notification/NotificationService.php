<?php
namespace App\Services\Notification;

use App\Repositories\Notification\NotificationRepositoryInterface;
use Illuminate\Support\Collection;
use Exception;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected NotificationRepositoryInterface $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * 獲取指定用戶的所有未讀通知。
     *
     * @param string $notifiableId
     * @param string $type
     * @return array
     */
    public function getUnreadNotifications(string $notifiableId, string $type)
    {
        try {
            $notifications = $this->notificationRepository->findUnreadByNotifiable($notifiableId, $type);
            return [
                'status' => 'success',
                'data' => $notifications
            ];
        } catch (Exception $e) {
            Log::error("Service error in getUnreadNotifications for {$notifiableId}, type {$type}: " . $e->getMessage(), ['exception' => $e]);
            return [
                'status' => 'error',
                'data' => null,
                'message' => '意外錯誤發生，取得通知失敗'
            ];
        }
    }

    /**
     * 將特定通知標記為已讀。
     *
     * @param string $notificationId
     * @return array
     */
    public function markNotificationAsRead(string $notificationId){
        try {
            $markAsRead = $this->notificationRepository->markAsRead($notificationId);
            if ($markAsRead) {
                return [
                    'status' => 'success',
                    'message' => '通知已成功標注為已讀'
                ];
            } else {
                return [
                    'status' => 'false', 
                    'message' => '通知標注已讀失敗'
                ];
            }
        } catch (Exception $e) {
            Log::error("Service error in markNotificationAsRead for {$notificationId}: " . $e->getMessage(), ['exception' => $e]);
            return [
                'status' => 'error',
                'message' => '通知標注已讀錯誤'
            ];
        }
    }

    /**
     * 將指定用戶的所有未讀通知標記為已讀。
     *
     * @param string $notifiableId
     * @param string $notifiableType
     * @return array The number of notifications marked as read.
     */
    public function markAllNotificationsAsRead(string $notifiableId, string $notifiableType)
    {
        try {
            $markAllAsRead = $this->notificationRepository->markAllAsRead($notifiableId, $notifiableType);
            if ($markAllAsRead) {
                return [
                    'status' => 'success',
                    'message' => '通知已成功標注為已讀'
                ];
            } else {
                return [
                    'status' => 'false', 
                    'message' => '通知標注已讀失敗'
                ];
            }
        } catch (\Exception $e) {
            Log::error("Service error in markNotificationAsRead for {$notifiableId}: " . $e->getMessage(), ['exception' => $e]);
            return [
                'status' => 'error',
                'message' => '通知標注已讀錯誤'
            ];
        }
    }
}