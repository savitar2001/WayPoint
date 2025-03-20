<?php
namespace App\Http\Controllers\Post;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Post\AddCommentService;

class ReplyToCommentController extends Controller {
    protected $addCommentService;

    public function __construct(AddCommentService $addCommentService) {
        $this->addCommentService = $addCommentService;
    }

    // 新增對評論的回覆
    public function replyToComment(Request $request) {
        $validatedData = $request->validate([
            'userId' => 'required|integer',
            'commentId' => 'required|integer',
            'comment' => 'required|string',
        ]);

        $addReplyToComment = $this->addCommentService->addReplyToComment($validatedData['commentId'], $validatedData['userId'], $validatedData['comment']);
        if (!$addReplyToComment['success']) {
            return response()->json($addReplyToComment, 422);
        }

        $updateCommentReplyCount = $this->addCommentService->updateCommentReplyCount($validatedData['commentId']);
        if (!$updateCommentReplyCount['success']) {
            return response()->json($updateCommentReplyCount, 422);
        }

        return response()->json($updateCommentReplyCount, 200);
    }
}