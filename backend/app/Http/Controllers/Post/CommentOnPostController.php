<?php
namespace App\Http\Controllers\Post;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Post\AddCommentService;

class CommentOnPostController extends Controller {
    protected $addCommentService;

    public function __construct(AddCommentService $addCommentService) {
        $this->addCommentService = $addCommentService;
    }

    // 新增對貼文的評論
    public function commentOnPost(Request $request) {
        $validatedData = $request->validate([
            'userId' => 'required|integer',
            'postId' => 'required|integer',
            'comment' => 'required|string',
        ]);

        $addCommentToPost = $this->addCommentService->addCommentToPost($validatedData['postId'], $validatedData['userId'], $validatedData['comment']);
        if (!$addCommentToPost['success']) {
            return response()->json($addCommentToPost, 422);
        }

        return response()->json($addCommentToPost, 200);
    }
}