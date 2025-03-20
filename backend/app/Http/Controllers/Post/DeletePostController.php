<?php

namespace App\Http\Controllers\Post;

use Illuminate\Http\Request;
use App\Services\Post\DeletePostService;
use App\Http\Controllers\Controller;

class DeletePostController extends Controller {
    protected $deletePostService;

    public function __construct(DeletePostService $deletePostService){
        $this->deletePostService = $deletePostService;
    }

    // 刪除貼文 API
    public function deletePost($userId, $postId,Request $request) {
        $userId = (int)$userId;
        $postId = (int)$postId;
        
        // 在資料庫修改貼文者總發文數
        $changePostAmount = $this->deletePostService->changePostAmount($userId);
        if (!$changePostAmount['success']) {
            return response()->json($changePostAmount, 422);
        }

        // 刪除 S3 上的圖片
        $deleteImage = $this->deletePostService->deleteImage($postId);
        if (!$deleteImage['success']) {
            return response()->json($deleteImage, 422);
        }

        // 在資料庫刪除貼文
        $deletePostToDatabase = $this->deletePostService->deletePostToDatabase($userId, $postId);
        if (!$deletePostToDatabase['success']) {
            return response()->json($deletePostToDatabase, 422);
        }

        return response()->json($deletePostToDatabase, 204);
    }
}