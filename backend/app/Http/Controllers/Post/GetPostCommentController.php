<?php

namespace App\Http\Controllers\Post;

use App\Services\Post\ReviewCommentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetPostCommentController extends Controller {
    private $reviewCommentService;

    public function __construct(ReviewCommentService $reviewCommentService) {
        $this->reviewCommentService = $reviewCommentService;
    }

    // 查詢某貼文的留言的內容及用戶資訊api
    public function getPostComment($postId) {
        if ($postId !== null) {
            $postId = (int) $postId;
        } else {
            return response()->json(['success'=> false, 'error' => '參數不足'], 400);
        }
 
        $fetchPostComment = $this->reviewCommentService->fetchPostComment($postId);
        if ($fetchPostComment['success'] !== true) {
            return response()->json($fetchPostComment, 422);
        }

        //將圖片網址替換成臨時圖片url
        for($i = 0; $i < count($fetchPostComment['data']); $i++) {
            $image =  $fetchPostComment['data'][$i]->avatar_url;
            $imageUrl = $this->reviewCommentService->generatePresignedUrl($image);
            if (!$imageUrl['success']) {
                return response()->json($imageUrl, 422);
            } else {
                $fetchPostComment['data'][$i]->avatar_url = $imageUrl['data']['url'];
            }
        }
        return response()->json($fetchPostComment, 200);
    }

    // 查詢某留言的回覆的內容及用戶資訊api
    public function getCommentReply($commentId) {
        if ($commentId !== null) {
            $commentId = (int) $commentId;
        } else {
            return response()->json(['success'=> false, 'error' => '參數不足'], 400);
        }
 
        $fetchCommentReply = $this->reviewCommentService->fetchCommentReply($commentId);
        if ($fetchCommentReply['success'] !== true) {
            return response()->json($fetchCommentReply, 422);
        }

        //將圖片網址替換成臨時圖片url
        for($i = 0; $i < count($fetchCommentReply['data']); $i++) {
            $image =  $fetchCommentReply['data'][$i]->avatar_url;
            if ($image == 'null') {
            } else {
                if (preg_match('/https?:\/\/[^\/]+\/(.+)/', $image ,$matches)) {
                    $filePath = $matches[1]; // 提取的部分
                }
                $imageUrl = $this->reviewCommentService->generatePresignedUrl($filePath);
                if (!$imageUrl['success']) {
                    return response()->json($imageUrl, 422);
                } else {
                    $fetchCommentReply['data'][$i]->avatar_url = $imageUrl['data']['url'];
                }
            }
        }
        return response()->json($fetchCommentReply, 200);
    }

}