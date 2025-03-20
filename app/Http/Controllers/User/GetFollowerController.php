<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\ReviewFollowerService;
use Illuminate\Http\Request;

class GetFollowerController extends Controller {
    private $reviewFollowerService;

    public function __construct(ReviewFollowerService $reviewFollowerService)
    {
        $this->reviewFollowerService = $reviewFollowerService;
    }

    // 查詢用戶所有粉絲
    public function getFollower(Request $request) {
        $userId = $request->query('userId');

        if ($userId !== null) {
            $userId = (int) $userId;
        } else {
            return response()->json(['success'=> false, 'error' => '參數不足'], 400);
        }

        $getAllUserFollowers = $this->reviewFollowerService->getAllUserFollowers($userId);
        if (!$getAllUserFollowers['success']) {
            return response()->json($getAllUserFollowers, 422);
        }

        //將圖片網址替換成臨時圖片url
        for($i = 0; $i < count($getAllUserFollowers['data']); $i++) {
            $image =  $getAllUserFollowers['data'][$i]['avatar_url'];
            $imageUrl = $this->reviewFollowerService->generatePresignedUrl($image);
            if (!$imageUrl['success']) {
                return response()->json($imageUrl, 422);
            } else {
                $getAllUserFollowers['data'][$i]['avatar_url'] = $imageUrl['data']['url'];
            }
        }
        return response()->json($getAllUserFollowers, 200);

    }
}