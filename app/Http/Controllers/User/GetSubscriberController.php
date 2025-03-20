<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\ReviewSubscriberService;
use Illuminate\Http\Request;

class GetSubscriberController extends Controller {
    private $reviewSubscriberService;

    public function __construct(ReviewSubscriberService $reviewSubscriberService)
    {
        $this->reviewSubscriberService = $reviewSubscriberService;
    }
 
    // 查詢用戶所有追蹤戶
    public function getSubscriber(Request $request) {
        $userId = $request->query('userId');

        if ($userId !== null) {
            $userId = (int) $userId;
        } else {
            return response()->json(['success'=> false, 'error' => '參數不足'], 400);
        }

        $getAllUserSubscribers = $this->reviewSubscriberService->getAllUserSubscribers($userId);
        if (!$getAllUserSubscribers['success']) {
            return response()->json($getAllUserSubscribers, 422);
        }

        //將圖片網址替換成臨時圖片url
        for($i = 0; $i < count($getAllUserSubscribers['data']); $i++) {
            $image =  $getAllUserSubscribers['data'][$i]['avatar_url'];
            $imageUrl = $this->reviewSubscriberService->generatePresignedUrl($image);
            if (!$imageUrl['success']) {
                return response()->json($imageUrl, 422);
            } else {
                $getAllUserSubscribers['data'][$i]['avatar_url'] = $imageUrl['data']['url'];
            }
        }
        return response()->json($getAllUserSubscribers, 200);

    }
}