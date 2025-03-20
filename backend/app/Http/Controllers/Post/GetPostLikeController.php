<?php

namespace App\Http\Controllers\Post;

use App\Services\Post\ReviewLikeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetPostLikeController extends Controller {
    private $reviewLikeService;

    public function __construct(ReviewLikeService $reviewLikeService) {
        $this->reviewLikeService = $reviewLikeService;
    }

    // 查詢喜歡某篇貼文的用戶api
    public function getPostLike(Request $request) {
        $postId = $request->query('postId');

        if ($postId !== null) {
            $postId = (int) $postId;
        } else {
            return response()->json(['success'=> false, 'error' => '參數不足'], 400);
        }
 
        $getLikeUserByPost = $this->reviewLikeService->getLikeUserByPost($postId);
        if ($getLikeUserByPost['success'] !== true) {
            return response()->json($getLikeUserByPost, 422);
        }

        //將圖片網址替換成臨時圖片url
        for($i = 0; $i < count($getLikeUserByPost['data']); $i++) {
            $image =  $getLikeUserByPost['data'][$i]['avatar_url'];
            $imageUrl = $this->reviewLikeService->generatePresignedUrl($image);
            if (!$imageUrl['success']) {
                return response()->json($imageUrl, 422);
            } else {
                $getLikeUserByPost['data'][$i]['avatar_url'] = $imageUrl['data']['url'];
            }
        }
        return response()->json($getLikeUserByPost, 200);
    }

}