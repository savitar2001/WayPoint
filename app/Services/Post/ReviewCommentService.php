<?php

namespace App\Services\Post;

use App\Models\PostComment;

class ReviewCommentService {
    private $postComment;
    private $response;

    //創立postComment對象
    public function  __construct(PostComment $postComment) {
        $this->postComment = $postComment;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //查詢某貼文的留言
    public function fetchPostComment($postId) {
        $res = $this->postComment->getPostComment($postId);
        if ($res === false) {
            $this->response['error'] = '查詢貼文留言失敗';
        } else {
            $this->response['success'] = true;
            $this->response['data'][] = $res;
        }
        return $this->response;
    }

    //查詢某留言的回覆留言
    public function fetchCommentReply($commentId) {
        $res = $this->postComment->getCommentReply($commentId);
        if ($res === false) {
            $this->response['error'] = '查詢該留言的回覆失敗';
        } else {
            $this->response['success'] = true;
            $this->response['data'][] = $res;
        }
        return $this->response; 
    }

}