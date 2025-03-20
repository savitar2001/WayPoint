<?php
namespace App\Services\User;

use App\Models\UserFollower;
use App\Services\Image\S3StorageService;

class ReviewFollowerService{
    private $userFollower;
    private $s3StorageService;
    private $response;

     //創立userFollower對象
    public function  __construct(UserFollower $userFollower, S3StorageService $s3StorageService ) {
        $this->userFollower = $userFollower;
        $this->s3StorageService = $s3StorageService;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //查詢用戶所有粉絲id
    public function  getAllUserFollowers($userId) {
        $res = $this->userFollower->getUserFollowers($userId);
        if ($res === false) {
            $this->response['error'] = '查詢粉絲失敗';
        } else {
            $this->response['success'] = true;
            $this->response['data'][] = $res;
        }
        return $this->response;
    }

     //取得使用者頭像臨時url
     public function generatePresignedUrl($fileName) {
        $generatePresignedUrl = $this->s3StorageService->generatePresignedUrl('avatar/',$fileName);
        return $generatePresignedUrl;
    }
}