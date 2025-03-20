<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\AddSubscriberService;
use Illuminate\Http\Request;

class AddSubscriberController extends Controller {
    private $addSubscriberService;

    public function __construct(AddSubscriberService $addSubscriberService) {
        $this->addSubscriberService = $addSubscriberService;
    }

    // 添加訂閱者api
    public function addSubscriber(Request $request) {
        $validatedData = $request->validate([
            'userId' => 'required|integer',
            'userSubscriberId' => 'required|integer',
        ]);
        $userId = $validatedData['userId'];
        $userSubscriberId = $validatedData['userSubscriberId'];

        $addSubscriberToDatabase = $this->addSubscriberService->addSubscriberToDatabase($userId, $userSubscriberId);
        if (!$addSubscriberToDatabase['success']) {
            return response()->json($addSubscriberToDatabase, 422);
        }

        $addFollowerToDatabase = $this->addSubscriberService->addFollowerToDatabase($userSubscriberId, $userId);
        if (!$addFollowerToDatabase['success']) {
            return response()->json($addFollowerToDatabase, 422);
        }

        $updateUserSubscriberCount = $this->addSubscriberService->updateUserSubscriberCount($userId);
        if (!$updateUserSubscriberCount['success']) {
            return response()->json($updateUserSubscriberCount, 422);
        }

        $updateUserFollowerCount = $this->addSubscriberService->updateUserFollowerCount($userSubscriberId);
        if (!$updateUserFollowerCount['success']) {
            return response()->json($updateUserFollowerCount, 422);
        }

        return response()->json($updateUserFollowerCount,200);
    }
}