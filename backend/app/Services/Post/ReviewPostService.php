<?php

namespace App\Services\Post;

use App\Models\Post;
use App\Services\Image\S3StorageService;

class ReviewPostService {
    private $post;
    private $s3StorageService;
    private $response;

    //創立post,s3storageService
    public function  __construct(Post $post, S3StorageService $s3StorageService) {
        $this->post = $post;
        $this->s3StorageService = $s3StorageService;
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

    //取得貼文圖片
    public function generatePresignedUrl($fileName) {
        $generatePresignedUrl = $this->s3StorageService->generatePresignedUrl('post/',$fileName);
        return $generatePresignedUrl;
    }
}