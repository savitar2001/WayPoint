<?php

namespace App\Http\Controllers\Post;

use App\Services\Post\ReviewPostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetPostController extends Controller {
    private $reviewPostService;

    public function __construct(ReviewPostService $reviewPostService) {
        $this->reviewPostService = $reviewPostService;
    }

    // 查詢貼文資訊
    public function getPost($userId = null, $postId = null, $tag = null) {
        if ($userId === "null") {
            $userId = null;
        }
      
        if ($userId !== null) {
            $userId = (int) $userId;
        }
        if ($postId === "null") {
            $postId = null;
        }

        if ($postId !== null) {
            $postId = (int) $postId;
        }

        if ($tag === "null") {
            $tag = null;
        }

        if (!$userId && !$postId && !$tag) {
            return response()->json(['success'=> false, 'error' => '參數不足'], 400);
        }

        // 查詢貼文資訊
        $fetchPostInfo = $this->reviewPostService->fetchPostInfo($userId, $postId, $tag);
        if (!$fetchPostInfo['success']) {
            return response()->json($fetchPostInfo, 422);
        }

        //將圖片網址替換成臨時圖片url
        for($i = 0; $i < count($fetchPostInfo['data']); $i++) {
            $image =  $fetchPostInfo['data'][$i]->image_url;
            if (preg_match('/https?:\/\/[^\/]+\/(.+)/', $image ,$matches)) {
                $filePath = $matches[1]; // 提取的部分
            }
            $imageUrl = $this->reviewPostService->generatePresignedUrl($filePath);
            if (!$imageUrl['success']) {
                return response()->json($imageUrl, 422);
            } else {
                $fetchPostInfo['data'][$i]->image_url = $imageUrl['data']['url'];
            }
        }
        return response()->json($fetchPostInfo, 200);
    }
}