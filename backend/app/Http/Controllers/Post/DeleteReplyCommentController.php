<?php

namespace App\Http\Controllers\Post;

use App\Services\Post\DeleteCommentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeleteReplyCommentController extends Controller {
    private $deleteCommentService;

    public function __construct(DeleteCommentService $deleteCommentService) {
        $this->deleteCommentService = $deleteCommentService;
    }

    // 刪除用戶對留言的回覆
    public function deleteReplyComment($userId, $commentId, $replyId,Request $request) {
        $userId = (int)$userId;
        $replyId = (int)$replyId;
        $commentId = (int)$commentId;

        $deleteReplyToComment = $this->deleteCommentService->deleteReplyToComment($userId, $replyId);
        if (!$deleteReplyToComment['success']) {
            return response()->json($deleteReplyToComment, 422);
        } 

        $updateCommentReplyCount = $this->deleteCommentService->updateCommentReplyCount($commentId);
        if (!$updateCommentReplyCount['success']) {
            return response()->json($updateCommentReplyCount, 422);
        }

        return response()->json($updateCommentReplyCount, 204);
    }
}