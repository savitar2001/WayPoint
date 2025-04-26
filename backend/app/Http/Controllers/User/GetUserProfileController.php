<?php

namespace App\Http\Controllers\User;

use App\Services\User\UserProfileService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetUserProfileController extends Controller {
    private $userProfileService;

    public function __construct(UserProfileService $userProfileService){
        $this->userProfileService = $userProfileService;
    }

    // 取得使用者資訊
    public function getUserInformation($userId) {
        if ($userId !== null) {
            $userId = (int) $userId;
        } else {
            return response()->json(['success'=> false, 'error' => '參數不足'], 400);
        }

        $getUserInformation= $this->userProfileService->getUserInformation($userId);
        if (!$getUserInformation['success']) {
            return response()->json($getUserInformation, 422);
        }

         //將圖片網址替換成臨時圖片url
        $image =  $getUserInformation['data']['avatarUrl'];
        $imageUrl = $this->userProfileService->generatePresignedUrl($image);
        if (!$imageUrl['success']) {
            return response()->json($imageUrl, 422);
        } else {
            $getUserInformation['data']['avatarUrl'] = $imageUrl['data']['url'];
        }

        return response()->json($getUserInformation, 200);
    }

    //透過名字尋找用戶
    public function searchByName($name) {
        if ($name == null) {
            return response()->json(['success'=> false, 'error' => '參數不足'], 400);
        }
            
        $getUserByName = $this->userProfileService->getUserByName($name);
        if (!$getUserByName['success']) {
            return response()->json($getUserByName, 422);
        }

         //將圖片網址替換成臨時圖片url
        $image =  $getUserByName['data']['avatarUrl'];
        if ($image == 'null') {
        } else {
            if (preg_match('/https?:\/\/[^\/]+\/(.+)/', $image ,$matches)) {
                $filePath = $matches[1]; // 提取的部分
            }
            $imageUrl = $this->userProfileService->generatePresignedUrl($filePath);
            if (!$imageUrl['success']) {
                return response()->json($imageUrl, 422);
            } else {
                $getUserByName['data']['avatarUrl'] = $imageUrl['data']['url'];
            }
        }
        
        return response()->json($getUserByName, 200);
    }
}