<?php

namespace App\Http\Controllers\Post;

use App\Services\Post\DeleteCommentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeletePostCommentController extends Controller {
    private $deleteCommentService;

    public function __construct(DeleteCommentService $deleteCommentService) {
        $this->deleteCommentService = $deleteCommentService;
    }

    // 刪除用戶對貼文的留言
    public function deletePostComment($userId, $postId, $commentId,Request $request) {
        $userId = (int)$userId;
        $postId = (int)$postId;
        $commentId = (int)$commentId;

        $deleteCommentToPost = $this->deleteCommentService->deleteCommentToPost($userId, $commentId);
        if (!$deleteCommentToPost['success']) {
            return response()->json($deleteCommentToPost, 422);
        } 

        return response()->json($updatePostCommentsCount, 204);
    }
}