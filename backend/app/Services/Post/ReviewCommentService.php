<?php

namespace App\Services\Post;

use App\Models\PostComment;
use App\Services\Image\S3StorageService;

class ReviewCommentService {
    private $postComment;
    private $s3StorageService;
    private $response;

    //創立postComment、s3StorageService對象
    public function  __construct(PostComment $postComment, S3StorageService $s3StorageService) {
        $this->postComment = $postComment;
        $this->s3StorageService = $s3StorageService;
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
            $this->response['data'] = $res;
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
            $this->response['data'] = $res;
        }
        return $this->response; 
    }

    //取得用戶臨時頭像url
    public function generatePresignedUrl($fileName) {
        $generatePresignedUrl = $this->s3StorageService->generatePresignedUrl($fileName);
        return $generatePresignedUrl;
    }

}