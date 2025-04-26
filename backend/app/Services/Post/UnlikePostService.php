<?php
namespace App\Services\Post;

use App\Models\Post;
use App\Models\PostLike;

class UnlikePostService{
    private $post;
    private $postLike;
    private $response;

    //創立post、postLike對象
    public function  __construct(Post $post, PostLike $postLike) {
        $this->post = $post;
        $this->postLike = $postLike;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //在資料庫紀錄用戶對哪則貼文取消喜歡
    public function removePostLike($userId, $postId) {
        if ($this->postLike->unlikePost($userId, $postId) === false) {
            $this->response['error'] = '對該貼文取消按讚失敗';
        } else {
            $this->response['success'] = true;
        }

        return $this->response;  
    }
}