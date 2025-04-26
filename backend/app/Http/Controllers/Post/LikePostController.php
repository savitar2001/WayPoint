<?php

namespace App\Http\Controllers\Post;

use App\Services\Post\LikePostService;
use App\Services\Post\UnlikePostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LikePostController extends Controller {
    private $likePostService;
    private $unlikePostService;

    public function __construct(LikePostService $likePostService, UnlikePostService $unlikePostService) {
        $this->likePostService = $likePostService;
        $this->unlikePostService = $unlikePostService; 
    }

    //按讚貼文api
    public function likePost(Request $request) {
        $validatedData = $request->validate([
            'userId' => 'required|integer',
            'postId' => 'required|integer'
        ]);

        //檢查使用者是否已經按讚過貼文
        $ifUserLikedPost = $this->likePostService->ifUserLikedPost($validatedData['userId'], $validatedData['postId']);
        if ($ifUserLikedPost['success'] === true) {
            //按讚
            $addPostLike = $this->likePostService->addPostLike($validatedData['userId'], $validatedData['postId']);
            if ($addPostLike['success'] === true) {
            } else {
                return response()->json($addPostLike, 422);
            }
        } else {
            //取消按讚
            $removePostLike = $this->unlikePostService->removePostLike($validatedData['userId'], $validatedData['postId']);
            if ($removePostLike['success'] === true) {
            } else {
                return response()->json($removePostLike, 422);
            }
        }
    }
}