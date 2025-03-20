<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\RemoveSubscriberService;
use Illuminate\Http\Request;

class RemoveSubscriberController extends Controller {
    private $removeSubscriberService;

    public function __construct(RemoveSubscriberService $removeSubscriberService) {
        $this->removeSubscriberService = $removeSubscriberService;
    }

    // 移除訂閱者
    public function removeSubscriber($followerId, $subscriberId,Request $request) {
        $followerId = (int)$followerId;
        $subscriberId = (int)$subscriberId;
    
        $removeSubscriberFromDatabase = $this->removeSubscriberService->removeSubscriberFromDatabase($followerId, $subscriberId);
        if (!$removeSubscriberFromDatabase['success']) {
            return response()->json($removeSubscriberFromDatabase, 422);
        }

        $removeFollowerToDatabase = $this->removeSubscriberService->removeFollowerToDatabase($subscriberId, $followerId);
        if (!$removeFollowerToDatabase['success']) {
            return response()->json($removeFollowerToDatabase, 422);
        }

        // 修改用戶追蹤數
        $updateSubscriberCount = $this->removeSubscriberService->updateUserSubscriberCount($followerId);
        if (!$updateSubscriberCount['success']) {
            return response()->json($updateSubscriberCount, 422);
        }

        // 修改用戶粉絲數
        $updateFollowerCount = $this->removeSubscriberService->updateUserFollowerCount($subscriberId);
        if (!$updateFollowerCount['success']) {
            return response()->json($updateFollowerCount, 422);
        }

        return response()->json($updateFollowerCount, 204);
    }
}