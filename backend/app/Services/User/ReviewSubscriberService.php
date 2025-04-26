<?php
namespace App\Services\User;

use App\Models\User;
use App\Services\Image\S3StorageService;

class ReviewSubscriberService{
    private $user;
    private $s3StorageService;
    private $response;

     //創立user對象
    public function  __construct(User $user, S3StorageService $s3StorageService) {
        $this->user = $user;
        $this->s3StorageService = $s3StorageService;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //查詢用戶所有追蹤用戶id
    public function  getAllUserSubscribers($userId) {
        $res = $this->user->findUserSubscriberId($userId);
        if ($res === false) {
            $this->response['error'] = '查詢訂閱者失敗';
        } else {
            $this->response['success'] = true;
            $this->response['data'] = $res;
        }
        return $this->response;
    }

    //取得使用者頭像臨時url
    public function generatePresignedUrl($fileName) {
        $generatePresignedUrl = $this->s3StorageService->generatePresignedUrl($fileName);
        return $generatePresignedUrl;
    }
}