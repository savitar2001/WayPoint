<?php
namespace App\Services\User;

use App\Models\UserFollower;

class ReviewFollowerService{
    private $userFollower;
    private $response;

     //創立userFollower對象
    public function  __construct(UserFollower $userFollower ) {
        $this->userFollower = $userFollower;
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
}