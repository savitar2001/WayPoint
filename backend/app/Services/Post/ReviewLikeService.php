<?php

namespace App\Services\Post;


use App\Models\PostLike;
use App\Services\Image\S3StorageService;

class ReviewLikeService {
    private $postLike;
    private $s3StorageService;
    private $response;

    //創立postLike,s3Storage對象
    public function  __construct(PostLike $postLike, S3StorageService $s3StorageService) {
        $this->postLike = $postLike;
        $this->s3StorageService = $s3StorageService;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //查詢喜歡某篇貼文的用戶
    public function getLikeUserByPost($postId) {
        $res = $this->postLike->getUserLikePost($postId);
        if ($res === false) {
            $this->response['error'] = '查詢按讚貼文用戶失敗';
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