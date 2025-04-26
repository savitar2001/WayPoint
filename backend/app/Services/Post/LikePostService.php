<?php
namespace App\Services\post;

use App\Models\Post;
use App\Models\PostLike;

class LikePostService{
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

    //檢查該用戶是否已經對於貼文表達喜歡
    public function ifUserLikedPost($userId, $postId) {
        if ($this->postLike->hasPostLike($userId, $postId) == true) {
            $this->response['error'] = '已經對這則貼文表達喜歡';
        } else {
            $this->response['success'] = true;
        }

        return $this->response;
    }

    //在資料庫紀錄用戶對哪則貼文表達喜歡
    public function addPostLike($userId, $postId) {
        if ($this->postLike->likePost($userId, $postId) === false) {
            $this->response['error'] = '對該貼文按讚失敗';
        } else {
            $this->response['success'] = true;
        }

        return $this->response;  
    }
}