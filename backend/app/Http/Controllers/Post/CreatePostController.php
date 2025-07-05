<?php

namespace App\Http\Controllers\Post;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Post\CreatePostService;
use App\Services\Broadcast\CreatePostBroadcastService;

class CreatePostController extends Controller {
    protected $createPostService;

    public function __construct(CreatePostService $createPostService, CreatePostBroadcastService $createPostBroadcastService) {
        $this->createPostService = $createPostService;
        $this->createPostBroadcastService = $createPostBroadcastService;
    }

    //新增貼文api
    public function createPost(Request $request) {
        $validatedData = $request->validate([
            'userId' => 'required|integer',
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'tag' => 'nullable|array',
            'base64' => 'nullable|string'
        ]);

        //在資料庫修改貼文者總發文數
        $changePostAmount = $this->createPostService->changePostAmount($validatedData['userId']);
        if (!$changePostAmount['success']) {
            return response()->json($changePostAmount, 422);
        }

        //上傳圖片
        $uploadBase64Image = $this->createPostService->uploadBase64Image($validatedData['base64']);
        if (!$uploadBase64Image['success']) {
           return response()->json($uploadBase64Image, 422);
        }

        //在資料庫新增貼文
        $createPostToDatabase = $this->createPostService->createPostToDatabase($validatedData['userId'], $validatedData['name'], $validatedData['content'], $validatedData['tag'], $uploadBase64Image['data']['url']);
        if (!$createPostToDatabase['success']) {
            return response()->json($createPostToDatabase, 422);
        }

        //在資料庫新增通知紀錄
        $notifyFollowersOfNewPost = $this->createPostService->notifyFollowersOfNewPost($validatedData['userId']);
        if (!$notifyFollowersOfNewPost['success']) {
            return response()->json($notifyFollowersOfNewPost, 422);
        }

        //發送通知給用戶粉絲
        $this->createPostBroadcastService->dispatchPostPublishedEvent($validatedData['userId']);

        return response()->json($createPostToDatabase,200);
    }
}