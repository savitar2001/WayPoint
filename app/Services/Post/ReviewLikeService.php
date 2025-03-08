<?php

namespace App\Services\Post;


use App\Models\PostLike;

class ReviewLikeService {
    private $postLike;
    private $response;

    //創立postLike對象
    public function  __construct(PostLike $postLike) {
        $this->postLike = $postLike;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //查詢喜歡某篇貼文的用戶
    public function getLikeUserByPost($postId) {
        $res = $this->postLike->getUserLikePost($postId);
        if ($res == false) {
            $this->response['error'] = '查詢按讚貼文用戶失敗';
        } else {
            $this->response['success'] = true;
            $this->response['data'][] = $res;
        }
        return $this->response;
    }
}