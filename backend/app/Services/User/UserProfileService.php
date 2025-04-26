<?php
namespace App\Services\User;

use App\Models\User;
use App\Services\Image\S3StorageService;

class UserProfileService {
    private $user;
    private $s3StorageService;
    private $response;

    //創立User, s3Storage對象
    public function  __construct(User $user, S3StorageService $s3StorageService) {
        $this->user = $user;
        $this->s3StorageService = $s3StorageService;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //取得使用者id、名字、貼文數量、追蹤者數量、粉絲數、頭像url
    public function getUserInformation($userId) {
        $res = $this->user->userInformation($userId);
        if ($res) {
            $this->response['data']['id'] = $res->id;
            $this->response['data']['name'] = $res->name;
            $this->response['data']['avatarUrl'] = $res->avatar_url;
            $this->response['data']['postAmount'] = $res->post_amount;
            $this->response['data']['subscriberCount'] = $res->subscriber_count;
            $this->response['data']['followerCount'] = $res->follower_count;
            $this->response['success'] = true;
        } else {
            $this->response['error'] = '無法取得使用者資訊';
        }

        return $this->response;
    }

    //用名字取得用戶頭像、id
    public function getUserByName($name) {
        $findUserByName = $this->user->findUserByName($name);
        if ($findUserByName) {
            $this->response['data']['id'] = $findUserByName->id;
            $this->response['data']['name'] = $findUserByName->name;
            $this->response['data']['avatarUrl'] = $findUserByName->avatar_url;
            $this->response['success'] = true;
        } else {
            $this->response['error'] = '無法取得使用者資訊';
        }
        return $this->response;
    }

    //取得使用者頭像臨時url
    public function generatePresignedUrl($fileName) {
        $generatePresignedUrl = $this->s3StorageService->generatePresignedUrl($fileName);
        return $generatePresignedUrl;
    }

}