<?php

namespace App\Http\Controllers\Notification;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Notification\NotificationService;

class NotificationController extends Controller {
    protected $notificationService;

    public function __construct(NotificationService $notificationService) {
        $this->notificationService = $notificationService;
    }

    /**
     * 獲取指定用戶的所有未讀通知。
     *
     * @param string $notifiableId
     * @param string $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadNotifications(string $notifiableId, string $type) {
        if (!$notifiableId && !$type) {
            return response()->json(['success'=> false, 'error' => '參數不足'], 400);
        }
        $unreadNotifications = $this->notificationService->getUnreadNotifications($notifiableId, $type);
        if ($unreadNotifications['status'] === 'error') {
            return response()->json($unreadNotifications, 500);
        }

        return response()->json($unreadNotifications,200);
    }
     /**
     * 將特定通知標記為已讀。
     *
     * @param string $notificationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markNotificationAsRead(Request $request)
    {
        $validatedData = $request->validate([
            'notificationId' => 'required|string',
        ]);

        $result = $this->notificationService->markNotificationAsRead($validatedData['notificationId']);

        if ($result['status'] === 'error') {
            return response()->json($result, 500);
        }
        if ($result['status'] === 'false') {
            return response()->json($result, 404);
        }
        return response()->json($result);
    }

    /**
     * 將指定用戶的所有未讀通知標記為已讀。
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllNotificationsAsRead(Request $request)
    {
        $validatedData = $request->validate([
            'notifiableId' => 'required|string',
            'notifiableType' => 'required|string',
           
        ]);

        $result = $this->notificationService->markAllNotificationsAsRead($validatedData['notifiableId'], $validatedData['notifiableType']);

        if ($result['status'] === 'error') {
            return response()->json($result, 500);
        }
        if ($result['status'] === 'false') {
            return response()->json($result, 404); 
        }
        return response()->json($result);
    }

}