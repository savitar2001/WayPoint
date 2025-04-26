<?php

namespace App\Http\Controllers\Post;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Post\CreatePostService;

class CreatePostController extends Controller {
    protected $createPostService;

    public function __construct(CreatePostService $createPostService) {
        $this->createPostService = $createPostService;
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

        return response()->json($createPostToDatabase,200);
    }
}