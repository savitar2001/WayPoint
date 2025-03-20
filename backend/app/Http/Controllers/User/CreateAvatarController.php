<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\CreateAvatarService;
use Illuminate\Http\Request;

class CreateAvatarController extends Controller {
    private $createAvatarService;

    public function __construct(CreateAvatarService $createAvatarService) {
        $this->createAvatarService = $createAvatarService;
    }

    // 上傳頭像並更新資料庫
    public function createAvatar(Request $request) {
        $validatedData = $request->validate([
            'userId' => 'required|integer',
            'base64Image' => 'required|string',
        ]);

        // 上傳圖片到 S3 並獲取圖片 URL
        $avatarUrl = $this->createAvatarService->uploadBase64Image($validatedData['base64Image']);
        if (!$avatarUrl['success']) {
            return response()->json($avatarUrl, 500);
        }

        // 更新使用者頭像資訊
        $createAvatar = $this->createAvatarService->createAvatar($validatedData['userId'], $avatarUrl['data']['url']);
        if (!$createAvatar['success']) {
            return response()->json($createAvatar, 500);
        }

        return response()->json($createAvatar, 200);
    }
}