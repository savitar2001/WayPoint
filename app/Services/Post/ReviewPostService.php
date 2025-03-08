<?php

namespace App\Services\Post;

use App\Models\Post;

class ReviewPostService {
    private $post;
    private $response;

    //創立post
    public function  __construct(Post $post) {
        $this->post = $post;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    } 
    
    //查詢貼文資訊
    public function fetchPostInfo($userId = null, $postId = null, $tag = null) {
        $res = $this->post->searchPost($userId, $postId, $tag);
        if ($res === false) {
            $this->response['error'] = '查詢貼文失敗';
        } else {
            $this->response['success'] = true;
            $this->response['data'][] = $res;
        }
        return $this->response; 
    }
}