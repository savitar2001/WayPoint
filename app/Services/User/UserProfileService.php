<?php
namespace App\Services\User;

use App\Models\User;

class UserProfileService {
    private $user;
    private $response;

    //創立Userr對象
    public function  __construct(User $user) {
        $this->user = $user;
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
            $this->response['data']['id'] = $res['id'];
            $this->response['data']['name'] = $res['name'];
            $this->response['data']['avatarUrl'] = $res['avatar_url'];
            $this->response['data']['postAmount'] = $res['post_amount'];
            $this->response['data']['subscriberCount'] = $res['subscriber_count'];
            $this->response['data']['followerCount'] = $res['follower_count'];
            $this->response['success'] = true;

        } else {
            $this->response['error'] = '無法取得使用者資訊';
        }

        return $this->response;
    }
}