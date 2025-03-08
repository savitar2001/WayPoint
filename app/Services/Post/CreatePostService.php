<?php
namespace App\Services\Post;


use App\Models\User;
use App\Models\Post;

class CreatePostService {
    private $user;
    private $post;
    private $response;

    //創立user、post對象
    public function  __construct(User $user, Post $post) {
        $this->user = $user;
        $this->post = $post;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

     //在資料庫修改貼文者總發文數
     public function changePostAmount($userId) {
        if ($this->user->changeUserPostAmount($userId, 1) !== 1) {
            $this->response['error'] = '貼文數更新失敗';
            return $this->response;
        }
        
        $this->response['success'] = true;
        return $this->response; 
     }

     //在資料庫新增貼文
     public function createPostToDatabase($userId, $name, $content, $tag, $imageUrl){
        if ($this->post->createPost($userId, $name, $content, $tag, $imageUrl)) {
            $this->response['success'] = true;
        } else {
            $this->response['error'] = '新增貼文至資料庫失敗';
        }

        return $this->response;
     }
}